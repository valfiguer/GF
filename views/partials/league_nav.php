<?php
$leagues = LEAGUES;
$activeLg = null;
if (preg_match('#^/league/([a-z]+)#', $requestPath, $m)) {
    $activeLg = $m[1];
}
?>
<nav class="gf-league-nav" aria-label="<?= e(t('nav.leagues', $lang)) ?>">
    <div class="gf-container">
        <div class="gf-league-nav__scroll">
            <?php foreach ($leagues as $slug => $lg): ?>
            <a href="/league/<?= e($slug) ?>"
               class="gf-league-nav__item<?= $activeLg === $slug ? ' gf-league-nav__item--active' : '' ?>">
                <img src="<?= e($lg['logo']) ?>" alt="<?= e($lang === 'en' ? $lg['name_en'] : $lg['name_es']) ?>" class="gf-league-nav__logo" width="28" height="28">
                <span><?= e($lang === 'en' ? $lg['name_en'] : $lg['name_es']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</nav>
