"""
Database repository functions for GoalFeed.
High-level database operations for articles, posts, sources, etc.
"""
import logging
from datetime import datetime, timedelta
from typing import Optional, List, Dict, Any
from dataclasses import dataclass, asdict
import json

from .database import get_database, Database
from utils.timeutils import utc_now, get_start_of_day, datetime_to_iso

logger = logging.getLogger(__name__)


@dataclass
class ArticleRecord:
    """Article data structure for database operations."""
    id: Optional[int] = None
    source_id: Optional[int] = None
    title: str = ""
    normalized_title: str = ""
    link: str = ""
    canonical_url: str = ""
    summary: Optional[str] = None
    published_at: Optional[str] = None
    sport: str = "football_eu"
    category: Optional[str] = None
    status: str = "RUMOR"
    score: int = 0
    content_hash: str = ""
    image_url: Optional[str] = None
    source_name: Optional[str] = None
    source_domain: Optional[str] = None
    is_duplicate: bool = False
    is_posted: bool = False
    is_digested: bool = False
    created_at: Optional[str] = None
    updated_at: Optional[str] = None


@dataclass
class WebArticleRecord:
    """Web article data structure for database operations."""
    id: Optional[int] = None
    article_id: Optional[int] = None
    slug: str = ""
    headline: str = ""
    subtitle: Optional[str] = None
    body_html: str = ""
    body_text: Optional[str] = None
    meta_description: Optional[str] = None
    meta_keywords: Optional[str] = None
    og_title: Optional[str] = None
    og_description: Optional[str] = None
    og_image_url: Optional[str] = None
    sport: str = "football_eu"
    category: Optional[str] = None
    status: str = "RUMOR"
    image_filename: Optional[str] = None
    image_url: Optional[str] = None
    source_name: Optional[str] = None
    source_url: Optional[str] = None
    is_published: bool = True
    is_featured: bool = False
    view_count: int = 0
    score: int = 0
    created_at: Optional[str] = None
    updated_at: Optional[str] = None


@dataclass
class PostRecord:
    """Post data structure for database operations."""
    id: Optional[int] = None
    article_id: Optional[int] = None
    telegram_message_id: Optional[int] = None
    telegram_chat_id: Optional[str] = None
    caption: Optional[str] = None
    image_path: Optional[str] = None
    sport: Optional[str] = None
    post_type: str = "single"
    posted_at: Optional[str] = None


