<?php
class LeagueController {

    public function show(string $leagueSlug): void {
        $leagues = LEAGUES;
        if (!isset($leagues[$leagueSlug])) {
            http_response_code(404);
            $lang = getLang();
            View::render('errors/404', compact('lang'));
            return;
        }

        $league     = $leagues[$leagueSlug];
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $perPage    = ARTICLES_PER_PAGE;
        $articles   = ArticleRepository::getByLeague($leagueSlug, $page, $perPage);
        $total      = ArticleRepository::getCountByLeague($leagueSlug);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $lang       = getLang();

        View::render('league', compact(
            'league', 'leagueSlug', 'articles', 'page', 'totalPages', 'total', 'lang'
        ));
    }

    public function team(string $leagueSlug, string $teamSlug): void {
        $leagues = LEAGUES;
        if (!isset($leagues[$leagueSlug]) || !isset($leagues[$leagueSlug]['teams'][$teamSlug])) {
            http_response_code(404);
            $lang = getLang();
            View::render('errors/404', compact('lang'));
            return;
        }

        $league     = $leagues[$leagueSlug];
        $team       = $league['teams'][$teamSlug];
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $perPage    = ARTICLES_PER_PAGE;
        $articles   = ArticleRepository::getByTeam($teamSlug, $page, $perPage);
        $total      = ArticleRepository::getCountByTeam($teamSlug);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $lang       = getLang();

        View::render('team', compact(
            'league', 'leagueSlug', 'team', 'teamSlug', 'articles',
            'page', 'totalPages', 'total', 'lang'
        ));
    }
}
