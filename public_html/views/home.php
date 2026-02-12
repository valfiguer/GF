<?php $pageTitle = t('nav.home', $lang); ?>
<div class="gf-container gf-section">

    <!-- Hero / Featured -->
    <?php if ($featured): ?>
    <section style="margin-bottom: 48px;">
        <h2 class="gf-section__title"><?= e(t('home.featured', $lang)) ?></h2>

        <!-- Hero article (first one, large) -->
        <?php $article = $featured[0]; ?>
        <?php View::partial('article_card_hero', compact('article', 'lang')); ?>

        <!-- Remaining featured articles -->
        <?php if (count($featured) > 1): ?>
        <div class="gf-grid gf-grid--3" style="margin-top: 20px;">
            <?php foreach (array_slice($featured, 1) as $article): ?>
            <?php View::partial('article_card', compact('article', 'lang')); ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>

    <!-- Latest articles — Animated Carousel -->
    <?php if ($articles): ?>
    <section class="gf-showcase">
        <div class="gf-showcase__header">
            <h2 class="gf-section__title" style="margin-bottom: 0;"><?= e(t('home.latest', $lang)) ?></h2>
            <div class="gf-showcase__controls">
                <button class="gf-showcase__arrow gf-showcase__arrow--prev" id="showcase-prev" aria-label="<?= e(t('pagination.prev', $lang)) ?>">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button class="gf-showcase__arrow gf-showcase__arrow--next" id="showcase-next" aria-label="<?= e(t('pagination.next', $lang)) ?>">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
        </div>

        <div class="gf-showcase__viewport" id="showcase-viewport">
            <div class="gf-showcase__track" id="showcase-track">
                <?php foreach ($articles as $article): ?>
                <div class="gf-showcase__item">
                    <?php View::partial('carousel_slide', compact('article', 'lang')); ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Progress dots -->
        <div class="gf-showcase__footer">
            <div class="gf-showcase__dots" id="showcase-dots"></div>
            <div class="gf-showcase__progress">
                <div class="gf-showcase__progress-bar" id="showcase-progress"></div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Remaining articles grid + pagination -->
    <?php if (count($articles) > 6): ?>
    <section style="margin-top: 48px;">
        <h2 class="gf-section__title"><?= e(t('home.more', $lang)) ?></h2>
        <div class="gf-grid gf-grid--3">
            <?php foreach (array_slice($articles, 6) as $article): ?>
            <?php View::partial('article_card', compact('article', 'lang')); ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
    <div class="gf-pagination">
        <?php if ($page > 1): ?>
        <a href="/?page=<?= $page - 1 ?>" class="gf-pagination__btn">&laquo; <?= e(t('pagination.prev', $lang)) ?></a>
        <?php endif; ?>
        <span class="gf-pagination__current"><?= $page ?> / <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
        <a href="/?page=<?= $page + 1 ?>" class="gf-pagination__btn"><?= e(t('pagination.next', $lang)) ?> &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!$articles): ?>
    <div class="gf-empty">
        <p class="gf-empty__text"><?= e(t('home.empty', $lang)) ?></p>
        <p class="gf-empty__subtext"><?= e(t('home.empty_sub', $lang)) ?></p>
    </div>
    <?php endif; ?>
</div>
