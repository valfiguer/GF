<?php
class ArticleController {

    public function show(string $slug): void {
        $article = ArticleRepository::getBySlug($slug);
        if (!$article) {
            http_response_code(404);
            $lang = getLang();
            View::render('errors/404', compact('lang'));
            return;
        }

        ArticleRepository::incrementViewCount($slug);

        $related     = ArticleRepository::getRelated($article['sport'], $slug, 4);
        $comments    = CommentRepository::getByArticle((int)$article['id']);
        $currentUser = Session::getCurrentUser();
        $lang        = getLang();

        View::render('article_detail', compact(
            'article', 'related', 'comments', 'currentUser', 'lang'
        ));
    }
}
