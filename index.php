<?php
/**
 * GoalFeed — Front Controller
 * All requests (except /static/) are routed through here.
 */

// ── Bootstrap ──
require __DIR__ . '/config.php';

require __DIR__ . '/core/Database.php';
require __DIR__ . '/core/Router.php';
require __DIR__ . '/core/View.php';
require __DIR__ . '/core/I18n.php';
require __DIR__ . '/core/Icons.php';
require __DIR__ . '/core/Session.php';
require __DIR__ . '/core/Auth.php';

require __DIR__ . '/models/ArticleRepository.php';
require __DIR__ . '/models/CommentRepository.php';
require __DIR__ . '/models/UserRepository.php';
require __DIR__ . '/models/SessionRepository.php';
require __DIR__ . '/models/LiveRepository.php';

require __DIR__ . '/controllers/HomeController.php';
require __DIR__ . '/controllers/ArticleController.php';
require __DIR__ . '/controllers/CategoryController.php';
require __DIR__ . '/controllers/LiveController.php';
require __DIR__ . '/controllers/ApiController.php';
require __DIR__ . '/controllers/AuthController.php';
require __DIR__ . '/controllers/SitemapController.php';
require __DIR__ . '/controllers/LangController.php';
require __DIR__ . '/controllers/LegalController.php';

// ── Init view engine ──
View::init(__DIR__ . '/views');

// ── Routes ──
$router = new Router();

// Pages
$router->get('/',                        ['HomeController',     'index']);
$router->get('/article/([a-zA-Z0-9_-]+)', ['ArticleController', 'show']);
$router->get('/category/([a-zA-Z0-9_]+)', ['CategoryController','show']);
$router->get('/live',                    ['LiveController',     'index']);

// API
$router->get('/api/articles',            ['ApiController',      'articles']);
$router->get('/api/live',                ['ApiController',      'live']);
$router->get('/api/comments/(\d+)',      ['ApiController',      'getComments']);
$router->post('/api/comments/(\d+)',     ['ApiController',      'postComment']);

// Auth
$router->get('/auth/login',             ['AuthController',      'loginPage']);
$router->post('/auth/login',            ['AuthController',      'loginSubmit']);
$router->get('/auth/register',          ['AuthController',      'registerPage']);
$router->post('/auth/register',         ['AuthController',      'registerSubmit']);
$router->get('/auth/logout',            ['AuthController',      'logout']);
$router->get('/auth/google',            ['AuthController',      'googleLogin']);
$router->get('/auth/google/callback',   ['AuthController',      'googleCallback']);
$router->get('/auth/profile',           ['AuthController',      'profile']);

// Sitemap & robots
$router->get('/sitemap\.xml',           ['SitemapController',   'sitemap']);
$router->get('/robots\.txt',            ['SitemapController',   'robots']);

// Legal
$router->get('/privacy',               ['LegalController',     'privacy']);
$router->get('/terms',                 ['LegalController',     'terms']);

// Language switcher
$router->get('/set-lang/(es|en)',       ['LangController',      'setLang']);

// ── Dispatch ──
if (!$router->dispatch()) {
    // 404
    http_response_code(404);
    $lang = getLang();
    View::render('errors/404', compact('lang'));
}
