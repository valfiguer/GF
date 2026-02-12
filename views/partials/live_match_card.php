<div class="gf-live-card">
    <!-- Match header -->
    <div class="gf-live-header">
        <span class="gf-live-header__league"><?= e($match['league_name']) ?></span>
        <span class="gf-live-header__status">
            <?php if (in_array($match['match_status'], ['1H', '2H', 'ET', 'LIVE'])): ?>
            <span class="gf-live-dot"></span>
            <span class="gf-live-status--live"><?= e($match['current_minute']) ?>'</span>
            <?php elseif ($match['match_status'] === 'HT'): ?>
            <span class="gf-live-status--ht"><?= e(t('live.halftime', $lang)) ?></span>
            <?php elseif (in_array($match['match_status'], ['FT', 'AET'])): ?>
            <span class="gf-live-status--ft"><?= e(t('live.fulltime', $lang)) ?></span>
            <?php else: ?>
            <span class="gf-live-status--ft"><?= e($match['match_status']) ?></span>
            <?php endif; ?>
        </span>
    </div>

    <!-- Score -->
    <div class="gf-live-score">
        <div class="gf-live-score__inner">
            <span class="gf-live-score__team gf-live-score__team--home"><?= e($match['home_team']) ?></span>
            <span class="gf-live-score__result"><?= e($match['home_score']) ?> - <?= e($match['away_score']) ?></span>
            <span class="gf-live-score__team gf-live-score__team--away"><?= e($match['away_team']) ?></span>
        </div>
    </div>

    <!-- Events timeline -->
    <?php if (!empty($match['events'])): ?>
    <div class="gf-live-events">
        <?php foreach ($match['events'] as $event): ?>
        <div class="gf-live-event">
            <span class="gf-live-event__minute"><?= e($event['event_minute']) ?>'</span>
            <span class="gf-live-event__icon <?php
                if ($event['event_type'] === 'goal') echo 'gf-live-event__icon--goal';
                elseif ($event['event_type'] === 'red_card') echo 'gf-live-event__icon--red';
                elseif ($event['event_type'] === 'var') echo 'gf-live-event__icon--var';
                else echo 'gf-live-event__icon--default';
            ?>">
                <?php
                    if ($event['event_type'] === 'goal') echo icon('event_goal');
                    elseif ($event['event_type'] === 'red_card') echo icon('event_red_card');
                    elseif ($event['event_type'] === 'penalty_miss') echo icon('event_penalty_miss');
                    elseif ($event['event_type'] === 'var') echo icon('event_var');
                    else echo icon('event_default');
                ?>
            </span>
            <span class="gf-live-event__player"><?= e($event['event_player'] ?? '') ?></span>
            <?php if (!empty($event['event_detail'])): ?>
            <span class="gf-live-event__detail">(<?= e($event['event_detail']) ?>)</span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
