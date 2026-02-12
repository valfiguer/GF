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
}
