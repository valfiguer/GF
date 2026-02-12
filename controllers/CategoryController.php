<?php
class CategoryController {

    public function show(string $sport): void {
        $sportDisplay = SPORT_DISPLAY;
        if (!isset($sportDisplay[$sport])) {
            http_response_code(404);
            $lang = getLang();
            View::render('errors/404', compact('lang'));
            return;
        }

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = ARTICLES_PER_PAGE;

        $articles   = ArticleRepository::getPaginated($page, $perPage, $sport);
        $total      = ArticleRepository::getCountBySport($sport);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $sportInfo  = $sportDisplay[$sport];
        $lang       = getLang();

        View::render('category', compact(
            'articles', 'sport', 'sportInfo', 'page', 'totalPages', 'total', 'lang'
        ));
    }
}
