<?php
/**
 * One-time migration + backfill script.
 * Creates article_teams table, adds columns, then tags existing articles.
 *
 * Run via browser: https://yoursite.com/migrate_and_backfill.php?key=YOUR_SECRET
 * Delete after use.
 */

// Auth: secret URL or query key
$SECRET = 'gf_migrate_2026';
$isRouter = defined('BASE_URL'); // loaded from index.php
if (!$isRouter && ($_GET['key'] ?? '') !== $SECRET) {
    http_response_code(403);
    die('Forbidden');
}

header('Content-Type: text/plain; charset=utf-8');

if (!$isRouter) {
    require __DIR__ . '/config.php';
}

// Connect to DB
$dsn = 'mysql:host=' . ($_ENV['DB_HOST'] ?? 'localhost')
     . ';dbname=' . ($_ENV['DB_NAME'] ?? 'goalfeed')
     . ';charset=utf8mb4';
$pdo = new PDO($dsn, $_ENV['DB_USER'] ?? '', $_ENV['DB_PASSWORD'] ?? '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "=== GoalFeed Migration & Backfill ===\n\n";

// ── Step 1: Create article_teams table ──
echo "1. Creating article_teams table...\n";
$pdo->exec("
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
");
echo "   OK\n";

// ── Step 2: Add columns if missing ──
echo "2. Adding primary_league/primary_team columns...\n";
$cols = $pdo->query("SHOW COLUMNS FROM web_articles LIKE 'primary_league'")->fetchAll();
if (empty($cols)) {
    $pdo->exec("ALTER TABLE web_articles ADD COLUMN primary_league VARCHAR(50) NULL AFTER category");
    $pdo->exec("ALTER TABLE web_articles ADD COLUMN primary_team VARCHAR(50) NULL AFTER primary_league");
    echo "   Added columns\n";
} else {
    echo "   Columns already exist\n";
}

// Add indexes (ignore if exist)
try { $pdo->exec("ALTER TABLE web_articles ADD INDEX idx_wa_league (primary_league)"); } catch (Exception $e) {}
try { $pdo->exec("ALTER TABLE web_articles ADD INDEX idx_wa_team (primary_team)"); } catch (Exception $e) {}
echo "   Indexes OK\n";

// ── Step 3: Team aliases (same as Python config) ──
$TEAM_ALIASES = [
    // La Liga
    'barcelona' => ['league' => 'laliga', 'aliases' => ['barcelona', 'barça', 'barca', 'blaugrana', 'culé', 'culers', 'fcb', 'fc barcelona']],
    'realmadrid' => ['league' => 'laliga', 'aliases' => ['real madrid', 'madrid', 'madridista', 'merengues', 'blancos', 'rmcf', 'los blancos']],
    'atlmadrid' => ['league' => 'laliga', 'aliases' => ['atletico madrid', 'atlético madrid', 'atletico de madrid', 'atlético de madrid', 'atl. madrid', 'atleti', 'colchoneros', 'rojiblanco']],
    'sevilla' => ['league' => 'laliga', 'aliases' => ['sevilla', 'sevilla fc', 'nervionenses', 'sevillismo']],
    'betis' => ['league' => 'laliga', 'aliases' => ['betis', 'real betis', 'verdiblanco', 'béticos', 'beticos']],
    'realsociedad' => ['league' => 'laliga', 'aliases' => ['real sociedad', 'la real', 'txuri-urdin', 'txuri urdin', 'donostiarra']],
    'villarreal' => ['league' => 'laliga', 'aliases' => ['villarreal', 'submarino amarillo', 'yellow submarine', 'groguet']],
    'athletic' => ['league' => 'laliga', 'aliases' => ['athletic', 'athletic club', 'athletic bilbao', 'athletic de bilbao', 'leones', 'los leones', 'zurigorri']],
    'valencia' => ['league' => 'laliga', 'aliases' => ['valencia cf', 'valencia', 'che', 'los che', 'murciélagos']],
    'celta' => ['league' => 'laliga', 'aliases' => ['celta', 'celta de vigo', 'celta vigo', 'celtiñas', 'celestes']],
    'osasuna' => ['league' => 'laliga', 'aliases' => ['osasuna', 'ca osasuna', 'rojillos', 'los rojillos']],
    'mallorca' => ['league' => 'laliga', 'aliases' => ['mallorca', 'rcd mallorca', 'bermellones']],
    'getafe' => ['league' => 'laliga', 'aliases' => ['getafe', 'getafe cf', 'azulones']],
    'girona' => ['league' => 'laliga', 'aliases' => ['girona', 'girona fc']],
    'espanyol' => ['league' => 'laliga', 'aliases' => ['espanyol', 'rcd espanyol', 'periquitos', 'pericos']],
    'rayovallecano' => ['league' => 'laliga', 'aliases' => ['rayo vallecano', 'rayo', 'franjirrojos', 'vallecanos']],
    'alaves' => ['league' => 'laliga', 'aliases' => ['alavés', 'alaves', 'deportivo alavés', 'deportivo alaves', 'babazorro']],
    'levante' => ['league' => 'laliga', 'aliases' => ['levante', 'levante ud', 'granotas']],
    'elche' => ['league' => 'laliga', 'aliases' => ['elche', 'elche cf', 'franjiverdes']],
    'realoviedo' => ['league' => 'laliga', 'aliases' => ['real oviedo', 'oviedo', 'carbayones', 'azules']],
    // Champions (non-Spanish)
    'bayernmunchen' => ['league' => 'champions', 'aliases' => ['bayern', 'bayern munich', 'bayern münchen', 'bayern munchen', 'fc bayern', 'bavarians']],
    'manchestercity' => ['league' => 'premierleague', 'aliases' => ['manchester city', 'man city', 'city', 'citizens', 'cityzens', 'mcfc']],
    'liverpool' => ['league' => 'premierleague', 'aliases' => ['liverpool', 'reds', 'lfc', 'liverpool fc', 'the reds', 'anfield']],
    'arsenal' => ['league' => 'premierleague', 'aliases' => ['arsenal', 'gunners', 'the gunners', 'afc', 'arsenal fc']],
    'chelsea' => ['league' => 'premierleague', 'aliases' => ['chelsea', 'blues', 'the blues', 'cfc', 'chelsea fc']],
    'psg' => ['league' => 'champions', 'aliases' => ['psg', 'paris saint-germain', 'paris saint germain', 'paris sg', 'parisinos']],
    'inter' => ['league' => 'seriea', 'aliases' => ['inter', 'inter milan', 'inter de milán', 'inter de milan', 'internazionale', 'nerazzurri']],
    'juventus' => ['league' => 'seriea', 'aliases' => ['juventus', 'juve', 'la vecchia signora', 'bianconeri', 'la juve']],
    'napoli' => ['league' => 'seriea', 'aliases' => ['napoli', 'nápoles', 'ssc napoli', 'partenopei', 'azzurri napoli']],
    'borussiadortmund' => ['league' => 'champions', 'aliases' => ['borussia dortmund', 'dortmund', 'bvb', 'die borussen']],
    'bayerleverkusen' => ['league' => 'champions', 'aliases' => ['bayer leverkusen', 'leverkusen', 'werkself', 'bayer 04']],
    'benfica' => ['league' => 'champions', 'aliases' => ['benfica', 'sl benfica', 'águias', 'encarnados']],
    'sporting' => ['league' => 'champions', 'aliases' => ['sporting', 'sporting cp', 'sporting lisboa', 'leões']],
    'ajax' => ['league' => 'champions', 'aliases' => ['ajax', 'ajax amsterdam', 'godenzonen', 'afc ajax']],
    'psv' => ['league' => 'champions', 'aliases' => ['psv', 'psv eindhoven']],
    'tottenham' => ['league' => 'premierleague', 'aliases' => ['tottenham', 'spurs', 'tottenham hotspur', 'thfc']],
    'newcastle' => ['league' => 'premierleague', 'aliases' => ['newcastle', 'newcastle united', 'magpies', 'toon', 'nufc']],
    'atalanta' => ['league' => 'seriea', 'aliases' => ['atalanta', 'atalanta bergamo', 'la dea', 'orobici']],
    'galatasaray' => ['league' => 'champions', 'aliases' => ['galatasaray', 'gala', 'cim bom']],
    'clubbrujas' => ['league' => 'champions', 'aliases' => ['club brujas', 'club brugge', 'bruges']],
    'milan' => ['league' => 'seriea', 'aliases' => ['ac milan', 'milan', 'rossoneri', 'diavolo', 'il milan']],
    // Serie A remaining
    'roma' => ['league' => 'seriea', 'aliases' => ['roma', 'as roma', 'giallorossi', 'la loba', 'romanisti']],
    'lazio' => ['league' => 'seriea', 'aliases' => ['lazio', 'ss lazio', 'biancocelesti', 'aquilotti']],
    'fiorentina' => ['league' => 'seriea', 'aliases' => ['fiorentina', 'acf fiorentina', 'viola', 'la viola', 'gigliati']],
    'torino' => ['league' => 'seriea', 'aliases' => ['torino', 'torino fc', 'toro', 'granata']],
    'bologna' => ['league' => 'seriea', 'aliases' => ['bologna', 'bologna fc', 'rossoblu']],
    'udinese' => ['league' => 'seriea', 'aliases' => ['udinese', 'udinese calcio', 'bianconeri friulani']],
    'genoa' => ['league' => 'seriea', 'aliases' => ['genoa', 'genoa cfc', 'grifone']],
    'cagliari' => ['league' => 'seriea', 'aliases' => ['cagliari', 'cagliari calcio', 'rossoblu sardi']],
    'lecce' => ['league' => 'seriea', 'aliases' => ['lecce', 'us lecce', 'salentini', 'giallorossi lecce']],
    'parma' => ['league' => 'seriea', 'aliases' => ['parma', 'parma calcio', 'ducali', 'crociati']],
    'hellasverona' => ['league' => 'seriea', 'aliases' => ['hellas verona', 'verona', 'mastini', 'gialloblu']],
    'como' => ['league' => 'seriea', 'aliases' => ['como', 'como 1907', 'lariani']],
    'sassuolo' => ['league' => 'seriea', 'aliases' => ['sassuolo', 'us sassuolo', 'neroverdi']],
    'pisa' => ['league' => 'seriea', 'aliases' => ['pisa', 'pisa sc', 'nerazzurri pisa']],
    'cremonese' => ['league' => 'seriea', 'aliases' => ['cremonese', 'us cremonese', 'grigiorossi']],
    // Premier League remaining
    'manchesterunited' => ['league' => 'premierleague', 'aliases' => ['manchester united', 'man united', 'man utd', 'red devils', 'mufc', 'united']],
    'astonvilla' => ['league' => 'premierleague', 'aliases' => ['aston villa', 'villa', 'villans', 'avfc']],
    'brighton' => ['league' => 'premierleague', 'aliases' => ['brighton', 'brighton & hove albion', 'brighton and hove', 'seagulls', 'bhafc']],
    'westham' => ['league' => 'premierleague', 'aliases' => ['west ham', 'west ham united', 'hammers', 'irons', 'whufc']],
    'crystalpalace' => ['league' => 'premierleague', 'aliases' => ['crystal palace', 'palace', 'eagles', 'cpfc']],
    'bournemouth' => ['league' => 'premierleague', 'aliases' => ['bournemouth', 'afc bournemouth', 'cherries']],
    'fulham' => ['league' => 'premierleague', 'aliases' => ['fulham', 'fulham fc', 'cottagers']],
    'wolverhampton' => ['league' => 'premierleague', 'aliases' => ['wolverhampton', 'wolves', 'wolverhampton wanderers', 'wwfc']],
    'everton' => ['league' => 'premierleague', 'aliases' => ['everton', 'toffees', 'efc', 'everton fc']],
    'brentford' => ['league' => 'premierleague', 'aliases' => ['brentford', 'brentford fc', 'bees']],
    'nottinghamforest' => ['league' => 'premierleague', 'aliases' => ['nottingham forest', 'forest', 'nffc', 'tricky trees']],
    'leicester' => ['league' => 'premierleague', 'aliases' => ['leicester', 'leicester city', 'foxes', 'lcfc']],
    'ipswich' => ['league' => 'premierleague', 'aliases' => ['ipswich', 'ipswich town', 'tractor boys', 'itfc']],
    'southampton' => ['league' => 'premierleague', 'aliases' => ['southampton', 'saints', 'soton', 'sfc']],
];

$TEAM_LEAGUE_MEMBERSHIP = [
    'barcelona' => ['laliga', 'champions'],
    'realmadrid' => ['laliga', 'champions'],
    'atlmadrid' => ['laliga', 'champions'],
    'villarreal' => ['laliga', 'champions'],
    'athletic' => ['laliga', 'champions'],
    'manchestercity' => ['premierleague', 'champions'],
    'liverpool' => ['premierleague', 'champions'],
    'arsenal' => ['premierleague', 'champions'],
    'chelsea' => ['premierleague', 'champions'],
    'tottenham' => ['premierleague', 'champions'],
    'newcastle' => ['premierleague', 'champions'],
    'inter' => ['seriea', 'champions'],
    'juventus' => ['seriea', 'champions'],
    'napoli' => ['seriea', 'champions'],
    'atalanta' => ['seriea', 'champions'],
    'milan' => ['seriea', 'champions'],
];

$LEAGUE_KEYWORDS = [
    'laliga' => ['laliga', 'la liga', 'liga española', 'liga espanola', 'primera división', 'primera division', 'liga santander', 'liga ea sports', 'jornada'],
    'champions' => ['champions league', 'champions', 'ucl', 'uefa champions', 'champions league draw', 'sorteo champions', 'fase de grupos champions', 'octavos champions', 'cuartos champions', 'semifinal champions', 'final champions', 'orejona'],
    'seriea' => ['serie a', 'calcio', 'scudetto', 'seria a', 'liga italiana', 'campeonato italiano'],
    'premierleague' => ['premier league', 'premier', 'epl', 'liga inglesa', 'english premier', 'premiership', 'fa cup', 'carabao cup', 'league cup'],
];

// ── Classification functions ──

function detect_league_context(string $text, array $leagueKeywords): ?string {
    $textLower = mb_strtolower($text);
    $scores = [];
    foreach ($leagueKeywords as $slug => $keywords) {
        $score = 0;
        foreach ($keywords as $kw) {
            $pattern = '/\b' . preg_quote(mb_strtolower($kw), '/') . '\b/u';
            $score += preg_match_all($pattern, $textLower);
        }
        if ($score > 0) $scores[$slug] = $score;
    }
    if (empty($scores)) return null;
    arsort($scores);
    return array_key_first($scores);
}

function classify_teams(string $title, string $body, array $teamAliases, array $membership, array $leagueKeywords): array {
    $titleLower = mb_strtolower($title);
    $bodyLower = mb_strtolower($body);
    $fullText = $titleLower . ' ' . $bodyLower;

    $leagueContext = detect_league_context($fullText, $leagueKeywords);

    $results = [];
    foreach ($teamAliases as $teamSlug => $info) {
        $primaryLeague = $info['league'];
        $score = 0;
        foreach ($info['aliases'] as $alias) {
            $pattern = '/\b' . preg_quote(mb_strtolower($alias), '/') . '\b/u';
            $titleHits = preg_match_all($pattern, $titleLower);
            $bodyHits = preg_match_all($pattern, $bodyLower);
            $score += $titleHits * 3 + $bodyHits;
        }
        if ($score < 2) continue;

        $leagueSlug = $primaryLeague;
        if (isset($membership[$teamSlug])) {
            if ($leagueContext && in_array($leagueContext, $membership[$teamSlug])) {
                $leagueSlug = $leagueContext;
            }
        }
        $results[] = ['team_slug' => $teamSlug, 'league_slug' => $leagueSlug, 'score' => $score];
    }

    usort($results, fn($a, $b) => $b['score'] - $a['score']);
    return $results;
}

// ── Step 4: Backfill ──
echo "\n3. Backfilling article_teams...\n";

$articles = $pdo->query(
    "SELECT id, headline, subtitle, body_text FROM web_articles WHERE is_published = 1 ORDER BY id"
)->fetchAll();

$total = count($articles);
echo "   Found {$total} published articles\n";

$tagged = 0;
$skipped = 0;

$insertStmt = $pdo->prepare(
    "INSERT INTO article_teams (web_article_id, league_slug, team_slug, is_primary) VALUES (?, ?, ?, ?)"
);
$updateStmt = $pdo->prepare(
    "UPDATE web_articles SET primary_league = ?, primary_team = ? WHERE id = ?"
);
$checkStmt = $pdo->prepare(
    "SELECT id FROM article_teams WHERE web_article_id = ? LIMIT 1"
);

foreach ($articles as $i => $art) {
    $webId = $art['id'];

    // Skip if already tagged
    $checkStmt->execute([$webId]);
    if ($checkStmt->fetch()) {
        $skipped++;
        continue;
    }

    $title = $art['headline'] ?? '';
    $body = ($art['body_text'] ?? '') . ' ' . ($art['subtitle'] ?? '');

    $teams = classify_teams($title, $body, $TEAM_ALIASES, $TEAM_LEAGUE_MEMBERSHIP, $LEAGUE_KEYWORDS);

    if (!empty($teams)) {
        foreach ($teams as $idx => $team) {
            $isPrimary = ($idx === 0) ? 1 : 0;
            $insertStmt->execute([$webId, $team['league_slug'], $team['team_slug'], $isPrimary]);
        }
        // Denormalized columns
        $updateStmt->execute([$teams[0]['league_slug'], $teams[0]['team_slug'], $webId]);
        $tagged++;

        if ($tagged % 50 === 0) {
            echo "   Progress: {$tagged} tagged, {$skipped} skipped, " . ($i + 1) . "/{$total}\n";
        }
    }
}

echo "\n=== DONE ===\n";
echo "Tagged: {$tagged}\n";
echo "Skipped (already tagged): {$skipped}\n";
echo "Total articles: {$total}\n";
echo "\nDELETE THIS FILE from the server now!\n";
