<?php
/**
 * Queries web_comments table.
 */
class CommentRepository {

    public static function getByArticle(int $webArticleId): array {
        return Database::fetchAll(
            "SELECT * FROM web_comments
             WHERE web_article_id = ? AND is_visible = 1
             ORDER BY created_at ASC",
            [$webArticleId]
        );
    }

    public static function add(
        int $webArticleId,
        string $userName,
        string $userInitials,
        string $commentText,
        ?int $userId = null
    ): int {
        Database::execute(
            "INSERT INTO web_comments (web_article_id, user_name, user_initials, comment_text, user_id)
             VALUES (?, ?, ?, ?, ?)",
            [$webArticleId, $userName, $userInitials, $commentText, $userId]
        );
        return (int)Database::lastInsertId();
    }

    public static function getCount(int $webArticleId): int {
        $row = Database::fetchOne(
            "SELECT COUNT(*) as count FROM web_comments WHERE web_article_id = ? AND is_visible = 1",
            [$webArticleId]
        );
        return $row ? (int)$row['count'] : 0;
    }
}
