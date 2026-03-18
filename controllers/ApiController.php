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

    public function ticker(): void {
        $leagueName = null;
        $slug = isset($_GET['league']) ? $_GET['league'] : null;
        if ($slug && isset(LEAGUE_SLUG_TO_NAME[$slug])) {
            $leagueName = LEAGUE_SLUG_TO_NAME[$slug];
        }

        $matches = LiveRepository::getTickerMatches($leagueName);
        $result = [];
        foreach ($matches as $m) {
            $result[] = [
                'id'         => $m['match_id'],
                'home'       => $m['home_team'],
                'away'       => $m['away_team'],
                'home_abbr'  => TeamLogos::getAbbreviation($m['home_team']),
                'away_abbr'  => TeamLogos::getAbbreviation($m['away_team']),
                'home_logo'  => TeamLogos::getLogo($m['home_team']),
                'away_logo'  => TeamLogos::getLogo($m['away_team']),
                'home_score' => (int)$m['home_score'],
                'away_score' => (int)$m['away_score'],
                'status'     => $m['match_status'],
                'minute'     => $m['current_minute'],
                'league'     => $m['league_name'],
            ];
        }

        View::renderJson(['matches' => $result]);
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

        try {
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
        } catch (\Throwable $e) {
            error_log('Error posting comment: ' . $e->getMessage());
            View::renderJson(['error' => 'Error al publicar comentario'], 500);
        }
    }
}
