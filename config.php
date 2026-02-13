<?php
/**
 * GoalFeed PHP Configuration
 * DB credentials, OAuth keys, constants.
 */

// Load .env from same directory
$envFile = __DIR__ . '/.env';
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

// ── Leagues & Teams ──
define('LEAGUES', [
    'laliga' => [
        'name_es' => 'La Liga', 'name_en' => 'La Liga',
        'slug' => 'laliga',
        'logo' => '/static/images/leagues/laliga.png',
        'teams' => [
            'barcelona'     => ['name_es' => 'FC Barcelona',      'name_en' => 'FC Barcelona',      'logo' => '/static/images/laliga/barcelona.png'],
            'realmadrid'    => ['name_es' => 'Real Madrid',       'name_en' => 'Real Madrid',       'logo' => '/static/images/laliga/realmadrid.png'],
            'atlmadrid'     => ['name_es' => 'Atlético de Madrid','name_en' => 'Atlético Madrid',   'logo' => '/static/images/laliga/atlmadrid.png'],
            'sevilla'       => ['name_es' => 'Sevilla FC',        'name_en' => 'Sevilla FC',        'logo' => '/static/images/laliga/sevilla.png'],
            'betis'         => ['name_es' => 'Real Betis',        'name_en' => 'Real Betis',        'logo' => '/static/images/laliga/betis.png'],
            'realsociedad'  => ['name_es' => 'Real Sociedad',     'name_en' => 'Real Sociedad',     'logo' => '/static/images/laliga/realsociedad.png'],
            'villarreal'    => ['name_es' => 'Villarreal CF',     'name_en' => 'Villarreal CF',     'logo' => '/static/images/laliga/villarreal.png'],
            'athletic'      => ['name_es' => 'Athletic Club',     'name_en' => 'Athletic Club',     'logo' => '/static/images/laliga/athletic.png'],
            'valencia'      => ['name_es' => 'Valencia CF',       'name_en' => 'Valencia CF',       'logo' => '/static/images/laliga/valencia.png'],
            'celta'         => ['name_es' => 'RC Celta',          'name_en' => 'Celta Vigo',        'logo' => '/static/images/laliga/celta.png'],
            'osasuna'       => ['name_es' => 'CA Osasuna',        'name_en' => 'CA Osasuna',        'logo' => '/static/images/laliga/osasuna.png'],
            'mallorca'      => ['name_es' => 'RCD Mallorca',      'name_en' => 'RCD Mallorca',      'logo' => '/static/images/laliga/mallorca.png'],
            'getafe'        => ['name_es' => 'Getafe CF',         'name_en' => 'Getafe CF',         'logo' => '/static/images/laliga/getafe.png'],
            'girona'        => ['name_es' => 'Girona FC',         'name_en' => 'Girona FC',         'logo' => '/static/images/laliga/girona.png'],
            'espanyol'      => ['name_es' => 'RCD Espanyol',      'name_en' => 'RCD Espanyol',      'logo' => '/static/images/laliga/espanyol.png'],
            'rayovallecano' => ['name_es' => 'Rayo Vallecano',    'name_en' => 'Rayo Vallecano',    'logo' => '/static/images/laliga/rayovallecano.png'],
            'alaves'        => ['name_es' => 'Deportivo Alavés',  'name_en' => 'Deportivo Alavés',  'logo' => '/static/images/laliga/alaves.png'],
            'levante'       => ['name_es' => 'Levante UD',        'name_en' => 'Levante UD',        'logo' => '/static/images/laliga/levante.png'],
            'elche'         => ['name_es' => 'Elche CF',          'name_en' => 'Elche CF',          'logo' => '/static/images/laliga/elche.png'],
            'realoviedo'    => ['name_es' => 'Real Oviedo',       'name_en' => 'Real Oviedo',       'logo' => '/static/images/laliga/realoviedo.png'],
        ],
    ],
    'champions' => [
        'name_es' => 'Champions League', 'name_en' => 'Champions League',
        'slug' => 'champions',
        'logo' => '/static/images/leagues/champions.jpg',
        'teams' => [
            'realmadrid'       => ['name_es' => 'Real Madrid',         'name_en' => 'Real Madrid',         'logo' => '/static/images/champions_league/realmadrid.png'],
            'barcelona'        => ['name_es' => 'FC Barcelona',        'name_en' => 'FC Barcelona',        'logo' => '/static/images/champions_league/barcelona.png'],
            'atlmadrid'        => ['name_es' => 'Atlético de Madrid',  'name_en' => 'Atlético Madrid',     'logo' => '/static/images/champions_league/atlmadrid.png'],
            'bayernmunchen'    => ['name_es' => 'Bayern Múnich',       'name_en' => 'Bayern Munich',       'logo' => '/static/images/champions_league/bayernmunchen.png'],
            'manchestercity'   => ['name_es' => 'Manchester City',     'name_en' => 'Manchester City',     'logo' => '/static/images/champions_league/manchestercity.png'],
            'liverpool'        => ['name_es' => 'Liverpool',           'name_en' => 'Liverpool',           'logo' => '/static/images/champions_league/liverpool.png'],
            'arsenal'          => ['name_es' => 'Arsenal',             'name_en' => 'Arsenal',             'logo' => '/static/images/champions_league/arsenal.png'],
            'chelsea'          => ['name_es' => 'Chelsea',             'name_en' => 'Chelsea',             'logo' => '/static/images/champions_league/chelsea.png'],
            'psg'              => ['name_es' => 'Paris Saint-Germain', 'name_en' => 'Paris Saint-Germain', 'logo' => '/static/images/champions_league/psg.png'],
            'inter'            => ['name_es' => 'Inter de Milán',      'name_en' => 'Inter Milan',         'logo' => '/static/images/champions_league/inter.png'],
            'juventus'         => ['name_es' => 'Juventus',            'name_en' => 'Juventus',            'logo' => '/static/images/champions_league/juventus.png'],
            'napoli'           => ['name_es' => 'Nápoles',             'name_en' => 'Napoli',              'logo' => '/static/images/champions_league/napoli.png'],
            'borussiadortmund' => ['name_es' => 'Borussia Dortmund',   'name_en' => 'Borussia Dortmund',   'logo' => '/static/images/champions_league/borussiadortmund.png'],
            'bayerleverkusen'  => ['name_es' => 'Bayer Leverkusen',    'name_en' => 'Bayer Leverkusen',    'logo' => '/static/images/champions_league/bayerleverkusen.png'],
            'benfica'          => ['name_es' => 'Benfica',             'name_en' => 'Benfica',             'logo' => '/static/images/champions_league/benfica.png'],
            'sporting'         => ['name_es' => 'Sporting CP',         'name_en' => 'Sporting CP',         'logo' => '/static/images/champions_league/sporting.png'],
            'ajax'             => ['name_es' => 'Ajax',                'name_en' => 'Ajax',                'logo' => '/static/images/champions_league/ajax.png'],
            'psv'              => ['name_es' => 'PSV Eindhoven',       'name_en' => 'PSV Eindhoven',       'logo' => '/static/images/champions_league/psv.png'],
            'tottenham'        => ['name_es' => 'Tottenham',           'name_en' => 'Tottenham',           'logo' => '/static/images/champions_league/tottenham.png'],
            'newcastle'        => ['name_es' => 'Newcastle United',    'name_en' => 'Newcastle United',    'logo' => '/static/images/champions_league/newcastle.png'],
            'atalanta'         => ['name_es' => 'Atalanta',            'name_en' => 'Atalanta',            'logo' => '/static/images/champions_league/atalanta.png'],
            'villarreal'       => ['name_es' => 'Villarreal CF',       'name_en' => 'Villarreal CF',       'logo' => '/static/images/champions_league/villarreal.png'],
            'athletic'         => ['name_es' => 'Athletic Club',       'name_en' => 'Athletic Club',       'logo' => '/static/images/champions_league/athletic.png'],
            'galatasaray'      => ['name_es' => 'Galatasaray',         'name_en' => 'Galatasaray',         'logo' => '/static/images/champions_league/galatasaray.png'],
            'clubbrujas'       => ['name_es' => 'Club Brujas',         'name_en' => 'Club Brugge',         'logo' => '/static/images/champions_league/clubbrujas.png'],
            'milan'            => ['name_es' => 'AC Milan',            'name_en' => 'AC Milan',            'logo' => '/static/images/seriea/milan.png'],
        ],
    ],
    'seriea' => [
        'name_es' => 'Serie A', 'name_en' => 'Serie A',
        'slug' => 'seriea',
        'logo' => '/static/images/leagues/italia.png',
        'teams' => [
            'inter'        => ['name_es' => 'Inter de Milán',   'name_en' => 'Inter Milan',       'logo' => '/static/images/seriea/inter.png'],
            'milan'        => ['name_es' => 'AC Milan',         'name_en' => 'AC Milan',          'logo' => '/static/images/seriea/milan.png'],
            'juventus'     => ['name_es' => 'Juventus',         'name_en' => 'Juventus',          'logo' => '/static/images/seriea/juventus.png'],
            'napoli'       => ['name_es' => 'Nápoles',          'name_en' => 'Napoli',            'logo' => '/static/images/seriea/napoli.png'],
            'roma'         => ['name_es' => 'AS Roma',          'name_en' => 'AS Roma',           'logo' => '/static/images/seriea/roma.png'],
            'lazio'        => ['name_es' => 'Lazio',            'name_en' => 'Lazio',             'logo' => '/static/images/seriea/lazio.png'],
            'atalanta'     => ['name_es' => 'Atalanta',         'name_en' => 'Atalanta',          'logo' => '/static/images/seriea/atalanta.png'],
            'fiorentina'   => ['name_es' => 'Fiorentina',       'name_en' => 'Fiorentina',        'logo' => '/static/images/seriea/fiorentina.png'],
            'torino'       => ['name_es' => 'Torino',           'name_en' => 'Torino',            'logo' => '/static/images/seriea/torino.png'],
            'bologna'      => ['name_es' => 'Bolonia',          'name_en' => 'Bologna',           'logo' => '/static/images/seriea/bologna.png'],
            'udinese'      => ['name_es' => 'Udinese',          'name_en' => 'Udinese',           'logo' => '/static/images/seriea/udinese.png'],
            'genoa'        => ['name_es' => 'Genoa',            'name_en' => 'Genoa',             'logo' => '/static/images/seriea/genoa.png'],
            'cagliari'     => ['name_es' => 'Cagliari',         'name_en' => 'Cagliari',          'logo' => '/static/images/seriea/cagliari.png'],
            'lecce'        => ['name_es' => 'Lecce',            'name_en' => 'Lecce',             'logo' => '/static/images/seriea/lecce.png'],
            'parma'        => ['name_es' => 'Parma',            'name_en' => 'Parma',             'logo' => '/static/images/seriea/parma.png'],
            'hellasverona' => ['name_es' => 'Hellas Verona',    'name_en' => 'Hellas Verona',     'logo' => '/static/images/seriea/hellasverona.png'],
            'como'         => ['name_es' => 'Como 1907',        'name_en' => 'Como 1907',         'logo' => '/static/images/seriea/como.png'],
            'sassuolo'     => ['name_es' => 'Sassuolo',         'name_en' => 'Sassuolo',          'logo' => '/static/images/seriea/sassuolo.png'],
            'pisa'         => ['name_es' => 'Pisa',             'name_en' => 'Pisa',              'logo' => '/static/images/seriea/pisa.png'],
            'cremonese'    => ['name_es' => 'Cremonese',        'name_en' => 'Cremonese',         'logo' => '/static/images/seriea/cremonese.png'],
        ],
    ],
    'premierleague' => [
        'name_es' => 'Premier League', 'name_en' => 'Premier League',
        'slug' => 'premierleague',
        'logo' => '/static/images/leagues/inglaterra.png',
        'teams' => [
            'manchestercity'   => ['name_es' => 'Manchester City',    'name_en' => 'Manchester City',    'logo' => '/static/images/premierleague/manchestercity.png'],
            'arsenal'          => ['name_es' => 'Arsenal',            'name_en' => 'Arsenal',            'logo' => '/static/images/premierleague/arsenal.png'],
            'liverpool'        => ['name_es' => 'Liverpool',          'name_en' => 'Liverpool',          'logo' => '/static/images/premierleague/liverpool.png'],
            'chelsea'          => ['name_es' => 'Chelsea',            'name_en' => 'Chelsea',            'logo' => '/static/images/premierleague/chelsea.png'],
            'manchesterunited' => ['name_es' => 'Manchester United',  'name_en' => 'Manchester United',  'logo' => '/static/images/premierleague/manchesterunited.png'],
            'tottenham'        => ['name_es' => 'Tottenham Hotspur', 'name_en' => 'Tottenham Hotspur', 'logo' => '/static/images/premierleague/tottenham.png'],
            'newcastle'        => ['name_es' => 'Newcastle United',   'name_en' => 'Newcastle United',   'logo' => '/static/images/premierleague/newcastle.png'],
            'astonvilla'       => ['name_es' => 'Aston Villa',        'name_en' => 'Aston Villa',        'logo' => '/static/images/premierleague/astonvilla.png'],
            'brighton'         => ['name_es' => 'Brighton',           'name_en' => 'Brighton',           'logo' => '/static/images/premierleague/brighton.png'],
            'westham'          => ['name_es' => 'West Ham United',    'name_en' => 'West Ham United',    'logo' => '/static/images/premierleague/westham.png'],
            'crystalpalace'    => ['name_es' => 'Crystal Palace',     'name_en' => 'Crystal Palace',     'logo' => '/static/images/premierleague/crystalpalace.png'],
            'bournemouth'      => ['name_es' => 'Bournemouth',        'name_en' => 'Bournemouth',        'logo' => '/static/images/premierleague/bournemouth.png'],
            'fulham'           => ['name_es' => 'Fulham',             'name_en' => 'Fulham',             'logo' => '/static/images/premierleague/fulham.png'],
            'wolverhampton'    => ['name_es' => 'Wolverhampton',      'name_en' => 'Wolverhampton',      'logo' => '/static/images/premierleague/wolverhampton.png'],
            'everton'          => ['name_es' => 'Everton',            'name_en' => 'Everton',            'logo' => '/static/images/premierleague/everton.png'],
            'brentford'        => ['name_es' => 'Brentford',          'name_en' => 'Brentford',          'logo' => '/static/images/premierleague/brentford.png'],
            'nottinghamforest' => ['name_es' => 'Nottingham Forest',  'name_en' => 'Nottingham Forest',  'logo' => '/static/images/premierleague/nottinghamforest.png'],
            'leicester'        => ['name_es' => 'Leicester City',     'name_en' => 'Leicester City',     'logo' => '/static/images/premierleague/leicester.png'],
            'ipswich'          => ['name_es' => 'Ipswich Town',       'name_en' => 'Ipswich Town',       'logo' => '/static/images/premierleague/ipswich.png'],
            'southampton'      => ['name_es' => 'Southampton',        'name_en' => 'Southampton',        'logo' => '/static/images/premierleague/southampton.png'],
        ],
    ],
    'copadelrey' => [
        'name_es' => 'Copa del Rey', 'name_en' => 'Copa del Rey',
        'slug' => 'copadelrey',
        'logo' => '/static/images/leagues/copadelrey.png',
        'teams' => [
            'barcelona'     => ['name_es' => 'FC Barcelona',       'name_en' => 'FC Barcelona',      'logo' => '/static/images/laliga/barcelona.png'],
            'realmadrid'    => ['name_es' => 'Real Madrid',        'name_en' => 'Real Madrid',       'logo' => '/static/images/laliga/realmadrid.png'],
            'atlmadrid'     => ['name_es' => 'Atlético de Madrid', 'name_en' => 'Atlético Madrid',   'logo' => '/static/images/laliga/atlmadrid.png'],
            'athletic'      => ['name_es' => 'Athletic Club',      'name_en' => 'Athletic Club',     'logo' => '/static/images/laliga/athletic.png'],
            'realsociedad'  => ['name_es' => 'Real Sociedad',      'name_en' => 'Real Sociedad',     'logo' => '/static/images/laliga/realsociedad.png'],
            'sevilla'       => ['name_es' => 'Sevilla FC',         'name_en' => 'Sevilla FC',        'logo' => '/static/images/laliga/sevilla.png'],
            'betis'         => ['name_es' => 'Real Betis',         'name_en' => 'Real Betis',        'logo' => '/static/images/laliga/betis.png'],
            'valencia'      => ['name_es' => 'Valencia CF',        'name_en' => 'Valencia CF',       'logo' => '/static/images/laliga/valencia.png'],
            'villarreal'    => ['name_es' => 'Villarreal CF',      'name_en' => 'Villarreal CF',     'logo' => '/static/images/laliga/villarreal.png'],
            'mallorca'      => ['name_es' => 'RCD Mallorca',       'name_en' => 'RCD Mallorca',      'logo' => '/static/images/laliga/mallorca.png'],
        ],
    ],
    'uefa' => [
        'name_es' => 'UEFA', 'name_en' => 'UEFA',
        'slug' => 'uefa',
        'logo' => '/static/images/leagues/uefa.png',
        'teams' => [
            'realmadrid'       => ['name_es' => 'Real Madrid',         'name_en' => 'Real Madrid',         'logo' => '/static/images/champions_league/realmadrid.png'],
            'barcelona'        => ['name_es' => 'FC Barcelona',        'name_en' => 'FC Barcelona',        'logo' => '/static/images/champions_league/barcelona.png'],
            'atlmadrid'        => ['name_es' => 'Atlético de Madrid',  'name_en' => 'Atlético Madrid',     'logo' => '/static/images/champions_league/atlmadrid.png'],
            'villarreal'       => ['name_es' => 'Villarreal CF',       'name_en' => 'Villarreal CF',       'logo' => '/static/images/champions_league/villarreal.png'],
            'athletic'         => ['name_es' => 'Athletic Club',       'name_en' => 'Athletic Club',       'logo' => '/static/images/champions_league/athletic.png'],
            'betis'            => ['name_es' => 'Real Betis',          'name_en' => 'Real Betis',          'logo' => '/static/images/laliga/betis.png'],
            'realsociedad'     => ['name_es' => 'Real Sociedad',       'name_en' => 'Real Sociedad',       'logo' => '/static/images/laliga/realsociedad.png'],
            'roma'             => ['name_es' => 'AS Roma',             'name_en' => 'AS Roma',             'logo' => '/static/images/seriea/roma.png'],
            'lazio'            => ['name_es' => 'Lazio',               'name_en' => 'Lazio',               'logo' => '/static/images/seriea/lazio.png'],
            'tottenham'        => ['name_es' => 'Tottenham',           'name_en' => 'Tottenham',           'logo' => '/static/images/champions_league/tottenham.png'],
            'manchesterunited' => ['name_es' => 'Manchester United',   'name_en' => 'Manchester United',   'logo' => '/static/images/premierleague/manchesterunited.png'],
            'ajax'             => ['name_es' => 'Ajax',                'name_en' => 'Ajax',                'logo' => '/static/images/champions_league/ajax.png'],
        ],
    ],
    'fifa' => [
        'name_es' => 'FIFA / Selecciones', 'name_en' => 'FIFA / National Teams',
        'slug' => 'fifa',
        'logo' => '/static/images/leagues/fifa.png',
        'teams' => [],
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
