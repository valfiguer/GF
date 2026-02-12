<?php
$user = $currentUser ?? null;
$sportDisplay = SPORT_DISPLAY;
?>
<nav class="gf-nav">
    <div class="gf-container">
        <div class="gf-nav__inner">
            <!-- Logo -->
            <a href="/" class="gf-nav__logo">
                <img src="/static/images/GFLogo.png" alt="GoalFeed" height="32">
            </a>

            <!-- Desktop: Segmented Control -->
            <div class="gf-nav__center">
                <div class="gf-segmented">
                    <a href="/" class="gf-segmented__item <?= $requestPath === '/' ? 'gf-segmented__item--active' : '' ?>"><?= e(t('nav.home', $lang)) ?></a>
                    <?php foreach ($sportDisplay as $sportKey => $sportInfo): ?>
                    <a href="/category/<?= e($sportKey) ?>" class="gf-segmented__item <?= $requestPath === '/category/' . $sportKey ? 'gf-segmented__item--active' : '' ?>">
                        <?= e($sportInfo['name']) ?>
                    </a>
                    <?php endforeach; ?>
                    <a href="/live" class="gf-segmented__item gf-segmented__item--live <?= $requestPath === '/live' ? 'gf-segmented__item--active' : '' ?>">
                        <span class="gf-live-dot"></span>
                        <?= e(t('nav.live', $lang)) ?>
                    </a>
                </div>
            </div>

            <!-- Right: Lang + Theme Toggle + User + Mobile -->
            <div class="gf-nav__right">
                <!-- Language switcher -->
                <div class="gf-lang-switch">
                    <a href="/set-lang/es" class="gf-lang-switch__item <?= $lang === 'es' ? 'gf-lang-switch__item--active' : '' ?>">ES</a>
                    <a href="/set-lang/en" class="gf-lang-switch__item <?= $lang === 'en' ? 'gf-lang-switch__item--active' : '' ?>">EN</a>
                </div>

                <!-- Theme toggle -->
                <button id="theme-toggle" class="gf-theme-toggle" aria-label="<?= e(t('nav.theme', $lang)) ?>">
                    <!-- Sun (shown in dark mode) -->
                    <svg class="gf-theme-toggle__sun" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                    </svg>
                    <!-- Moon (shown in light mode) -->
                    <svg class="gf-theme-toggle__moon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
                    </svg>
                </button>

                <?php if ($user): ?>
                <!-- Logged-in user menu (desktop) -->
                <div id="user-menu" class="gf-user-menu">
                    <button class="gf-user-menu__trigger">
                        <span class="gf-user-menu__avatar"><?= e($user['initials']) ?></span>
                        <span class="gf-user-menu__name"><?= e($user['display_name']) ?></span>
                        <svg class="gf-user-menu__arrow" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>
                    <div class="gf-user-menu__dropdown">
                        <a href="/auth/profile" class="gf-user-menu__dropdown-item"><?= e(t('nav.profile', $lang)) ?></a>
                        <div class="gf-user-menu__dropdown-divider"></div>
                        <a href="/auth/logout" class="gf-user-menu__dropdown-item"><?= e(t('nav.logout', $lang)) ?></a>
                    </div>
                </div>
                <?php else: ?>
                <!-- Not logged in: login button -->
                <a href="/auth/login" class="gf-btn gf-btn--sm gf-btn--outline"><?= e(t('nav.login', $lang)) ?></a>
                <?php endif; ?>

                <!-- Mobile menu button -->
                <button id="mobile-menu-btn" class="gf-mobile-btn" aria-label="Menu">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="gf-mobile-menu">
        <a href="/" class="gf-mobile-menu__link"><?= e(t('nav.home', $lang)) ?></a>
        <?php foreach ($sportDisplay as $sportKey => $sportInfo): ?>
        <a href="/category/<?= e($sportKey) ?>" class="gf-mobile-menu__link">
            <?= e($sportInfo['name']) ?>
        </a>
        <?php endforeach; ?>
        <a href="/live" class="gf-mobile-menu__link gf-mobile-menu__link--live">
            <span class="gf-live-dot"></span>
            <?= e(t('nav.live', $lang)) ?>
        </a>

        <!-- Mobile lang switch -->
        <div class="gf-mobile-menu__lang">
            <a href="/set-lang/es" class="gf-lang-switch__item <?= $lang === 'es' ? 'gf-lang-switch__item--active' : '' ?>">ES</a>
            <a href="/set-lang/en" class="gf-lang-switch__item <?= $lang === 'en' ? 'gf-lang-switch__item--active' : '' ?>">EN</a>
        </div>

        <!-- Mobile user info -->
        <?php if ($user): ?>
        <div class="gf-mobile-menu__user">
            <span class="gf-user-menu__avatar"><?= e($user['initials']) ?></span>
            <div>
                <div class="gf-mobile-menu__user-name"><?= e($user['display_name']) ?></div>
                <div class="gf-mobile-menu__user-email"><?= e($user['email']) ?></div>
            </div>
        </div>
        <a href="/auth/profile" class="gf-mobile-menu__link"><?= e(t('nav.profile', $lang)) ?></a>
        <a href="/auth/logout" class="gf-mobile-menu__link"><?= e(t('nav.logout', $lang)) ?></a>
        <?php else: ?>
        <a href="/auth/login" class="gf-mobile-menu__link"><?= e(t('nav.login', $lang)) ?></a>
        <a href="/auth/register" class="gf-mobile-menu__link"><?= e(t('nav.register', $lang)) ?></a>
        <?php endif; ?>
    </div>
</nav>
