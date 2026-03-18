<?php
// Detect current league from URL for contextual filtering
$tickerLeague = '';
if (preg_match('#^/league/([a-z]+)#', $requestPath ?? '', $_tlm)) {
    $tickerLeague = $_tlm[1];
}

$tickerLeagueName = null;
if ($tickerLeague && isset(LEAGUE_SLUG_TO_NAME[$tickerLeague])) {
    $tickerLeagueName = LEAGUE_SLUG_TO_NAME[$tickerLeague];
}

$tickerMatches = LiveRepository::getTickerMatches($tickerLeagueName);
if (empty($tickerMatches)) return;
?>
<div id="gf-score-ticker" class="gf-score-ticker" data-ticker-league="<?= e($tickerLeague) ?>">
    <div class="gf-container">
        <div class="gf-score-ticker__inner">
            <button class="gf-score-ticker__arrow gf-score-ticker__arrow--left hidden" aria-label="Scroll left">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <div class="gf-score-ticker__scroll">
                <?php foreach ($tickerMatches as $tm):
                    $isLive = in_array($tm['match_status'], ['1H','2H','ET','LIVE','HT']);
                    $homeLogo = TeamLogos::getLogo($tm['home_team']);
                    $awayLogo = TeamLogos::getLogo($tm['away_team']);
                    $homeAbbr = TeamLogos::getAbbreviation($tm['home_team']);
                    $awayAbbr = TeamLogos::getAbbreviation($tm['away_team']);
                    $statusClass = '';
                    $statusText = $tm['match_status'];
                    if (in_array($tm['match_status'], ['1H','2H','ET','LIVE'])) {
                        $statusClass = 'gf-score-ticker__status--live';
                        $statusText = $tm['current_minute'] ? $tm['current_minute'] . "'" : $tm['match_status'];
                    } elseif ($tm['match_status'] === 'HT') {
                        $statusClass = 'gf-score-ticker__status--ht';
                        $statusText = 'HT';
                    } elseif (in_array($tm['match_status'], ['FT','AET','PEN'])) {
                        $statusClass = 'gf-score-ticker__status--ft';
                        $statusText = $tm['match_status'];
                    } elseif ($tm['match_status'] === 'NS') {
                        $statusClass = 'gf-score-ticker__status--ns';
                        $statusText = '--:--';
                    }
                ?>
                <div class="gf-score-ticker__match<?= $isLive ? ' gf-score-ticker__match--live' : '' ?>">
                    <div class="gf-score-ticker__team">
                        <?php if ($homeLogo): ?><img src="<?= e($homeLogo) ?>" alt="" class="gf-score-ticker__logo" width="18" height="18"><?php endif; ?>
                        <span class="gf-score-ticker__abbr"><?= e($homeAbbr) ?></span>
                    </div>
                    <span class="gf-score-ticker__score"><?= (int)$tm['home_score'] ?> - <?= (int)$tm['away_score'] ?></span>
                    <div class="gf-score-ticker__team">
                        <span class="gf-score-ticker__abbr"><?= e($awayAbbr) ?></span>
                        <?php if ($awayLogo): ?><img src="<?= e($awayLogo) ?>" alt="" class="gf-score-ticker__logo" width="18" height="18"><?php endif; ?>
                    </div>
                    <span class="gf-score-ticker__status <?= $statusClass ?>"><?= e($statusText) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="gf-score-ticker__arrow gf-score-ticker__arrow--right" aria-label="Scroll right">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </div>
</div>
