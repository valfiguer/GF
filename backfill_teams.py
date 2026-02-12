#!/usr/bin/env python3
"""
Backfill script: tag existing web_articles with league/team data.

Run once before deploy:
    python backfill_teams.py

Steps:
1. Creates article_teams table if not exists
2. Adds primary_league/primary_team columns if missing
3. Scans all web_articles
4. Classifies each via classify_teams() and inserts associations
"""
import logging
import sys
import os

# Ensure project root is on path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from config import get_config
from db import get_database, get_repository
from processor.classify import classify_teams
from processor.normalize import NormalizedItem

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
)
logger = logging.getLogger(__name__)


def ensure_schema(db):
    """Create table and columns if they don't exist."""
    logger.info("Ensuring article_teams table exists...")
    db.execute("""
        CREATE TABLE IF NOT EXISTS article_teams (
            id INT AUTO_INCREMENT PRIMARY KEY,
            web_article_id INT NOT NULL,
            league_slug VARCHAR(50) NOT NULL,
            team_slug VARCHAR(50) NOT NULL,
            is_primary TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_at_article (web_article_id),
            INDEX idx_at_league (league_slug),
            INDEX idx_at_team (team_slug),
            INDEX idx_at_league_team (league_slug, team_slug),
            FOREIGN KEY (web_article_id) REFERENCES web_articles(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    """)

    # Add columns if missing (MySQL will error on duplicate, so check first)
    try:
        row = db.fetchone(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS "
            "WHERE TABLE_NAME = 'web_articles' AND COLUMN_NAME = 'primary_league'"
        )
        if not row:
            db.execute("ALTER TABLE web_articles ADD COLUMN primary_league VARCHAR(50) NULL AFTER category")
            db.execute("ALTER TABLE web_articles ADD COLUMN primary_team VARCHAR(50) NULL AFTER primary_league")
            logger.info("Added primary_league and primary_team columns")
        else:
            logger.info("Columns already exist")
    except Exception as e:
        logger.warning(f"Column check/add: {e}")

    # Indexes (ignore if already exists)
    for idx in ["idx_wa_league", "idx_wa_team"]:
        try:
            col = "primary_league" if "league" in idx else "primary_team"
            db.execute(f"ALTER TABLE web_articles ADD INDEX {idx} ({col})")
        except Exception:
            pass


def backfill():
    """Main backfill logic."""
    config = get_config()
    db = get_database()
    repo = get_repository()

    ensure_schema(db)

    # Get all web articles
    rows = db.fetchall(
        "SELECT id, headline, subtitle, body_text, sport, category FROM web_articles "
        "WHERE is_published = 1 ORDER BY id"
    )
    total = len(rows)
    logger.info(f"Found {total} web articles to process")

    tagged = 0
    skipped = 0

    for i, row in enumerate(rows):
        article = dict(row)
        web_id = article["id"]

        # Check if already tagged
        existing = db.fetchone(
            "SELECT id FROM article_teams WHERE web_article_id = %s LIMIT 1",
            (web_id,)
        )
        if existing:
            skipped += 1
            continue

        # Build a minimal NormalizedItem for classification
        item = NormalizedItem(
            title=article.get("headline") or "",
            normalized_title="",
            link="",
            canonical_url="",
            content_hash="",
            summary=article.get("body_text") or article.get("subtitle") or "",
            source_sport_hint=article.get("sport") or "football_eu",
        )

        teams = classify_teams(item)
        if teams:
            repo.insert_article_teams(web_id, teams)
            tagged += 1
            if tagged % 50 == 0:
                logger.info(f"  Progress: {tagged} tagged, {skipped} skipped, {i+1}/{total}")

    logger.info(f"Done. Tagged: {tagged}, Skipped (already tagged): {skipped}, Total: {total}")


if __name__ == "__main__":
    backfill()
