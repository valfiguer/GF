<?php
$sportDisplay = SPORT_DISPLAY;
$statusConfig = STATUS_CONFIG;
$artSportInfo = $sportDisplay[$article['sport']] ?? [];
?>
<a href="/article/<?= e($article['slug']) ?>" class="gf-hero-card">
    <!-- Image -->
    <div class="gf-hero-card__image">
        <?php if (!empty($article['image_url'])): ?>
        <img src="<?= e($article['image_url']) ?>" alt="<?= e($article['headline']) ?>" loading="lazy">
        <?php elseif (!empty($article['image_filename'])): ?>
        <img src="/static/images/articles/<?= e($article['image_filename']) ?>" alt="<?= e($article['headline']) ?>" loading="lazy">
        <?php else: ?>
        <div class="gf-hero-card__image-placeholder">
            <?= icon($article['sport']) ?: icon('event_default') ?>
        </div>
        <?php endif; ?>

        <!-- Gradient overlay -->
        <div class="gf-hero-card__overlay"></div>

        <!-- Content on image -->
        <div class="gf-hero-card__content">
            <!-- Badges -->
            <div class="gf-hero-card__badges">
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

            <h3 class="gf-hero-card__title"><?= e($article['headline']) ?></h3>
            <?php if (!empty($article['subtitle'])): ?>
            <p class="gf-hero-card__subtitle"><?= e($article['subtitle']) ?></p>
            <?php endif; ?>

            <div class="gf-hero-card__meta">
                <?php if (!empty($article['source_name'])): ?>
                <span><?= e($article['source_name']) ?></span>
                <?php endif; ?>
                <?php if (!empty($article['created_at'])): ?>
                <time><?= e(str_replace('T', ' ', substr($article['created_at'], 0, 16))) ?></time>
                <?php endif; ?>
            </div>
        </div>
    </div>
</a>
