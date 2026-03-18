<?php
/**
 * Maps team names from live_matches to logos and abbreviations
 * using the LEAGUES config.
 */
class TeamLogos {

    private static ?array $nameMap = null;

    /** Known aliases: bot/API team names → config slug */
    private static array $aliases = [
        'Man City'           => 'manchestercity',
        'Man Utd'            => 'manchesterunited',
        'Manchester City'    => 'manchestercity',
        'Manchester United'  => 'manchesterunited',
        'Atletico Madrid'    => 'atlmadrid',
        'Atletico de Madrid' => 'atlmadrid',
        'Atl. Madrid'        => 'atlmadrid',
        'Atl Madrid'         => 'atlmadrid',
        'Real Sociedad'      => 'realsociedad',
        'Real Madrid'        => 'realmadrid',
        'Real Betis'         => 'betis',
        'Real Oviedo'        => 'realoviedo',
        'FC Barcelona'       => 'barcelona',
        'Barca'              => 'barcelona',
        'Inter Milan'        => 'inter',
        'Inter de Milan'     => 'inter',
        'AC Milan'           => 'milan',
        'Bayern Munich'      => 'bayernmunchen',
        'Bayern Munchen'     => 'bayernmunchen',
        'Borussia Dortmund'  => 'borussiadortmund',
        'Bayer Leverkusen'   => 'bayerleverkusen',
        'Paris Saint-Germain'=> 'psg',
        'Tottenham Hotspur'  => 'tottenham',
        'Newcastle United'   => 'newcastle',
        'West Ham United'    => 'westham',
        'West Ham'           => 'westham',
        'Crystal Palace'     => 'crystalpalace',
        'Aston Villa'        => 'astonvilla',
        'Nottingham Forest'  => 'nottinghamforest',
        'Nott Forest'        => 'nottinghamforest',
        'Leicester City'     => 'leicester',
        'Ipswich Town'       => 'ipswich',
        'Rayo Vallecano'     => 'rayovallecano',
        'Hellas Verona'      => 'hellasverona',
        'Club Brugge'        => 'clubbrujas',
        'Club Brujas'        => 'clubbrujas',
        'PSV Eindhoven'      => 'psv',
        'Sporting CP'        => 'sporting',
        'Celta Vigo'         => 'celta',
        'Sevilla FC'         => 'sevilla',
        'Valencia CF'        => 'valencia',
        'Villarreal CF'      => 'villarreal',
        'Athletic Club'      => 'athletic',
        'Athletic Bilbao'    => 'athletic',
        'Girona FC'          => 'girona',
        'RCD Mallorca'       => 'mallorca',
        'RCD Espanyol'       => 'espanyol',
        'Como 1907'          => 'como',
        'AS Roma'            => 'roma',
        'Deportivo Alaves'   => 'alaves',
        'CA Osasuna'         => 'osasuna',
        'Getafe CF'          => 'getafe',
        'Levante UD'         => 'levante',
        'Elche CF'           => 'elche',
    ];

