<?php
$pageTitle       = $article['headline'];
$metaDescription = $article['meta_description'] ?? $article['subtitle'] ?? '';
$metaKeywords    = $article['meta_keywords'] ?? '';
$ogType          = 'article';
$ogTitle         = $article['og_title'] ?? $article['headline'];
$ogDescription   = $article['og_description'] ?? $article['meta_description'] ?? $article['subtitle'] ?? '';

if (!empty($article['og_image_url'])) {
    $ogImage = $article['og_image_url'];
} elseif (!empty($article['image_url'])) {
    $ogImage = $article['image_url'];
} elseif (!empty($article['image_filename'])) {
    $ogImage = BASE_URL . '/static/images/articles/' . $article['image_filename'];
} else {
    $ogImage = BASE_URL . '/static/images/og-default.jpg';
}

$sportDisplay = SPORT_DISPLAY;
$statusConfig = STATUS_CONFIG;

// JSON-LD
$headExtra = '<script type="application/ld+json">'
    . json_encode([
        '@context'      => 'https://schema.org',
        '@type'         => 'NewsArticle',
        'headline'      => $article['headline'],
        'description'   => $article['meta_description'] ?? $article['subtitle'] ?? '',
        'image'         => $ogImage,
        'datePublished' => $article['created_at'] ?? '',
        'dateModified'  => $article['updated_at'] ?? $article['created_at'] ?? '',
        'author'        => ['@type' => 'Organization', 'name' => 'GoalFeed'],
        'publisher'     => ['@type' => 'Organization', 'name' => 'GoalFeed'],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    . '</script>';
?>

<article class="gf-container gf-container--narrow gf-section">

    <!-- Hero image -->
    <?php if (!empty($article['image_url']) || !empty($article['image_filename'])): ?>
    <div class="gf-article-hero">
        <?php if (!empty($article['image_url'])): ?>
        <img src="<?= e($article['image_url']) ?>" alt="<?= e($article['headline']) ?>">
        <?php else: ?>
        <img src="/static/images/articles/<?= e($article['image_filename']) ?>" alt="<?= e($article['headline']) ?>">
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Badges -->
    <div class="gf-article__badges">
        <?php $artSportInfo = $sportDisplay[$article['sport']] ?? []; ?>
        <a href="/category/<?= e($article['sport']) ?>" class="gf-badge gf-badge--sport">
            <?= icon($article['sport']) ?>
            <?= e($artSportInfo['name'] ?? $article['sport']) ?>
        </a>

        <?php if (!empty($article['status']) && isset($statusConfig[$article['status']])): ?>
        <?php $si = $statusConfig[$article['status']]; ?>
        <span class="gf-badge <?php if ($article['status'] === 'CONFIRMADO') echo 'gf-badge--confirmed'; elseif ($article['status'] === 'RUMOR') echo 'gf-badge--rumor'; else echo 'gf-badge--breaking'; ?>">
            <?= icon($article['status']) ?>
            <?= e($si['label']) ?>
        </span>
        <?php endif; ?>

        <?php if (!empty($article['category'])): ?>
        <span class="gf-badge gf-badge--category"><?= e($article['category']) ?></span>
        <?php endif; ?>
    </div>

    <!-- Headline -->
    <h1 class="gf-article__headline"><?= e($article['headline']) ?></h1>

    <!-- Subtitle -->
    <?php if (!empty($article['subtitle'])): ?>
    <p class="gf-article__subtitle"><?= e($article['subtitle']) ?></p>
    <?php endif; ?>

    <!-- Meta -->
    <div class="gf-article__meta">
        <?php if (!empty($article['created_at'])): ?>
        <time><?= e(str_replace('T', ' ', substr($article['created_at'], 0, 16))) ?></time>
        <?php endif; ?>
        <?php if (!empty($article['view_count'])): ?>
        <span><?= e($article['view_count']) ?> <?= e(t('article.reads', $lang)) ?></span>
        <?php endif; ?>
    </div>

    <!-- Body -->
    <div class="gf-prose">
        <?= $article['body_html'] ?>
    </div>

    <!-- Source -->
    <?php if (!empty($article['source_url'])): ?>
    <div class="gf-source-box">
        <p>
            <?= e(t('article.source', $lang)) ?>:
            <a href="<?= e($article['source_url']) ?>" target="_blank" rel="noopener noreferrer">
                <?= e($article['source_name'] ?? t('article.see_source', $lang)) ?>
            </a>
        </p>
    </div>
    <?php endif; ?>
</article>

<!-- Comments Section -->
<section class="gf-container gf-container--narrow gf-comments-section" id="comments-section" data-article-id="<?= e($article['id']) ?>">
    <h2 class="gf-section__title"><?= e(t('comments.title', $lang)) ?></h2>

    <!-- Comments list -->
    <div class="gf-comments__list" id="comments-list">
        <!-- Comments loaded via JS -->
    </div>

    <!-- Comment form -->
    <div class="gf-comments__form-wrapper" id="comment-form-wrapper">
        <?php if ($currentUser): ?>
        <!-- Logged in: show form -->
        <form class="gf-comments__form" id="comment-form">
            <div class="gf-comments__form-header">
                <div class="gf-comments__form-avatar"><?= e($currentUser['initials']) ?></div>
                <span class="gf-comments__form-name"><?= e($currentUser['display_name']) ?></span>
            </div>
            <textarea class="gf-comments__textarea" id="comment-text" placeholder="<?= e(t('comments.placeholder', $lang)) ?>" rows="3" maxlength="2000"></textarea>
            <div class="gf-comments__form-actions">
                <span class="gf-comments__char-count" id="comment-char-count">0 / 2000</span>
                <button type="submit" class="gf-btn gf-btn--primary gf-comments__submit"><?= e(t('comments.submit', $lang)) ?></button>
            </div>
        </form>
        <?php else: ?>
        <!-- Not logged in: blurred form with overlay -->
        <div class="gf-comments__gate">
            <div class="gf-comments__form gf-comments__form--blurred" aria-hidden="true">
                <div class="gf-comments__form-header">
                    <div class="gf-comments__form-avatar">?</div>
                    <span class="gf-comments__form-name">Usuario</span>
                </div>
                <textarea class="gf-comments__textarea" placeholder="<?= e(t('comments.placeholder', $lang)) ?>" rows="3" disabled></textarea>
                <div class="gf-comments__form-actions">
                    <span class="gf-comments__char-count">0 / 2000</span>
                    <button type="button" class="gf-btn gf-btn--primary gf-comments__submit" disabled><?= e(t('comments.submit', $lang)) ?></button>
                </div>
            </div>
            <div class="gf-comments__gate-overlay">
                <div class="gf-comments__gate-card">
                    <svg class="gf-comments__gate-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <p class="gf-comments__gate-text"><?= e(t('comments.gate', $lang)) ?></p>
                    <a href="/auth/register" class="gf-btn gf-btn--primary"><?= e(t('comments.gate_btn', $lang)) ?></a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Related articles -->
<?php if ($related): ?>
<section class="gf-container gf-related">
    <h2 class="gf-section__title"><?= e(t('article.related', $lang)) ?></h2>
    <div class="gf-grid gf-grid--4">
        <?php foreach ($related as $article): ?>
        <?php View::partial('article_card', compact('article', 'lang')); ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
