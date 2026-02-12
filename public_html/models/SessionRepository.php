<?php
/**
 * Queries sessions table.
 * Most session logic lives in core/Session.php; this is for extras like cleanup.
 */
class SessionRepository {

    public static function cleanupExpired(): void {
        $now = gmdate('Y-m-d\TH:i:s');
        Database::execute(
            "DELETE FROM sessions WHERE expires_at <= ?",
            [$now]
        );
    }
}
