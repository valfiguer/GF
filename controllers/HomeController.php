<?php
class HomeController {

    /** One-time migration — delete after use */
    public function runMigration(): void {
        require __DIR__ . '/../migrate_and_backfill.php';
        exit;
    }

    public function index(): void {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(ARTICLES_PER_PAGE, 18);

        $featured = ArticleRepository::getFeatured(4);
        if (!$featured) {
            $featured = ArticleRepository::getLatest(4);
        }

        $articles   = ArticleRepository::getPaginated($page, $perPage);
        $total      = ArticleRepository::getCountBySport();
        $totalPages = max(1, (int)ceil($total / $perPage));

        $lang = getLang();

        View::render('home', compact(
            'featured', 'articles', 'page', 'totalPages', 'total', 'lang'
        ));
    }
}
