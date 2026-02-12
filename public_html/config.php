<?php
/**
 * GoalFeed PHP Configuration
 * DB credentials, OAuth keys, constants.
 */

// Load .env from project root (one level up from public_html)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$key, $val] = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $val;
            putenv("$key=$val");
        }
    }
}

// ── Database ──
define('DB_HOST',     $_ENV['DB_HOST']     ?? 'localhost');
define('DB_USER',     $_ENV['DB_USER']     ?? '');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');
define('DB_NAME',     $_ENV['DB_NAME']     ?? '');
define('DB_CHARSET',  'utf8mb4');

// ── Site ──
define('BASE_URL',          $_ENV['WEB_BASE_URL']     ?? 'https://goal-feed.com');
define('ARTICLES_PER_PAGE', (int)($_ENV['ARTICLES_PER_PAGE'] ?? 12));

// ── Google OAuth ──
define('GOOGLE_CLIENT_ID',     $_ENV['GOOGLE_CLIENT_ID']     ?? '');
define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
define('GOOGLE_REDIRECT_URI',  $_ENV['GOOGLE_REDIRECT_URI']  ?? '');

// ── Session ──
define('SESSION_COOKIE',       'gf_session');
define('SESSION_MAX_AGE_DAYS', 30);

// ── Sport display config ──
define('SPORT_DISPLAY', [
    'football_eu' => [
        'name'    => 'Fútbol',
        'hashtag' => '#Fútbol',
        'emoji'   => '⚽',
    ],
]);

// ── Status config ──
define('STATUS_CONFIG', [
    'CONFIRMADO' => [
        'emoji' => '✅',
        'label' => 'CONFIRMADO',
        'description' => 'Información verificada de fuente oficial o múltiples fuentes',
    ],
    'RUMOR' => [
        'emoji' => '🔮',
        'label' => 'RUMOR',
        'description' => 'Información de una única fuente no oficial',
    ],
    'EN_DESARROLLO' => [
        'emoji' => '🔄',
        'label' => 'EN DESARROLLO',
        'description' => 'Noticia en curso, pueden haber actualizaciones',
    ],
]);
