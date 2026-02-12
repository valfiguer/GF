<?php
/**
 * DB-backed sessions: create, getCurrentUser, destroy.
 */
class Session {

    /** Create a session for user_id, set cookie on response. Returns token. */
    public static function create(int $userId): string {
        $token     = bin2hex(random_bytes(32)); // 64-char hex
        $expiresAt = gmdate('Y-m-d\TH:i:s', time() + SESSION_MAX_AGE_DAYS * 86400);

        Database::execute(
            "INSERT INTO sessions (id, user_id, expires_at) VALUES (?, ?, ?)",
            [$token, $userId, $expiresAt]
        );

        Database::execute(
            "UPDATE users SET last_login_at = ? WHERE id = ?",
            [gmdate('Y-m-d\TH:i:s'), $userId]
        );

        setcookie(SESSION_COOKIE, $token, [
            'expires'  => time() + SESSION_MAX_AGE_DAYS * 86400,
            'path'     => '/',
            'httponly'  => true,
            'samesite' => 'Lax',
        ]);

        return $token;
    }

    /** Get current user from session cookie, or null. */
    public static function getCurrentUser(): ?array {
        $token = $_COOKIE[SESSION_COOKIE] ?? null;
        if (!$token) return null;

        $now = gmdate('Y-m-d\TH:i:s');
        return Database::fetchOne(
            "SELECT u.* FROM users u
             JOIN sessions s ON s.user_id = u.id
             WHERE s.id = ? AND s.expires_at > ? AND u.is_active = 1",
            [$token, $now]
        );
    }

    /** Delete session from DB and clear cookie. */
    public static function destroy(): void {
        $token = $_COOKIE[SESSION_COOKIE] ?? null;
        if ($token) {
            Database::execute("DELETE FROM sessions WHERE id = ?", [$token]);
        }
        setcookie(SESSION_COOKIE, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly'  => true,
            'samesite' => 'Lax',
        ]);
    }
}
