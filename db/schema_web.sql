-- GoalFeed Web Portal Schema
-- Table for AI-generated web articles

CREATE TABLE IF NOT EXISTS web_articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,

    -- Reference to original article
    article_id INTEGER NOT NULL,

    -- URL and content
    slug TEXT NOT NULL UNIQUE,
    headline TEXT NOT NULL,
    subtitle TEXT,
    body_html TEXT NOT NULL,
    body_text TEXT,

    -- SEO
    meta_description TEXT,
    meta_keywords TEXT,
    og_title TEXT,
    og_description TEXT,
    og_image_url TEXT,

    -- Classification (copied from parent article)
    sport TEXT NOT NULL,
    category TEXT,
    status TEXT DEFAULT 'RUMOR',

    -- Image
    image_filename TEXT,
    image_url TEXT,

    -- Source
    source_name TEXT,
    source_url TEXT,

    -- Flags
    is_published INTEGER DEFAULT 1,
    is_featured INTEGER DEFAULT 0,
    view_count INTEGER DEFAULT 0,
    score INTEGER DEFAULT 0,

    -- Timestamps
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (article_id) REFERENCES articles(id)
);

-- Indexes for web_articles
CREATE INDEX IF NOT EXISTS idx_web_articles_slug ON web_articles(slug);
CREATE INDEX IF NOT EXISTS idx_web_articles_article_id ON web_articles(article_id);
CREATE INDEX IF NOT EXISTS idx_web_articles_sport ON web_articles(sport);
CREATE INDEX IF NOT EXISTS idx_web_articles_is_published ON web_articles(is_published);
CREATE INDEX IF NOT EXISTS idx_web_articles_is_featured ON web_articles(is_featured);
CREATE INDEX IF NOT EXISTS idx_web_articles_created_at ON web_articles(created_at);
CREATE INDEX IF NOT EXISTS idx_web_articles_score ON web_articles(score DESC);

-- Comments on web articles
CREATE TABLE IF NOT EXISTS web_comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    web_article_id INTEGER NOT NULL,
    user_name TEXT NOT NULL,
    user_initials TEXT NOT NULL DEFAULT '??',
    comment_text TEXT NOT NULL,
    is_visible INTEGER DEFAULT 1,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (web_article_id) REFERENCES web_articles(id)
);

CREATE INDEX IF NOT EXISTS idx_web_comments_article ON web_comments(web_article_id);
CREATE INDEX IF NOT EXISTS idx_web_comments_created ON web_comments(created_at);
