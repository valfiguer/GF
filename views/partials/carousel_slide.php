<?php
$sportDisplay = SPORT_DISPLAY;
$artSportInfo = $sportDisplay[$article['sport']] ?? [];
?>
<a href="/article/<?= e($article['slug']) ?>" class="gf-slide">
    <?php if (!empty($article['image_url'])): ?>
    <img class="gf-slide__img" src="<?= e($article['image_url']) ?>" alt="<?= e($article['headline']) ?>" loading="lazy">
    <?php elseif (!empty($article['image_filename'])): ?>
    <img class="gf-slide__img" src="/static/images/articles/<?= e($article['image_filename']) ?>" alt="<?= e($article['headline']) ?>" loading="lazy">
    <?php else: ?>
    <div class="gf-slide__img gf-slide__img--placeholder">
        <?= icon($article['sport']) ?: icon('event_default') ?>
    </div>
    <?php endif; ?>
    <div class="gf-slide__overlay"></div>
    <div class="gf-slide__content">
        <span class="gf-slide__badge">
            <?= icon($article['sport']) ?>
            <?= e($artSportInfo['name'] ?? $article['sport']) ?>
        </span>
        <h3 class="gf-slide__title"><?= e($article['headline']) ?></h3>
        <?php if (!empty($article['subtitle'])): ?>
        <p class="gf-slide__sub"><?= e($article['subtitle']) ?></p>
        <?php endif; ?>
        <div class="gf-slide__meta">
            <?php if (!empty($article['source_name'])): ?><span><?= e($article['source_name']) ?></span><?php endif; ?>
            <?php if (!empty($article['created_at'])): ?><time><?= e(str_replace('T', ' ', substr($article['created_at'], 0, 16))) ?></time><?php endif; ?>
        </div>
    </div>
</a>
