<?php
$sportDisplay = SPORT_DISPLAY;
$statusConfig = STATUS_CONFIG;
$artSportInfo = $sportDisplay[$article['sport']] ?? [];
?>
<a href="/article/<?= e($article['slug']) ?>" class="gf-card">
    <!-- Image -->
    <div class="gf-card__image">
        <?php if (!empty($article['image_url'])): ?>
        <img src="<?= e($article['image_url']) ?>" alt="<?= e($article['headline']) ?>" loading="lazy">
        <?php elseif (!empty($article['image_filename'])): ?>
        <img src="/static/images/articles/<?= e($article['image_filename']) ?>" alt="<?= e($article['headline']) ?>" loading="lazy">
        <?php else: ?>
        <div class="gf-card__image-placeholder">
            <?= icon($article['sport']) ?: icon('event_default') ?>
        </div>
        <?php endif; ?>

        <!-- Badges -->
        <div class="gf-card__badges">
            <span class="gf-badge gf-badge--sport">
                <?= icon($article['sport']) ?>
                <?= e($artSportInfo['name'] ?? $article['sport']) ?>
            </span>

            <?php if (!empty($article['status']) && isset($statusConfig[$article['status']])): ?>
            <?php $si = $statusConfig[$article['status']]; ?>
            <span class="gf-badge <?php if ($article['status'] === 'CONFIRMADO') echo 'gf-badge--confirmed'; elseif ($article['status'] === 'RUMOR') echo 'gf-badge--rumor'; else echo 'gf-badge--breaking'; ?>">
                <?= icon($article['status']) ?>
                <?= e($si['label']) ?>
            </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Content -->
    <div class="gf-card__body">
        <h3 class="gf-card__title"><?= e($article['headline']) ?></h3>
        <?php if (!empty($article['subtitle'])): ?>
        <p class="gf-card__subtitle"><?= e($article['subtitle']) ?></p>
        <?php endif; ?>

        <div class="gf-card__meta">
            <?php if (!empty($article['source_name'])): ?>
            <span><?= e($article['source_name']) ?></span>
            <?php endif; ?>
            <?php if (!empty($article['created_at'])): ?>
            <time><?= e(str_replace('T', ' ', substr($article['created_at'], 0, 16))) ?></time>
            <?php endif; ?>
        </div>
    </div>
</a>
