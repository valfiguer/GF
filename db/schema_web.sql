-- GoalFeed Web Portal Schema (MySQL/MariaDB)

CREATE TABLE IF NOT EXISTS web_articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    slug VARCHAR(512) NOT NULL UNIQUE,
    headline VARCHAR(1024) NOT NULL,
    subtitle VARCHAR(1024) NULL,
    body_html MEDIUMTEXT NOT NULL,
    body_text MEDIUMTEXT NULL,
    meta_description VARCHAR(512) NULL,
    meta_keywords VARCHAR(512) NULL,
    og_title VARCHAR(512) NULL,
    og_description VARCHAR(512) NULL,
    og_image_url VARCHAR(2048) NULL,
    sport VARCHAR(50) NOT NULL,
    category VARCHAR(100) NULL,
    status VARCHAR(20) DEFAULT 'RUMOR',
    image_filename VARCHAR(512) NULL,
    image_url VARCHAR(2048) NULL,
    source_name VARCHAR(255) NULL,
    source_url VARCHAR(2048) NULL,
    is_published TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    view_count INT DEFAULT 0,
    score INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_web_articles_slug (slug(191)),
    INDEX idx_web_articles_article_id (article_id),
    INDEX idx_web_articles_sport (sport),
    INDEX idx_web_articles_is_published (is_published),
    INDEX idx_web_articles_is_featured (is_featured),
    INDEX idx_web_articles_created_at (created_at),
    INDEX idx_web_articles_score (score),
    FOREIGN KEY (article_id) REFERENCES articles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS web_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    web_article_id INT NOT NULL,
    user_id INT NULL,
    user_name VARCHAR(255) NOT NULL,
    user_initials VARCHAR(10) NOT NULL DEFAULT '??',
    comment_text TEXT NOT NULL,
    is_visible TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_web_comments_article (web_article_id),
    INDEX idx_web_comments_created (created_at),
    INDEX idx_web_comments_user (user_id),
    FOREIGN KEY (web_article_id) REFERENCES web_articles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NULL,
    display_name VARCHAR(255) NOT NULL,
    initials VARCHAR(10) NOT NULL DEFAULT '??',
    avatar_url VARCHAR(2048) NULL,
    auth_provider VARCHAR(20) NOT NULL DEFAULT 'local',
    google_id VARCHAR(255) NULL UNIQUE,
    is_active TINYINT(1) DEFAULT 1,
    is_admin TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login_at DATETIME NULL,
    INDEX idx_users_email (email),
    INDEX idx_users_google_id (google_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions table
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) NOT NULL PRIMARY KEY,
    user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    INDEX idx_sessions_user (user_id),
    INDEX idx_sessions_expires (expires_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
