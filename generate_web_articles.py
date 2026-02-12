#!/usr/bin/env python3
"""
Generate web articles from RSS sources.
Fetches RSS feeds, processes items, and creates web articles in the database.
Does NOT require Telegram — only populates the web portal.

Usage:
    python generate_web_articles.py
"""
import sys
import logging
from pathlib import Path

# Add project root to path
PROJECT_ROOT = Path(__file__).parent.absolute()
sys.path.insert(0, str(PROJECT_ROOT))

from config import get_config, RSSSource
from db import init_db, get_repository, ArticleRecord
from collector import collect_all
from processor import normalize_all, classify_all, rank_all, dedupe_all
from web.article_pipeline import create_web_article_from_item


def main():
    # Setup logging
    logging.basicConfig(
        level=logging.INFO,
        format='%(asctime)s | %(levelname)-8s | %(name)s | %(message)s',
        datefmt='%H:%M:%S'
    )
    logger = logging.getLogger('goalfeed.generate')

    # Init
    init_db()
    config = get_config()
    repo = get_repository()

    if not config.web.enabled:
        logger.error("Web portal is not enabled in config. Set web.enabled = true.")
        sys.exit(1)

    # 0. Sync sources from config into DB (add new, deactivate removed)
    logger.info("Syncing RSS sources from config...")
    config_sources = [
        {"name": s.name, "url": s.url, "sport_hint": s.sport_hint, "weight": s.weight}
        for s in config.rss_sources
    ]
    repo.seed_sources(config_sources)
    # Deactivate sources no longer in config
    config_urls = {s.url for s in config.rss_sources}
    all_db_sources = repo.get_sources(active_only=False)
    for s in all_db_sources:
        if s['url'] not in config_urls and s.get('active', 1):
            repo.deactivate_source(s['id'])
            logger.info(f"Deactivated source: {s['name']}")

    # 1. Collect from RSS
    logger.info("Collecting from RSS feeds...")
    db_sources = repo.get_sources(active_only=True)
    sources = [
        RSSSource(
            name=s['name'],
            url=s['url'],
            sport_hint=s['sport_hint'],
            weight=s['weight']
        )
        for s in db_sources
    ]

    if not sources:
        logger.error("No RSS sources found in database.")
        sys.exit(1)

    logger.info(f"Found {len(sources)} active RSS source(s)")
    raw_items = collect_all(sources)

    if not raw_items:
        logger.info("No items collected from RSS feeds.")
        return

    logger.info(f"Collected {len(raw_items)} raw items")

    # 2. Process pipeline
    normalized = normalize_all(raw_items)
    unique = dedupe_all(normalized)

    if not unique:
        logger.info("All items were duplicates (already processed).")
        return

    classified = classify_all(unique)
    ranked = rank_all(classified)

    logger.info(f"{len(ranked)} unique items after processing")

    # 3. Save articles to DB (assigns article_id needed by web pipeline)
    saved = 0
    for item in ranked:
        try:
            record = ArticleRecord(
                title=item.title,
                normalized_title=item.normalized_title,
                link=item.link,
                canonical_url=item.canonical_url,
                summary=item.summary,
                published_at=item.published_at.isoformat() if item.published_at else None,
                sport=item.sport,
                category=item.category,
                status=item.status,
                score=item.score,
                content_hash=item.content_hash,
                image_url=item.image_url,
                source_name=item.source_name,
                source_domain=item.source_domain
            )
            article_id = repo.upsert_article(record)
            item.article_id = article_id
            saved += 1
        except Exception as e:
            logger.error(f"Error saving article record: {e}")

    logger.info(f"Saved {saved} article records to DB")

    # 4. Create web articles
    created = 0
    skipped = 0
    failed = 0

    for item in ranked:
        if not getattr(item, 'article_id', None):
            skipped += 1
            continue
        try:
            result = create_web_article_from_item(item)
            if result:
                created += 1
            else:
                skipped += 1
        except Exception as e:
            logger.error(f"Error creating web article for '{item.title[:50]}': {e}")
            failed += 1

    logger.info(f"Done! Created: {created} | Skipped: {skipped} | Failed: {failed}")


if __name__ == '__main__':
    main()
