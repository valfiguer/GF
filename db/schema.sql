-- GoalFeed Database Schema (MySQL/MariaDB)

-- RSS Sources table
CREATE TABLE IF NOT EXISTS sources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    url VARCHAR(512) NOT NULL UNIQUE,
    sport_hint VARCHAR(50) NOT NULL,
    weight INT DEFAULT 10,
    active TINYINT(1) DEFAULT 1,
    last_fetched_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Articles table
CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_id INT NULL,
    title VARCHAR(1024) NOT NULL,
    normalized_title VARCHAR(1024) NOT NULL,
    link VARCHAR(2048) NOT NULL,
    canonical_url VARCHAR(2048) NOT NULL,
    summary TEXT NULL,
    published_at DATETIME NULL,
    sport VARCHAR(50) NOT NULL,
    category VARCHAR(100) NULL,
    status VARCHAR(20) DEFAULT 'RUMOR',
    score INT DEFAULT 0,
    content_hash VARCHAR(64) NOT NULL,
    image_url VARCHAR(2048) NULL,
    source_name VARCHAR(255) NULL,
    source_domain VARCHAR(255) NULL,
    is_duplicate TINYINT(1) DEFAULT 0,
    is_posted TINYINT(1) DEFAULT 0,
    is_digested TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_articles_canonical_url (canonical_url(191)),
    INDEX idx_articles_content_hash (content_hash),
    INDEX idx_articles_sport (sport),
    INDEX idx_articles_score (score),
    INDEX idx_articles_created_at (created_at),
    INDEX idx_articles_is_posted (is_posted),
    INDEX idx_articles_is_duplicate (is_duplicate),
    FOREIGN KEY (source_id) REFERENCES sources(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Posts table
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NULL,
    telegram_message_id INT NULL,
    telegram_chat_id VARCHAR(100) NULL,
    caption TEXT NULL,
    image_path VARCHAR(512) NULL,
    sport VARCHAR(50) NULL,
    post_type VARCHAR(20) DEFAULT 'single',
    posted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_posts_posted_at (posted_at),
    INDEX idx_posts_sport (sport),
    INDEX idx_posts_post_type (post_type),
    FOREIGN KEY (article_id) REFERENCES articles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Digests table
CREATE TABLE IF NOT EXISTS digests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    telegram_message_id INT NULL,
    telegram_chat_id VARCHAR(100) NULL,
    caption TEXT NULL,
    image_path VARCHAR(512) NULL,
    sport VARCHAR(50) NOT NULL,
    article_count INT DEFAULT 0,
    posted_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Digest items
CREATE TABLE IF NOT EXISTS digest_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    digest_id INT NOT NULL,
    article_id INT NOT NULL,
    position INT DEFAULT 0,
    INDEX idx_digest_items_digest_id (digest_id),
    FOREIGN KEY (digest_id) REFERENCES digests(id),
    FOREIGN KEY (article_id) REFERENCES articles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(191) PRIMARY KEY,
    `value` TEXT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daily stats table
CREATE TABLE IF NOT EXISTS daily_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `date` VARCHAR(10) NOT NULL UNIQUE,
    post_count INT DEFAULT 0,
    digest_count INT DEFAULT 0,
    articles_fetched INT DEFAULT 0,
    articles_duplicated INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_daily_stats_date (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Live events table
CREATE TABLE IF NOT EXISTS live_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id VARCHAR(100) NOT NULL,
    league_id INT NULL,
    league_name VARCHAR(255) NULL,
    home_team VARCHAR(255) NOT NULL,
    away_team VARCHAR(255) NOT NULL,
    home_score INT DEFAULT 0,
    away_score INT DEFAULT 0,
    event_type VARCHAR(50) NOT NULL,
    event_minute INT NULL,
    event_player VARCHAR(255) NULL,
    event_detail VARCHAR(512) NULL,
    telegram_message_id INT NULL,
    telegram_chat_id VARCHAR(100) NULL,
    is_published TINYINT(1) DEFAULT 0,
    published_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_live_event (match_id, event_type, event_minute, event_player(100)),
    INDEX idx_live_events_match_id (match_id),
    INDEX idx_live_events_event_type (event_type),
    INDEX idx_live_events_created_at (created_at),
    INDEX idx_live_events_is_published (is_published)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Live matches table
CREATE TABLE IF NOT EXISTS live_matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id VARCHAR(100) NOT NULL UNIQUE,
    league_id INT NULL,
    league_name VARCHAR(255) NULL,
    home_team VARCHAR(255) NOT NULL,
    away_team VARCHAR(255) NOT NULL,
    home_score INT DEFAULT 0,
    away_score INT DEFAULT 0,
    match_status VARCHAR(20) NULL,
    current_minute INT NULL,
    events_published INT DEFAULT 0,
    last_event_at DATETIME NULL,
    is_top_team_match TINYINT(1) DEFAULT 0,
    match_start DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_live_matches_match_id (match_id),
    INDEX idx_live_matches_match_status (match_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT IGNORE INTO settings (`key`, `value`) VALUES ('initialized', 'true');
INSERT IGNORE INTO settings (`key`, `value`) VALUES ('version', '1.1.0')
