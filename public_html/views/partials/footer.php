<?php $sportDisplay = SPORT_DISPLAY; ?>
<footer class="gf-footer">
    <div class="gf-container">
        <div class="gf-footer__grid">
            <!-- Brand -->
            <div>
                <img src="/static/images/GFLogo.png" alt="GoalFeed" class="gf-footer__logo">
                <p class="gf-footer__description"><?= e(t('footer.description', $lang)) ?></p>
            </div>

            <!-- Categories -->
            <div>
                <h3 class="gf-footer__heading"><?= e(t('footer.categories', $lang)) ?></h3>
                <ul class="gf-footer__links">
                    <?php foreach ($sportDisplay as $sportKey => $sportInfo): ?>
                    <li><a href="/category/<?= e($sportKey) ?>" class="gf-footer__link"><?= e($sportInfo['name']) ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="/live" class="gf-footer__link gf-footer__link--live"><?= e(t('nav.live', $lang)) ?></a></li>
                </ul>
            </div>

            <!-- Info -->
            <div>
                <h3 class="gf-footer__heading"><?= e(t('footer.info', $lang)) ?></h3>
                <ul class="gf-footer__links">
                    <li><a href="/sitemap.xml" class="gf-footer__link">Sitemap</a></li>
                </ul>
            </div>
        </div>

        <div class="gf-footer__copyright">
            &copy; <?= date('Y') ?> <?= e(t('footer.copyright', $lang)) ?>
        </div>
    </div>
</footer>
