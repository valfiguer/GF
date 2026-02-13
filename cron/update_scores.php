<?php
/**
 * Cron: Pre-compute feed scores for web_articles.
 * Run every 15 minutes: */15 * * * * php /path/to/cron/update_scores.php
 *
 * Updates the `score` column (already exists in DB) so it can be used
 * for ORDER BY instead of computing inline. Optional optimization.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Database.php';

Database::execute(
    "UPDATE web_articles
     SET score = (1000 / (1 + TIMESTAMPDIFF(HOUR, created_at, NOW()) / 6))
                 + (LOG(1 + IFNULL(view_count, 0)) * 10)
                 + (CASE WHEN is_featured = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 200 ELSE 0 END)
     WHERE is_published = 1"
);

echo date('Y-m-d H:i:s') . " — Scores updated.\n";
