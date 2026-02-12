<?php
/**
 * Queries live_matches + live_events tables.
 */
class LiveRepository {

    public static function getActiveMatches(): array {
        return Database::fetchAll(
            "SELECT * FROM live_matches
             WHERE match_status NOT IN ('FT', 'AET', 'PEN', 'CANC', 'PST', 'ABD')
             ORDER BY created_at DESC"
        );
    }

    public static function getMatchEvents(string $matchId): array {
        return Database::fetchAll(
            "SELECT * FROM live_events
             WHERE match_id = ?
             ORDER BY event_minute ASC, created_at ASC",
            [$matchId]
        );
    }
}