    /** Known abbreviations */
    private static array $abbreviations = [
        'barcelona'        => 'BAR',
        'realmadrid'       => 'RMA',
        'atlmadrid'        => 'ATM',
        'sevilla'          => 'SEV',
        'betis'            => 'BET',
        'realsociedad'     => 'RSO',
        'villarreal'       => 'VIL',
        'athletic'         => 'ATH',
        'valencia'         => 'VAL',
        'celta'            => 'CEL',
        'osasuna'          => 'OSA',
        'mallorca'         => 'MLL',
        'getafe'           => 'GET',
        'girona'           => 'GIR',
        'espanyol'         => 'ESP',
        'rayovallecano'    => 'RAY',
        'alaves'           => 'ALA',
        'levante'          => 'LEV',
        'elche'            => 'ELC',
        'realoviedo'       => 'OVI',
        'manchestercity'   => 'MCI',
        'manchesterunited' => 'MUN',
        'liverpool'        => 'LIV',
        'arsenal'          => 'ARS',
        'chelsea'          => 'CHE',
        'tottenham'        => 'TOT',
        'newcastle'        => 'NEW',
        'astonvilla'       => 'AVL',
        'brighton'         => 'BHA',
        'westham'          => 'WHU',
        'crystalpalace'    => 'CRY',
        'bournemouth'      => 'BOU',
        'fulham'           => 'FUL',
        'wolverhampton'    => 'WOL',
        'everton'          => 'EVE',
        'brentford'        => 'BRE',
        'nottinghamforest' => 'NFO',
        'leicester'        => 'LEI',
        'ipswich'          => 'IPS',
        'southampton'      => 'SOU',
        'inter'            => 'INT',
        'milan'            => 'MIL',
        'juventus'         => 'JUV',
        'napoli'           => 'NAP',
        'roma'             => 'ROM',
        'lazio'            => 'LAZ',
        'atalanta'         => 'ATA',
        'fiorentina'       => 'FIO',
        'torino'           => 'TOR',
        'bologna'          => 'BOL',
        'udinese'          => 'UDI',
        'genoa'            => 'GEN',
        'cagliari'         => 'CAG',
        'lecce'            => 'LEC',
        'parma'            => 'PAR',
        'hellasverona'     => 'VER',
        'como'             => 'COM',
        'sassuolo'         => 'SAS',
        'bayernmunchen'    => 'BAY',
        'borussiadortmund' => 'BVB',
        'bayerleverkusen'  => 'LEV',
        'psg'              => 'PSG',
        'benfica'          => 'BEN',
        'sporting'         => 'SPO',
        'ajax'             => 'AJX',
        'psv'              => 'PSV',
        'galatasaray'      => 'GAL',
        'clubbrujas'       => 'BRU',
    ];

    /**
     * Build a lookup map: lowercased name/slug → {logo, slug}
     * Built once per request (singleton).
     */
    private static function buildMap(): array {
        if (self::$nameMap !== null) return self::$nameMap;

        self::$nameMap = [];
        foreach (LEAGUES as $leagueSlug => $league) {
            foreach ($league['teams'] as $teamSlug => $team) {
                $entry = ['logo' => $team['logo'], 'slug' => $teamSlug];
                // Index by slug
                self::$nameMap[strtolower($teamSlug)] = $entry;
                // Index by name_es and name_en
                self::$nameMap[strtolower($team['name_es'])] = $entry;
                self::$nameMap[strtolower($team['name_en'])] = $entry;
            }
        }
        // Index aliases
        foreach (self::$aliases as $alias => $slug) {
            $key = strtolower($alias);
            if (!isset(self::$nameMap[$key])) {
                // Find logo from existing slug entry
                if (isset(self::$nameMap[strtolower($slug)])) {
                    self::$nameMap[$key] = self::$nameMap[strtolower($slug)];
                }
            }
        }
        return self::$nameMap;
    }

    /**
     * Resolve a team slug from a name string.
     */
    private static function resolveSlug(string $teamName): ?string {
        $map = self::buildMap();
        $lower = strtolower(trim($teamName));
        if (isset($map[$lower])) {
            return $map[$lower]['slug'];
        }
        return null;
    }

    /**
     * Get logo URL for a team name. Returns null if not found.
     */
    public static function getLogo(string $teamName): ?string {
        $map = self::buildMap();
        $lower = strtolower(trim($teamName));
        return $map[$lower]['logo'] ?? null;
    }

    /**
     * Get abbreviation for a team name.
     * Falls back to first 3 uppercase letters.
     */
    public static function getAbbreviation(string $teamName): string {
        $slug = self::resolveSlug($teamName);
        if ($slug && isset(self::$abbreviations[$slug])) {
            return self::$abbreviations[$slug];
        }
        // Fallback: first 3 letters uppercased
        $clean = preg_replace('/[^a-zA-Z]/', '', $teamName);
        return strtoupper(substr($clean, 0, 3));
    }
}
