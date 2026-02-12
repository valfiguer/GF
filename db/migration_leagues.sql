-- GoalFeed: League & Team tagging migration
-- Run once before deploy.

-- New table for article ↔ team associations
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Denormalized columns on web_articles for fast filtering
ALTER TABLE web_articles
    ADD COLUMN IF NOT EXISTS primary_league VARCHAR(50) NULL AFTER category,
    ADD COLUMN IF NOT EXISTS primary_team VARCHAR(50) NULL AFTER primary_league;

-- Indexes (ignore error if already exists)
ALTER TABLE web_articles ADD INDEX idx_wa_league (primary_league);
ALTER TABLE web_articles ADD INDEX idx_wa_team (primary_team);
