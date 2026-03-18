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

    /**
     * Get today's matches for the score ticker.
     * Returns live + finished matches, excluding cancelled/postponed/abandoned.
     */
    public static function getTickerMatches(?string $leagueName = null): array {
        $where = "match_status NOT IN ('CANC', 'PST', 'ABD')";
        $where .= " AND (
            match_status NOT IN ('FT', 'AET', 'PEN')
            OR DATE(updated_at) = CURDATE()
        )";
        $params = [];

        if ($leagueName) {
            $where .= " AND league_name = ?";
            $params[] = $leagueName;
        } else {
            $where .= " AND is_top_team_match = 1";
        }

        return Database::fetchAll(
            "SELECT match_id, league_name, home_team, away_team,
                    home_score, away_score, match_status, current_minute
             FROM live_matches
             WHERE $where
             ORDER BY
                CASE
                    WHEN match_status IN ('1H','2H','ET','LIVE') THEN 0
                    WHEN match_status = 'HT' THEN 1
                    WHEN match_status = 'NS' THEN 2
                    ELSE 3
                END,
                updated_at DESC
             LIMIT 20",
            $params
        );
    }
}