class Repository:
    """Database repository for all GoalFeed operations."""
    
    def __init__(self, db: Optional[Database] = None):
        """
        Initialize repository.
        
        Args:
            db: Database instance (uses global if not provided)
        """
        self.db = db or get_database()
    
    # ===================
    # SOURCES
    # ===================
    
    def get_sources(self, active_only: bool = True) -> List[Dict]:
        """
        Get all RSS sources.
        
        Args:
            active_only: Only return active sources
            
        Returns:
            List of source dictionaries
        """
        query = "SELECT * FROM sources"
        if active_only:
            query += " WHERE active = 1"
        query += " ORDER BY weight DESC, name ASC"
        
        rows = self.db.fetchall(query)
        return [dict(row) for row in rows]
    
    def upsert_source(self, name: str, url: str, sport_hint: str, weight: int = 10) -> int:
        """
        Insert or update a source.
        
        Returns:
            Source ID
        """
        existing = self.db.fetchone(
            "SELECT id FROM sources WHERE url = %s",
            (url,)
        )

        if existing:
            self.db.execute(
                """UPDATE sources
                   SET name = %s, sport_hint = %s, weight = %s, updated_at = %s
                   WHERE url = %s""",
                (name, sport_hint, weight, datetime_to_iso(utc_now()), url)
            )
            return existing['id']
        else:
            cursor = self.db.execute(
                """INSERT INTO sources (name, url, sport_hint, weight)
                   VALUES (%s, %s, %s, %s)""",
                (name, url, sport_hint, weight)
            )
            return cursor.lastrowid
    
    def deactivate_source(self, source_id: int):
        """Deactivate a source by setting active = 0."""
        self.db.execute(
            "UPDATE sources SET active = 0, updated_at = %s WHERE id = %s",
            (datetime_to_iso(utc_now()), source_id)
        )

    def update_source_fetched(self, source_id: int):
        """Update the last_fetched_at timestamp for a source."""
        self.db.execute(
            "UPDATE sources SET last_fetched_at = %s WHERE id = %s",
            (datetime_to_iso(utc_now()), source_id)
        )
    
    def seed_sources(self, sources: List[Dict]):
        """
        Seed multiple sources into the database.
        
        Args:
            sources: List of source dicts with name, url, sport_hint, weight
        """
        for source in sources:
            self.upsert_source(
                name=source.get('name', ''),
                url=source.get('url', ''),
                sport_hint=source.get('sport_hint', 'football_eu'),
                weight=source.get('weight', 10)
            )
        logger.info(f"Seeded {len(sources)} sources")
    
    # ===================
    # ARTICLES
    # ===================
    
    def upsert_article(self, article: ArticleRecord) -> int:
        """
        Insert or update an article.
        
        Args:
            article: ArticleRecord to save
            
        Returns:
            Article ID
        """
        now = datetime_to_iso(utc_now())
        
        # Check if exists by canonical_url
        existing = self.db.fetchone(
            "SELECT id FROM articles WHERE canonical_url = %s",
            (article.canonical_url,)
        )
        
        if existing:
            # Update existing
            self.db.execute(
                """UPDATE articles SET
                   title = %s, normalized_title = %s, summary = %s,
                   sport = %s, category = %s, status = %s, score = %s,
                   image_url = %s, updated_at = %s
                   WHERE id = %s""",
                (
                    article.title, article.normalized_title, article.summary,
                    article.sport, article.category, article.status, article.score,
                    article.image_url, now, existing['id']
                )
            )
            return existing['id']
        else:
            # Insert new
            cursor = self.db.execute(
                """INSERT INTO articles (
                    source_id, title, normalized_title, link, canonical_url,
                    summary, published_at, sport, category, status, score,
                    content_hash, image_url, source_name, source_domain,
                    is_duplicate, is_posted, is_digested, created_at, updated_at
                ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)""",
                (
                    article.source_id, article.title, article.normalized_title,
                    article.link, article.canonical_url, article.summary,
                    article.published_at, article.sport, article.category,
                    article.status, article.score, article.content_hash,
                    article.image_url, article.source_name, article.source_domain,
                    int(article.is_duplicate), int(article.is_posted),
                    int(article.is_digested), now, now
                )
            )
            return cursor.lastrowid
    
    def get_article_by_id(self, article_id: int) -> Optional[Dict]:
        """Get an article by ID."""
        row = self.db.fetchone(
            "SELECT * FROM articles WHERE id = %s",
            (article_id,)
        )
        return dict(row) if row else None
    
    def get_article_by_canonical_url(self, canonical_url: str) -> Optional[Dict]:
        """Get an article by canonical URL."""
        row = self.db.fetchone(
            "SELECT * FROM articles WHERE canonical_url = %s",
            (canonical_url,)
        )
        return dict(row) if row else None
    
    def get_article_by_content_hash(self, content_hash: str) -> Optional[Dict]:
        """Get an article by content hash."""
        row = self.db.fetchone(
            "SELECT * FROM articles WHERE content_hash = %s",
            (content_hash,)
        )
        return dict(row) if row else None
    
    def is_duplicate(self, canonical_url: str, content_hash: str) -> bool:
        """
        Check if an article is a duplicate.
        
        Args:
            canonical_url: Canonical URL
            content_hash: Content hash
            
        Returns:
            True if duplicate exists
        """
        row = self.db.fetchone(
            """SELECT id FROM articles 
               WHERE canonical_url = %s OR content_hash = %s
               LIMIT 1""",
            (canonical_url, content_hash)
        )
        return row is not None
    
    def get_recent_articles(
        self,
        hours: int = 6,
        sport: Optional[str] = None,
        posted_only: bool = False,
        unposted_only: bool = False
    ) -> List[Dict]:
        """
        Get recent articles.
        
        Args:
            hours: How many hours back to look
            sport: Filter by sport
            posted_only: Only return posted articles
            unposted_only: Only return unposted articles
            
        Returns:
            List of article dicts
        """
        cutoff = utc_now() - timedelta(hours=hours)
        cutoff_str = datetime_to_iso(cutoff)
        
        query = "SELECT * FROM articles WHERE created_at >= %s"
        params = [cutoff_str]
        
        if sport:
            query += " AND sport = %s"
            params.append(sport)
        
        if posted_only:
            query += " AND is_posted = 1"
        elif unposted_only:
            query += " AND is_posted = 0 AND is_duplicate = 0"
        
        query += " ORDER BY score DESC, created_at DESC"
        
        rows = self.db.fetchall(query, tuple(params))
        return [dict(row) for row in rows]
    
    def get_unposted_candidates(
        self,
        min_score: int = 0,
        limit: int = 50
    ) -> List[Dict]:
        """
        Get unposted article candidates for publishing.
        
        Args:
            min_score: Minimum score threshold
            limit: Maximum number of results
            
        Returns:
            List of candidate article dicts
        """
        rows = self.db.fetchall(
            """SELECT * FROM articles 
               WHERE is_posted = 0 
               AND is_duplicate = 0 
               AND is_digested = 0
               AND score >= %s
               ORDER BY score DESC, created_at DESC
               LIMIT %s""",
            (min_score, limit)
        )
        return [dict(row) for row in rows]
    
    def get_digest_candidates(
        self,
        sport: str,
        window_minutes: int = 20,
        score_min: int = 55,
        score_max: int = 75
    ) -> List[Dict]:
        """
        Get candidates for digest aggregation.
        
        Args:
            sport: Sport type
            window_minutes: Time window in minutes
            score_min: Minimum score
            score_max: Maximum score
            
        Returns:
            List of candidate articles
        """
        cutoff = utc_now() - timedelta(minutes=window_minutes)
        cutoff_str = datetime_to_iso(cutoff)
        
        rows = self.db.fetchall(
            """SELECT * FROM articles 
               WHERE sport = %s
               AND is_posted = 0 
               AND is_duplicate = 0
               AND is_digested = 0
               AND score >= %s AND score <= %s
               AND created_at >= %s
               ORDER BY score DESC
               LIMIT 10""",
            (sport, score_min, score_max, cutoff_str)
        )
        return [dict(row) for row in rows]
    
    def mark_article_posted(self, article_id: int):
        """Mark an article as posted."""
        self.db.execute(
            "UPDATE articles SET is_posted = 1, updated_at = %s WHERE id = %s",
            (datetime_to_iso(utc_now()), article_id)
        )
    
    def mark_article_duplicate(self, article_id: int):
        """Mark an article as duplicate."""
        self.db.execute(
            "UPDATE articles SET is_duplicate = 1, updated_at = %s WHERE id = %s",
            (datetime_to_iso(utc_now()), article_id)
        )
    
    def mark_articles_digested(self, article_ids: List[int]):
        """Mark multiple articles as included in a digest."""
        now = datetime_to_iso(utc_now())
        for article_id in article_ids:
            self.db.execute(
                "UPDATE articles SET is_digested = 1, updated_at = %s WHERE id = %s",
                (now, article_id)
            )
    
    def get_similar_titles_recent(
        self,
        normalized_title: str,
        hours: int = 6
    ) -> List[Dict]:
        """
        Get articles with similar titles in recent hours.
        Used for fuzzy deduplication.
        
        Args:
            normalized_title: Normalized title to compare
            hours: Hours to look back
            
        Returns:
            List of recent articles for comparison
        """
        cutoff = utc_now() - timedelta(hours=hours)
        cutoff_str = datetime_to_iso(cutoff)
        
        rows = self.db.fetchall(
            """SELECT id, normalized_title, canonical_url FROM articles 
               WHERE created_at >= %s
               ORDER BY created_at DESC
               LIMIT 500""",
            (cutoff_str,)
        )
        return [dict(row) for row in rows]
    
    # ===================
    # POSTS
    # ===================
    
    def record_post(
        self,
        article_id: int,
        telegram_message_id: int,
        telegram_chat_id: str,
        caption: str,
        image_path: Optional[str] = None,
        sport: Optional[str] = None,
        post_type: str = "single"
    ) -> int:
        """
        Record a published post.
        
        Returns:
            Post ID
        """
        cursor = self.db.execute(
            """INSERT INTO posts (
                article_id, telegram_message_id, telegram_chat_id,
                caption, image_path, sport, post_type, posted_at
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)""",
            (
                article_id, telegram_message_id, telegram_chat_id,
                caption, image_path, sport, post_type,
                datetime_to_iso(utc_now())
            )
        )
        
        # Mark article as posted
        self.mark_article_posted(article_id)
        
        # Update daily stats
        self._increment_daily_posts()
        
        return cursor.lastrowid
    
    def count_posts_today(self, tz_name: str = "Europe/Madrid") -> int:
        """
        Count posts made today.
        
        Args:
            tz_name: Timezone for 'today' calculation
            
        Returns:
            Number of posts today
        """
        start_of_day = get_start_of_day(tz_name)
        start_str = datetime_to_iso(start_of_day)
        
        row = self.db.fetchone(
            "SELECT COUNT(*) as count FROM posts WHERE posted_at >= %s",
            (start_str,)
        )
        return row['count'] if row else 0
    
    def count_posts_last_hour(self) -> int:
        """
        Count posts in the last hour.
        
        Returns:
            Number of posts in last hour
        """
        cutoff = utc_now() - timedelta(hours=1)
        cutoff_str = datetime_to_iso(cutoff)
        
        row = self.db.fetchone(
            "SELECT COUNT(*) as count FROM posts WHERE posted_at >= %s",
            (cutoff_str,)
        )
        return row['count'] if row else 0
    
    def last_post_time_by_sport(self, sport: str) -> Optional[datetime]:
        """
        Get the last post time for a sport.
        
        Args:
            sport: Sport type
            
        Returns:
            Last post datetime or None
        """
        row = self.db.fetchone(
            """SELECT posted_at FROM posts 
               WHERE sport = %s 
               ORDER BY posted_at DESC 
               LIMIT 1""",
            (sport,)
        )
        
        if row and row['posted_at']:
            from utils.timeutils import iso_to_datetime
            return iso_to_datetime(row['posted_at'])
        return None
    
    def get_recent_posts(self, hours: int = 24) -> List[Dict]:
        """Get posts from the last N hours."""
        cutoff = utc_now() - timedelta(hours=hours)
        cutoff_str = datetime_to_iso(cutoff)
        
        rows = self.db.fetchall(
            """SELECT p.*, a.title as article_title
               FROM posts p
               LEFT JOIN articles a ON p.article_id = a.id
               WHERE p.posted_at >= %s
               ORDER BY p.posted_at DESC""",
            (cutoff_str,)
        )
        return [dict(row) for row in rows]
    
    # ===================
    # DIGESTS
    # ===================
    
    def record_digest(
        self,
        article_ids: List[int],
        telegram_message_id: int,
        telegram_chat_id: str,
        caption: str,
        image_path: Optional[str] = None,
        sport: str = "football_eu"
    ) -> int:
        """
        Record a published digest.
        
        Returns:
            Digest ID
        """
        cursor = self.db.execute(
            """INSERT INTO digests (
                telegram_message_id, telegram_chat_id, caption,
                image_path, sport, article_count, posted_at
            ) VALUES (%s, %s, %s, %s, %s, %s, %s)""",
            (
                telegram_message_id, telegram_chat_id, caption,
                image_path, sport, len(article_ids),
                datetime_to_iso(utc_now())
            )
        )
        
        digest_id = cursor.lastrowid
        
        # Record digest items
        for pos, article_id in enumerate(article_ids):
            self.db.execute(
                """INSERT INTO digest_items (digest_id, article_id, position)
                   VALUES (%s, %s, %s)""",
                (digest_id, article_id, pos)
            )
        
        # Mark articles as digested
        self.mark_articles_digested(article_ids)
        
        # Update daily stats
        self._increment_daily_digests()
        
        return digest_id
    
    def count_digests_today(self, tz_name: str = "Europe/Madrid") -> int:
        """Count digests made today."""
        start_of_day = get_start_of_day(tz_name)
        start_str = datetime_to_iso(start_of_day)
        
        row = self.db.fetchone(
            "SELECT COUNT(*) as count FROM digests WHERE posted_at >= %s",
            (start_str,)
        )
        return row['count'] if row else 0
    
    # ===================
    # DAILY STATS
    # ===================
    
    def _get_today_date(self) -> str:
        """Get today's date string."""
        return utc_now().strftime("%Y-%m-%d")
    
    def _ensure_daily_stats(self):
        """Ensure today's daily stats row exists."""
        today = self._get_today_date()
        existing = self.db.fetchone(
            "SELECT id FROM daily_stats WHERE `date` = %s",
            (today,)
        )
        if not existing:
            self.db.execute(
                "INSERT INTO daily_stats (`date`) VALUES (%s)",
                (today,)
            )
    
    def _increment_daily_posts(self):
        """Increment today's post count."""
        self._ensure_daily_stats()
        today = self._get_today_date()
        self.db.execute(
            """UPDATE daily_stats 
               SET post_count = post_count + 1, updated_at = %s
               WHERE `date` = %s""",
            (datetime_to_iso(utc_now()), today)
        )
    
    def _increment_daily_digests(self):
        """Increment today's digest count."""
        self._ensure_daily_stats()
        today = self._get_today_date()
        self.db.execute(
            """UPDATE daily_stats 
               SET digest_count = digest_count + 1, updated_at = %s
               WHERE `date` = %s""",
            (datetime_to_iso(utc_now()), today)
        )
    
    def increment_articles_fetched(self, count: int = 1):
        """Increment today's fetched article count."""
        self._ensure_daily_stats()
        today = self._get_today_date()
        self.db.execute(
            """UPDATE daily_stats 
               SET articles_fetched = articles_fetched + %s, updated_at = %s
               WHERE `date` = %s""",
            (count, datetime_to_iso(utc_now()), today)
        )
    
    def increment_articles_duplicated(self, count: int = 1):
        """Increment today's duplicated article count."""
        self._ensure_daily_stats()
        today = self._get_today_date()
        self.db.execute(
            """UPDATE daily_stats 
               SET articles_duplicated = articles_duplicated + %s, updated_at = %s
               WHERE `date` = %s""",
            (count, datetime_to_iso(utc_now()), today)
        )
    
    def get_daily_stats(self, date: Optional[str] = None) -> Optional[Dict]:
        """Get daily stats for a specific date."""
        if date is None:
            date = self._get_today_date()
        
        row = self.db.fetchone(
            "SELECT * FROM daily_stats WHERE `date` = %s",
            (date,)
        )
        return dict(row) if row else None
    
    # ===================
    # SETTINGS
    # ===================
    
    def get_setting(self, key: str, default: Optional[str] = None) -> Optional[str]:
        """Get a setting value."""
        row = self.db.fetchone(
            "SELECT `value` FROM settings WHERE `key` = %s",
            (key,)
        )
        return row['value'] if row else default
    
    def set_setting(self, key: str, value: str):
        """Set a setting value."""
        self.db.execute(
            """REPLACE INTO settings (`key`, `value`, updated_at)
               VALUES (%s, %s, %s)""",
            (key, value, datetime_to_iso(utc_now()))
        )
    
    # ===================
    # LIVE MATCHES
    # ===================
    
    def upsert_live_match(
        self,
        match_id: str,
        league_id: int,
        league_name: str,
        home_team: str,
        away_team: str,
        home_score: int = 0,
        away_score: int = 0,
        match_status: str = "NS",
        current_minute: int = 0,
        is_top_team_match: bool = False,
        match_start: Optional[str] = None
    ) -> int:
        """
        Insert or update a live match.
        
        Returns:
            Match row ID
        """
        now = datetime_to_iso(utc_now())
        
        existing = self.db.fetchone(
            "SELECT id FROM live_matches WHERE match_id = %s",
            (match_id,)
        )
        
        if existing:
            self.db.execute(
                """UPDATE live_matches SET
                   home_score = %s, away_score = %s, match_status = %s,
                   current_minute = %s, updated_at = %s
                   WHERE match_id = %s""",
                (home_score, away_score, match_status, current_minute, now, match_id)
            )
            return existing['id']
        else:
            cursor = self.db.execute(
                """INSERT INTO live_matches (
                    match_id, league_id, league_name, home_team, away_team,
                    home_score, away_score, match_status, current_minute,
                    is_top_team_match, match_start, created_at, updated_at
                ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)""",
                (
                    match_id, league_id, league_name, home_team, away_team,
                    home_score, away_score, match_status, current_minute,
                    int(is_top_team_match), match_start, now, now
                )
            )
            return cursor.lastrowid
    
    def get_live_match(self, match_id: str) -> Optional[Dict]:
        """Get a live match by match_id."""
        row = self.db.fetchone(
            "SELECT * FROM live_matches WHERE match_id = %s",
            (match_id,)
        )
        return dict(row) if row else None
    
    def get_active_live_matches(self) -> List[Dict]:
        """Get all active (non-finished) live matches."""
        rows = self.db.fetchall(
            """SELECT * FROM live_matches 
               WHERE match_status NOT IN ('FT', 'AET', 'PEN', 'CANC', 'PST', 'ABD')
               ORDER BY created_at DESC"""
        )
        return [dict(row) for row in rows]
    
    def increment_match_events(self, match_id: str):
        """Increment the events_published counter for a match."""
        now = datetime_to_iso(utc_now())
        self.db.execute(
            """UPDATE live_matches 
               SET events_published = events_published + 1,
                   last_event_at = %s,
                   updated_at = %s
               WHERE match_id = %s""",
            (now, now, match_id)
        )
    
    def get_match_event_count(self, match_id: str) -> int:
        """Get the number of events published for a match."""
        row = self.db.fetchone(
            "SELECT events_published FROM live_matches WHERE match_id = %s",
            (match_id,)
        )
        return row['events_published'] if row else 0
    
    def get_last_event_time(self, match_id: str) -> Optional[datetime]:
        """Get the last event time for a match."""
        row = self.db.fetchone(
            "SELECT last_event_at FROM live_matches WHERE match_id = %s",
            (match_id,)
        )
        if row and row['last_event_at']:
            from utils.timeutils import iso_to_datetime
            return iso_to_datetime(row['last_event_at'])
        return None
    
    # ===================
    # LIVE EVENTS
    # ===================
    
    def record_live_event(
        self,
        match_id: str,
        league_id: int,
        league_name: str,
        home_team: str,
        away_team: str,
        home_score: int,
        away_score: int,
        event_type: str,
        event_minute: Optional[int] = None,
        event_player: Optional[str] = None,
        event_detail: Optional[str] = None,
        telegram_message_id: Optional[int] = None,
        telegram_chat_id: Optional[str] = None
    ) -> Optional[int]:
        """
        Record a live event.
        
        Returns:
            Event ID or None if duplicate
        """
        now = datetime_to_iso(utc_now())
        
        try:
            cursor = self.db.execute(
                """INSERT INTO live_events (
                    match_id, league_id, league_name, home_team, away_team,
                    home_score, away_score, event_type, event_minute,
                    event_player, event_detail, telegram_message_id,
                    telegram_chat_id, is_published, published_at, created_at
                ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)""",
                (
                    match_id, league_id, league_name, home_team, away_team,
                    home_score, away_score, event_type, event_minute,
                    event_player, event_detail, telegram_message_id,
                    telegram_chat_id, 1 if telegram_message_id else 0,
                    now if telegram_message_id else None, now
                )
            )
            return cursor.lastrowid
        except Exception as e:
            # Likely duplicate constraint violation
            logger.warning(f"Could not record live event (likely duplicate): {e}")
            return None
    
    def is_event_published(
        self,
        match_id: str,
        event_type: str,
        event_minute: Optional[int] = None,
        event_player: Optional[str] = None
    ) -> bool:
        """Check if an event has already been published."""
        if event_minute is not None and event_player:
            row = self.db.fetchone(
                """SELECT id FROM live_events 
                   WHERE match_id = %s AND event_type = %s 
                   AND event_minute = %s AND event_player = %s""",
                (match_id, event_type, event_minute, event_player)
            )
        elif event_type == 'final':
            row = self.db.fetchone(
                """SELECT id FROM live_events 
                   WHERE match_id = %s AND event_type = 'final'""",
                (match_id,)
            )
        else:
            row = self.db.fetchone(
                """SELECT id FROM live_events 
                   WHERE match_id = %s AND event_type = %s""",
                (match_id, event_type)
            )
        
        return row is not None
    
    def get_match_events(self, match_id: str) -> List[Dict]:
        """Get all events for a match."""
        rows = self.db.fetchall(
            """SELECT * FROM live_events 
               WHERE match_id = %s
               ORDER BY event_minute ASC, created_at ASC""",
            (match_id,)
        )
        return [dict(row) for row in rows]
    
    def count_live_events_today(self, tz_name: str = "Europe/Madrid") -> int:
        """Count live events published today."""
        start_of_day = get_start_of_day(tz_name)
        start_str = datetime_to_iso(start_of_day)

        row = self.db.fetchone(
            """SELECT COUNT(*) as count FROM live_events
               WHERE is_published = 1 AND published_at >= %s""",
            (start_str,)
        )
        return row['count'] if row else 0

    # ===================
    # WEB ARTICLES
    # ===================

    def insert_web_article(self, article: WebArticleRecord) -> int:
        """Insert a new web article. Returns web article ID."""
        now = datetime_to_iso(utc_now())
        cursor = self.db.execute(
            """INSERT INTO web_articles (
                article_id, slug, headline, subtitle, body_html, body_text,
                meta_description, meta_keywords, og_title, og_description, og_image_url,
                sport, category, status, image_filename, image_url,
                source_name, source_url, is_published, is_featured,
                view_count, score, created_at, updated_at
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)""",
            (
                article.article_id, article.slug, article.headline,
                article.subtitle, article.body_html, article.body_text,
                article.meta_description, article.meta_keywords,
                article.og_title, article.og_description, article.og_image_url,
                article.sport, article.category, article.status,
                article.image_filename, article.image_url,
                article.source_name, article.source_url,
                int(article.is_published), int(article.is_featured),
                article.view_count, article.score, now, now
            )
        )
        return cursor.lastrowid

    def get_web_article_by_slug(self, slug: str) -> Optional[Dict]:
        """Get a web article by slug."""
        row = self.db.fetchone(
            "SELECT * FROM web_articles WHERE slug = %s",
            (slug,)
        )
        return dict(row) if row else None

    def get_web_article_by_article_id(self, article_id: int) -> Optional[Dict]:
        """Get a web article by its parent article ID."""
        row = self.db.fetchone(
            "SELECT * FROM web_articles WHERE article_id = %s",
            (article_id,)
        )
        return dict(row) if row else None

    def get_web_articles_paginated(
        self,
        page: int = 1,
        per_page: int = 12,
        sport: Optional[str] = None
    ) -> List[Dict]:
        """Get paginated web articles."""
        offset = (page - 1) * per_page
        query = "SELECT * FROM web_articles WHERE is_published = 1"
        params: list = []

        if sport:
            query += " AND sport = %s"
            params.append(sport)

        query += " ORDER BY created_at DESC LIMIT %s OFFSET %s"
        params.extend([per_page, offset])

        rows = self.db.fetchall(query, tuple(params))
        return [dict(row) for row in rows]

    def get_featured_web_articles(self, limit: int = 4) -> List[Dict]:
        """Get featured web articles."""
        rows = self.db.fetchall(
            """SELECT * FROM web_articles
               WHERE is_published = 1 AND is_featured = 1
               ORDER BY created_at DESC LIMIT %s""",
            (limit,)
        )
        return [dict(row) for row in rows]

    def get_latest_web_articles(self, limit: int = 12) -> List[Dict]:
        """Get the latest web articles."""
        rows = self.db.fetchall(
            """SELECT * FROM web_articles
               WHERE is_published = 1
               ORDER BY created_at DESC LIMIT %s""",
            (limit,)
        )
        return [dict(row) for row in rows]

    def increment_view_count(self, slug: str):
        """Increment the view count for a web article."""
        self.db.execute(
            "UPDATE web_articles SET view_count = view_count + 1 WHERE slug = %s",
            (slug,)
        )

    def get_web_article_count_by_sport(self, sport: Optional[str] = None) -> int:
        """Get total count of published web articles, optionally by sport."""
        if sport:
            row = self.db.fetchone(
                "SELECT COUNT(*) as count FROM web_articles WHERE is_published = 1 AND sport = %s",
                (sport,)
            )
        else:
            row = self.db.fetchone(
                "SELECT COUNT(*) as count FROM web_articles WHERE is_published = 1"
            )
        return row['count'] if row else 0

    def insert_article_teams(self, web_article_id: int, teams_list: List[Dict]):
        """
        Insert team associations for a web article.

        Args:
            web_article_id: The web_articles.id
            teams_list: List of dicts with team_slug, league_slug, score
        """
        if not teams_list:
            return

        for i, team in enumerate(teams_list):
            is_primary = 1 if i == 0 else 0
            try:
                self.db.execute(
                    """INSERT INTO article_teams
                       (web_article_id, league_slug, team_slug, is_primary)
                       VALUES (%s, %s, %s, %s)""",
                    (web_article_id, team["league_slug"], team["team_slug"], is_primary)
                )
            except Exception as e:
                logger.warning(f"Error inserting article_team: {e}")

        # Also update denormalized columns on web_articles
        primary = teams_list[0]
        try:
            self.db.execute(
                """UPDATE web_articles
                   SET primary_league = %s, primary_team = %s
                   WHERE id = %s""",
                (primary["league_slug"], primary["team_slug"], web_article_id)
            )
        except Exception as e:
            logger.warning(f"Error updating primary league/team: {e}")

    def get_related_web_articles(self, sport: str, exclude_slug: str, limit: int = 4) -> List[Dict]:
        """Get related web articles by sport, excluding current."""
        rows = self.db.fetchall(
            """SELECT * FROM web_articles
               WHERE is_published = 1 AND sport = %s AND slug != %s
               ORDER BY created_at DESC LIMIT %s""",
            (sport, exclude_slug, limit)
        )
        return [dict(row) for row in rows]

    # ── Comments ──

    def get_comments(self, web_article_id: int) -> List[Dict]:
        """Get visible comments for a web article."""
        rows = self.db.fetchall(
            """SELECT * FROM web_comments
               WHERE web_article_id = %s AND is_visible = 1
               ORDER BY created_at ASC""",
            (web_article_id,)
        )
        return [dict(row) for row in rows]

    def add_comment(
        self,
        web_article_id: int,
        user_name: str,
        user_initials: str,
        comment_text: str,
        user_id: Optional[int] = None,
    ) -> int:
        """Add a comment to a web article. Returns comment id."""
        cursor = self.db.execute(
            """INSERT INTO web_comments (web_article_id, user_name, user_initials, comment_text, user_id)
               VALUES (%s, %s, %s, %s, %s)""",
            (web_article_id, user_name, user_initials, comment_text, user_id)
        )
        return cursor.lastrowid

    def get_comment_count(self, web_article_id: int) -> int:
        """Get comment count for a web article."""
        row = self.db.fetchone(
            "SELECT COUNT(*) as count FROM web_comments WHERE web_article_id = %s AND is_visible = 1",
            (web_article_id,)
        )
        return row['count'] if row else 0

    # ===================
    # USERS
    # ===================

    def create_user(
        self,
        email: str,
        display_name: str,
        initials: str,
        password_hash: Optional[str] = None,
        auth_provider: str = "local",
        google_id: Optional[str] = None,
        avatar_url: Optional[str] = None,
    ) -> int:
        """Create a new user. Returns user id."""
        now = datetime_to_iso(utc_now())
        cursor = self.db.execute(
            """INSERT INTO users (email, password_hash, display_name, initials,
                avatar_url, auth_provider, google_id, created_at, updated_at)
               VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)""",
            (email, password_hash, display_name, initials,
             avatar_url, auth_provider, google_id, now, now)
        )
        return cursor.lastrowid

    def get_user_by_email(self, email: str) -> Optional[Dict]:
        """Get a user by email."""
        row = self.db.fetchone(
            "SELECT * FROM users WHERE email = %s",
            (email,)
        )
        return dict(row) if row else None

    def get_user_by_google_id(self, google_id: str) -> Optional[Dict]:
        """Get a user by Google ID."""
        row = self.db.fetchone(
            "SELECT * FROM users WHERE google_id = %s",
            (google_id,)
        )
        return dict(row) if row else None

    def get_user_by_id(self, user_id: int) -> Optional[Dict]:
        """Get a user by ID."""
        row = self.db.fetchone(
            "SELECT * FROM users WHERE id = %s",
            (user_id,)
        )
        return dict(row) if row else None

    def update_user_last_login(self, user_id: int):
        """Update the last_login_at timestamp for a user."""
        self.db.execute(
            "UPDATE users SET last_login_at = %s WHERE id = %s",
            (datetime_to_iso(utc_now()), user_id)
        )

    # ===================
    # SESSIONS
    # ===================

    def create_session(self, session_id: str, user_id: int, expires_at: str):
        """Create a new session."""
        self.db.execute(
            """INSERT INTO sessions (id, user_id, expires_at)
               VALUES (%s, %s, %s)""",
            (session_id, user_id, expires_at)
        )

    def get_user_by_session(self, session_id: str) -> Optional[Dict]:
        """Get a user by their session token. Returns None if expired or not found."""
        now = datetime_to_iso(utc_now())
        row = self.db.fetchone(
            """SELECT u.* FROM users u
               JOIN sessions s ON s.user_id = u.id
               WHERE s.id = %s AND s.expires_at > %s AND u.is_active = 1""",
            (session_id, now)
        )
        return dict(row) if row else None

    def delete_session(self, session_id: str):
        """Delete a session."""
        self.db.execute(
            "DELETE FROM sessions WHERE id = %s",
            (session_id,)
        )

    def cleanup_expired_sessions(self):
        """Remove all expired sessions."""
        now = datetime_to_iso(utc_now())
        self.db.execute(
            "DELETE FROM sessions WHERE expires_at <= %s",
            (now,)
        )


# Convenience function
def get_repository() -> Repository:
    """Get a Repository instance."""
    return Repository()
