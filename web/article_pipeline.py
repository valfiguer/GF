"""
Article pipeline for GoalFeed Web Portal.
Uses original RSS content directly (no AI generation).
"""
import logging
import re
import unicodedata
from typing import Optional

from config import get_config
from db import get_repository, WebArticleRecord
from web.image_service import save_article_image

logger = logging.getLogger(__name__)


def _generate_slug(title: str) -> str:
    """Generate a URL-friendly slug from a title."""
    # Normalize unicode characters
    slug = unicodedata.normalize('NFKD', title)
    slug = slug.encode('ascii', 'ignore').decode('ascii')
    slug = slug.lower().strip()
    # Replace non-alphanumeric with hyphens
    slug = re.sub(r'[^a-z0-9]+', '-', slug)
    # Remove leading/trailing hyphens
    slug = slug.strip('-')
    # Limit length
    return slug[:120] if slug else 'articulo'


def _summary_to_html(summary: str) -> str:
    """Convert a plain text summary into simple HTML paragraphs."""
    if not summary:
        return '<p>Sin contenido disponible.</p>'
    paragraphs = [p.strip() for p in summary.split('\n') if p.strip()]
    if not paragraphs:
        return f'<p>{summary.strip()}</p>'
    return '\n'.join(f'<p>{p}</p>' for p in paragraphs)


def _generate_meta_description(title: str, summary: str) -> str:
    """Generate a meta description from title and summary (max 160 chars)."""
    text = summary.strip() if summary else title
    if len(text) <= 160:
        return text
    return text[:157] + '...'


def _generate_meta_keywords(title: str, sport: str, category: Optional[str]) -> str:
    """Generate meta keywords from title, sport, and category."""
    keywords = []
    if sport:
        keywords.append(sport)
    if category:
        keywords.append(category)
    # Extract significant words from title (>3 chars)
    stop_words = {'para', 'como', 'este', 'esta', 'estos', 'estas', 'pero', 'porque',
                  'cuando', 'donde', 'quien', 'cual', 'tras', 'ante', 'sobre', 'entre',
                  'desde', 'hasta', 'según', 'contra', 'hacia', 'mediante', 'durante',
                  'the', 'and', 'for', 'with', 'from', 'that', 'this', 'have', 'will',
                  'could', 'would', 'should', 'been', 'more', 'than', 'what', 'which'}
    words = re.findall(r'[a-záéíóúñü]+', title.lower())
    for w in words:
        if len(w) > 3 and w not in stop_words and w not in keywords:
            keywords.append(w)
    return ', '.join(keywords[:10])


def create_web_article_from_item(item) -> Optional[int]:
    """
    Create a web article from a NormalizedItem using original RSS content.

    Args:
        item: NormalizedItem with article_id, title, summary, sport, etc.

    Returns:
        web_article ID or None on failure
    """
    config = get_config()

    if not config.web.enabled:
        return None

    article_id = getattr(item, 'article_id', None)
    if not article_id:
        logger.debug("Item has no article_id, skipping web article generation")
        return None

    repo = get_repository()

    # Check if web article already exists for this article
    existing = repo.get_web_article_by_article_id(article_id)
    if existing:
        logger.debug(f"Web article already exists for article_id={article_id}")
        return existing['id']

    # Use original content directly
    title = item.title or 'Sin título'
    summary = item.summary or ''

    slug = _generate_slug(title)
    headline = title
    subtitle = summary[:200] if summary else ''
    body_html = _summary_to_html(summary)
    body_text = summary
    meta_description = _generate_meta_description(title, summary)
    meta_keywords = _generate_meta_keywords(title, item.sport, getattr(item, 'category', None))

    # Ensure slug is unique
    if repo.get_web_article_by_slug(slug):
        import time
        slug = f"{slug}-{int(time.time()) % 100000}"

    # Save image locally
    image_filename = None
    image_url = getattr(item, 'image_url', None)
    if image_url:
        image_filename = save_article_image(image_url, slug, item.sport)

    # Build web article record
    web_article = WebArticleRecord(
        article_id=article_id,
        slug=slug,
        headline=headline,
        subtitle=subtitle,
        body_html=body_html,
        body_text=body_text,
        meta_description=meta_description,
        meta_keywords=meta_keywords,
        og_title=headline,
        og_description=meta_description,
        og_image_url=image_url,
        sport=item.sport,
        category=getattr(item, 'category', None),
        status=getattr(item, 'status', 'RUMOR'),
        image_filename=image_filename,
        image_url=image_url,
        source_name=item.source_name,
        source_url=item.link,
        is_published=True,
        score=getattr(item, 'score', 0),
    )

    try:
        web_id = repo.insert_web_article(web_article)
        logger.info(f"Created web article: {slug} (id={web_id})")

        # Save team associations
        teams = getattr(item, 'teams', None)
        if teams and web_id:
            try:
                repo.insert_article_teams(web_id, teams)
                logger.debug(f"Saved {len(teams)} team tags for web_id={web_id}")
            except Exception as te:
                logger.warning(f"Error saving team tags: {te}")

        return web_id
    except Exception as e:
        logger.error(f"Error inserting web article: {e}")
        return None
