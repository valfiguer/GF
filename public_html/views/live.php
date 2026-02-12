<?php
$pageTitle       = t('live.title', $lang);
$metaDescription = t('live.title', $lang) . ' - GoalFeed';
$scriptsExtra    = '<script src="/static/js/live.js"></script>';
?>
<div class="gf-container gf-container--wide gf-section">

    <!-- Header -->
    <div class="gf-live-header-page">
        <span class="gf-live-dot gf-live-dot--lg"></span>
        <h1 class="gf-live-header-page__title"><?= e(t('live.title', $lang)) ?></h1>
        <span class="gf-live-header-page__time" id="live-update-time"><?= e(t('live.updated', $lang)) ?></span>
    </div>

    <div id="live-matches-container">
        <?php if ($matches): ?>
        <div class="gf-live-list">
            <?php foreach ($matches as $match): ?>
            <?php View::partial('live_match_card', compact('match', 'lang')); ?>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="gf-empty" id="no-matches-msg">
            <p class="gf-empty__text"><?= e(t('live.empty', $lang)) ?></p>
            <p class="gf-empty__subtext"><?= e(t('live.empty_sub', $lang)) ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>
