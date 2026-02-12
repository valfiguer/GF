<?php
class LiveController {

    public function index(): void {
        $matches = LiveRepository::getActiveMatches();

        // Enrich with events
        $enriched = [];
        foreach ($matches as $match) {
            $match['events'] = LiveRepository::getMatchEvents($match['match_id']);
            $enriched[] = $match;
        }

        $matches = $enriched;
        $lang    = getLang();

        View::render('live', compact('matches', 'lang'));
    }
}
