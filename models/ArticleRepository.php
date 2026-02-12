<?php
/**
 * Queries web_articles table.
 */
class ArticleRepository {

    public static function getBySlug(string $slug): ?array {
        return Database::fetchOne(
            "SELECT * FROM web_articles WHERE slug = ?",
            [$slug]
        );
    }

    public static function getPaginated(int $page = 1, int $perPage = 12, ?string $sport = null): array {
        $offset = ($page - 1) * $perPage;
        $sql    = "SELECT * FROM web_articles WHERE is_published = 1";
        $params = [];

        if ($sport) {
            $sql .= " AND sport = ?";
            $params[] = $sport;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        return Database::fetchAll($sql, $params);
    }

    public static function getFeatured(int $limit = 4): array {
        return Database::fetchAll(
            "SELECT * FROM web_articles WHERE is_published = 1 AND is_featured = 1
             ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }

    public static function getLatest(int $limit = 12): array {
        return Database::fetchAll(
            "SELECT * FROM web_articles WHERE is_published = 1
             ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }

    public static function getCountBySport(?string $sport = null): int {
        if ($sport) {
            $row = Database::fetchOne(
                "SELECT COUNT(*) as count FROM web_articles WHERE is_published = 1 AND sport = ?",
                [$sport]
            );
        } else {
            $row = Database::fetchOne(
                "SELECT COUNT(*) as count FROM web_articles WHERE is_published = 1"
            );
        }
        return $row ? (int)$row['count'] : 0;
    }

    public static function getRelated(string $sport, string $excludeSlug, int $limit = 4): array {
        return Database::fetchAll(
            "SELECT * FROM web_articles
             WHERE is_published = 1 AND sport = ? AND slug != ?
             ORDER BY created_at DESC LIMIT ?",
            [$sport, $excludeSlug, $limit]
        );
    }

    public static function incrementViewCount(string $slug): void {
        Database::execute(
            "UPDATE web_articles SET view_count = view_count + 1 WHERE slug = ?",
            [$slug]
        );
    }

    // ── League / Team queries ──

    public static function getByLeague(string $leagueSlug, int $page = 1, int $perPage = 12): array {
        $offset = ($page - 1) * $perPage;
        return Database::fetchAll(
            "SELECT DISTINCT wa.* FROM web_articles wa
             JOIN article_teams at2 ON at2.web_article_id = wa.id
             WHERE wa.is_published = 1 AND at2.league_slug = ?
             ORDER BY wa.created_at DESC LIMIT ? OFFSET ?",
            [$leagueSlug, $perPage, $offset]
        );
    }

    public static function getCountByLeague(string $leagueSlug): int {
        $row = Database::fetchOne(
            "SELECT COUNT(DISTINCT wa.id) as count FROM web_articles wa
             JOIN article_teams at2 ON at2.web_article_id = wa.id
             WHERE wa.is_published = 1 AND at2.league_slug = ?",
            [$leagueSlug]
        );
        return $row ? (int)$row['count'] : 0;
    }

    public static function getByTeam(string $teamSlug, int $page = 1, int $perPage = 12): array {
        $offset = ($page - 1) * $perPage;
        return Database::fetchAll(
            "SELECT DISTINCT wa.* FROM web_articles wa
             JOIN article_teams at2 ON at2.web_article_id = wa.id
             WHERE wa.is_published = 1 AND at2.team_slug = ?
             ORDER BY wa.created_at DESC LIMIT ? OFFSET ?",
            [$teamSlug, $perPage, $offset]
        );
    }

    public static function getCountByTeam(string $teamSlug): int {
        $row = Database::fetchOne(
            "SELECT COUNT(DISTINCT wa.id) as count FROM web_articles wa
             JOIN article_teams at2 ON at2.web_article_id = wa.id
             WHERE wa.is_published = 1 AND at2.team_slug = ?",
            [$teamSlug]
        );
        return $row ? (int)$row['count'] : 0;
    }
}
