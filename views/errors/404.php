<?php
$pageTitle = $lang === 'es' ? 'Página no encontrada' : 'Page Not Found';
$metaDescription = $lang === 'es' ? 'La página que buscas no existe en GoalFeed.' : 'The page you are looking for does not exist on GoalFeed.';
?>
<div class="gf-container gf-section" style="text-align: center; padding: 80px 20px;">
    <h1 style="font-size: 48px; font-weight: 700; margin-bottom: 16px;">404</h1>
    <p style="font-size: 18px; opacity: 0.7;"><?= e(t('article.not_found', $lang)) ?></p>
    <a href="/" class="gf-btn gf-btn--primary" style="margin-top: 24px;"><?= e(t('nav.home', $lang)) ?></a>
</div>
