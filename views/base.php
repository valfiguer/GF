<?php
$lang = getLang();
$currentUser = Session::getCurrentUser();
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pageTitle = $pageTitle ?? 'GoalFeed';
$metaDescription = $metaDescription ?? t('meta.description', $lang);
$metaKeywords = $metaKeywords ?? 'noticias deportivas, fútbol, football, fichajes, resultados';
$ogType = $ogType ?? 'website';
$ogTitle = $ogTitle ?? t('meta.og_title', $lang);
$ogDescription = $ogDescription ?? t('meta.og_description', $lang);
$ogImage = $ogImage ?? BASE_URL . '/static/images/og-default.jpg';
$headExtra = $headExtra ?? '';
$scriptsExtra = $scriptsExtra ?? '';
?><!DOCTYPE html>
<html lang="<?= e($lang) ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= e(t('meta.subtitle', $lang)) ?></title>

    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta name="keywords" content="<?= e($metaKeywords) ?>">
    <meta name="theme-color" content="#f5f5f7">

    <!-- Open Graph -->
    <meta property="og:type" content="<?= e($ogType) ?>">
    <meta property="og:title" content="<?= e($ogTitle) ?>">
    <meta property="og:description" content="<?= e($ogDescription) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:url" content="<?= e(BASE_URL . $requestPath) ?>">
    <meta property="og:site_name" content="GoalFeed">

    <!-- Prevent FOUC: apply theme before paint -->
    <script>
        (function() {
            var t = localStorage.getItem('gf-theme');
            if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', t);
            var m = document.querySelector('meta[name="theme-color"]');
            if (m) m.content = t === 'dark' ? '#1d1d1f' : '#f5f5f7';
        })();
    </script>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fffbeb', 100: '#fff3c4', 200: '#fce588',
                            300: '#fbd24e', 400: '#ffcf25', 500: '#e6b800',
                            600: '#b8920a', 700: '#926d00', 800: '#6b5000', 900: '#4a3800'
                        },
                    },
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', '"SF Pro Display"', '"SF Pro Text"', '"Helvetica Neue"', 'Arial', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="/static/css/style.css">
    <link rel="icon" type="image/x-icon" href="/static/images/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/static/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/static/images/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/static/images/apple-touch-icon.png">

    <?= $headExtra ?>
</head>
<body>

    <?php View::partial('nav', compact('lang', 'currentUser', 'requestPath')); ?>

    <main style="flex: 1;">
        <?= $content ?>
    </main>

    <?php View::partial('footer', compact('lang')); ?>

    <script>window.GF_LANG = "<?= e($lang) ?>"; window.GF_I18N = <?= json_encode(getJsTranslations($lang), JSON_UNESCAPED_UNICODE) ?>;</script>
    <script src="/static/js/main.js"></script>
    <?= $scriptsExtra ?>
</body>
</html>
