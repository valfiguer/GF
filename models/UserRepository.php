<?php
/**
 * Queries users table.
 */
class UserRepository {

    public static function create(
        string $email,
        string $displayName,
        string $initials,
        ?string $passwordHash = null,
        string $authProvider = 'local',
        ?string $googleId = null,
        ?string $avatarUrl = null
    ): int {
        $now = gmdate('Y-m-d\TH:i:s');
        Database::execute(
            "INSERT INTO users (email, password_hash, display_name, initials,
             avatar_url, auth_provider, google_id, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$email, $passwordHash, $displayName, $initials,
             $avatarUrl, $authProvider, $googleId, $now, $now]
        );
        return (int)Database::lastInsertId();
    }

    public static function getByEmail(string $email): ?array {
        return Database::fetchOne(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
    }

    public static function getByGoogleId(string $googleId): ?array {
        return Database::fetchOne(
            "SELECT * FROM users WHERE google_id = ?",
            [$googleId]
        );
    }

    public static function getById(int $userId): ?array {
        return Database::fetchOne(
            "SELECT * FROM users WHERE id = ?",
            [$userId]
        );
    }

    public static function linkGoogle(int $userId, string $googleId, ?string $avatarUrl): void {
        Database::execute(
            "UPDATE users SET google_id = ?, avatar_url = ?, auth_provider = 'google' WHERE id = ?",
            [$googleId, $avatarUrl, $userId]
        );
    }
}
