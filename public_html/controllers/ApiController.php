<?php
class ApiController {

    public function articles(): void {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $sport   = isset($_GET['sport']) && $_GET['sport'] !== '' ? $_GET['sport'] : null;
        $perPage = ARTICLES_PER_PAGE;

        $articles = ArticleRepository::getPaginated($page, $perPage, $sport);
        $total    = ArticleRepository::getCountBySport($sport);

        View::renderJson([
            'articles' => $articles,
            'page'     => $page,
            'total'    => $total,
            'per_page' => $perPage,
        ]);
    }

    public function live(): void {
        $matches  = LiveRepository::getActiveMatches();
        $enriched = [];
        foreach ($matches as $match) {
            $match['events'] = LiveRepository::getMatchEvents($match['match_id']);
            $enriched[] = $match;
        }

        View::renderJson(['matches' => $enriched]);
    }

    public function getComments(string $webArticleId): void {
        $comments = CommentRepository::getByArticle((int)$webArticleId);
        View::renderJson(['comments' => $comments]);
    }

    public function postComment(string $webArticleId): void {
        $user = Session::getCurrentUser();
        if (!$user) {
            View::renderJson(['error' => 'No autenticado'], 401);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $text  = trim($input['comment_text'] ?? '');

        if ($text === '' || mb_strlen($text) > 2000) {
            View::renderJson(['error' => 'Comentario vacio'], 400);
        }

        $cleanText    = e($text);
        $userName     = $user['display_name'];
        $userInitials = $user['initials'];

        $commentId = CommentRepository::add(
            (int)$webArticleId,
            e($userName),
            e($userInitials),
            $cleanText,
            (int)$user['id']
        );

        View::renderJson([
            'id'             => $commentId,
            'user_name'      => $userName,
            'user_initials'  => $userInitials,
            'comment_text'   => $cleanText,
            'created_at'     => 'now',
        ], 201);
    }
}
