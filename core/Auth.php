<?php
/**
 * Password hashing helpers and initials generator.
 * Uses PHP's password_hash (bcrypt) — compatible with Python bcrypt $2b$ hashes.
 */
class Auth {

    /** Hash a plaintext password. */
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /** Verify plaintext against bcrypt hash (handles $2b$ from Python). */
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    /** Generate 2-letter initials from a display name. */
    public static function makeInitials(string $name): string {
        $parts = preg_split('/\s+/', trim($name));
        if (count($parts) >= 2) {
            return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
        }
        if ($parts) {
            return strtoupper(mb_substr($parts[0], 0, 2));
        }
        return '??';
    }
}
