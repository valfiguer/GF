<?php
$pageTitle       = $sportInfo['name'];
$metaDescription = $sportInfo['name'] . ' - GoalFeed';
?>
<div class="gf-container gf-section">

    <!-- Header -->
    <div class="gf-category-header">
        <h1 class="gf-category-header__title"><?= icon($sport) ?> <?= e($sportInfo['name']) ?></h1>
        <p class="gf-category-header__count"><?= e(t('category.articles_count', $lang, ['count' => $total, 's' => ($total != 1 ? 's' : '')])) ?></p>
    </div>

    <?php if ($articles): ?>

    <!-- Hero article (first one) -->
    <?php $article = $articles[0]; ?>
    <?php View::partial('article_card_hero', compact('article', 'lang')); ?>

    <!-- Remaining articles -->
    <?php if (count($articles) > 1): ?>
    <div class="gf-grid gf-grid--3" style="margin-top: 20px;">
        <?php foreach (array_slice($articles, 1) as $article): ?>
        <?php View::partial('article_card', compact('article', 'lang')); ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="gf-pagination">
        <?php if ($page > 1): ?>
        <a href="/category/<?= e($sport) ?>?page=<?= $page - 1 ?>" class="gf-pagination__btn">&laquo; <?= e(t('pagination.prev', $lang)) ?></a>
        <?php endif; ?>

        <span class="gf-pagination__current"><?= $page ?> / <?= $totalPages ?></span>

        <?php if ($page < $totalPages): ?>
        <a href="/category/<?= e($sport) ?>?page=<?= $page + 1 ?>" class="gf-pagination__btn"><?= e(t('pagination.next', $lang)) ?> &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="gf-empty">
        <p class="gf-empty__text"><?= e(t('category.empty', $lang, ['sport' => $sportInfo['name']])) ?></p>
    </div>
    <?php endif; ?>
</div>
