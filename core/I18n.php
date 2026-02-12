<?php
/**
 * Lightweight bilingual ES/EN translation system.
 */

$TRANSLATIONS = [
    // ── Navigation ──
    'nav.home'     => ['es' => 'Inicio',         'en' => 'Home'],
    'nav.live'     => ['es' => 'En Vivo',        'en' => 'Live'],
    'nav.football' => ['es' => 'Fútbol',         'en' => 'Football'],
    'nav.login'    => ['es' => 'Iniciar sesión',  'en' => 'Log in'],
    'nav.register' => ['es' => 'Registrarse',    'en' => 'Sign up'],
    'nav.profile'  => ['es' => 'Mi perfil',      'en' => 'My profile'],
    'nav.logout'   => ['es' => 'Cerrar sesión',  'en' => 'Log out'],
    'nav.theme'    => ['es' => 'Cambiar tema',   'en' => 'Toggle theme'],

    // ── Home page ──
    'home.featured'  => ['es' => 'Destacados',        'en' => 'Featured'],
    'home.latest'    => ['es' => 'Últimas Noticias',   'en' => 'Latest News'],
    'home.more'      => ['es' => 'Más Noticias',       'en' => 'More News'],
    'home.empty'     => ['es' => 'No hay artículos publicados aún.', 'en' => 'No articles published yet.'],
    'home.empty_sub' => [
        'es' => 'Los artículos se generan automáticamente desde las fuentes RSS.',
        'en' => 'Articles are generated automatically from RSS feeds.',
    ],

    // ── Pagination ──
    'pagination.prev' => ['es' => 'Anterior',  'en' => 'Previous'],
    'pagination.next' => ['es' => 'Siguiente', 'en' => 'Next'],

    // ── Article detail ──
    'article.reads'      => ['es' => 'lecturas',            'en' => 'reads'],
    'article.source'     => ['es' => 'Fuente original',     'en' => 'Original source'],
    'article.see_source' => ['es' => 'Ver fuente',          'en' => 'View source'],
    'article.related'    => ['es' => 'Artículos Relacionados', 'en' => 'Related Articles'],
    'article.not_found'  => ['es' => 'Artículo no encontrado', 'en' => 'Article not found'],

    // ── Comments ──
    'comments.title'       => ['es' => 'Comentarios',       'en' => 'Comments'],
    'comments.placeholder' => ['es' => 'Escribe un comentario...', 'en' => 'Write a comment...'],
    'comments.submit'      => ['es' => 'Comentar',          'en' => 'Comment'],
    'comments.empty'       => ['es' => 'Sé el primero en comentar', 'en' => 'Be the first to comment'],
    'comments.gate'        => [
        'es' => 'Para poder comentar necesitas estar registrado',
        'en' => 'You need to be registered to comment',
    ],
    'comments.gate_btn'    => ['es' => 'Registrarse',       'en' => 'Sign up'],
    'comments.error_load'  => ['es' => 'Error cargando comentarios', 'en' => 'Error loading comments'],
    'comments.not_auth'    => ['es' => 'No autenticado',    'en' => 'Not authenticated'],
    'comments.empty_text'  => ['es' => 'Comentario vacío',  'en' => 'Empty comment'],

    // ── Category ──
    'category.articles_count' => [
        'es' => '{count} artículo{s} publicado{s}',
        'en' => '{count} published article{s}',
    ],
    'category.empty' => [
        'es' => 'No hay artículos de {sport} aún.',
        'en' => 'No {sport} articles yet.',
    ],

    // ── Leagues & Teams ──
    'nav.leagues' => ['es' => 'Ligas', 'en' => 'Leagues'],
    'league.articles_count' => [
        'es' => '{count} artículo{s} publicado{s}',
        'en' => '{count} published article{s}',
    ],
    'league.empty' => [
        'es' => 'No hay artículos de esta liga aún.',
        'en' => 'No articles for this league yet.',
    ],
    'team.articles_count' => [
        'es' => '{count} artículo{s} publicado{s}',
        'en' => '{count} published article{s}',
    ],
    'team.empty' => [
        'es' => 'No hay artículos de este equipo aún.',
        'en' => 'No articles for this team yet.',
    ],

    // ── Live ──
    'live.title'      => ['es' => 'En Vivo',             'en' => 'Live'],
    'live.updated'    => ['es' => 'Actualizado ahora',   'en' => 'Updated now'],
    'live.updated_at' => ['es' => 'Actualizado',         'en' => 'Updated'],
    'live.empty'      => [
        'es' => 'No hay partidos en vivo en este momento.',
        'en' => 'No live matches at this time.',
    ],
    'live.empty_sub' => [
        'es' => 'Esta página se actualiza automáticamente.',
        'en' => 'This page refreshes automatically.',
    ],
    'live.halftime' => ['es' => 'Descanso',  'en' => 'Half-time'],
    'live.fulltime' => ['es' => 'Final',     'en' => 'Full-time'],

    // ── Footer ──
    'footer.description' => [
        'es' => 'Tu portal de noticias de fútbol.',
        'en' => 'Your football news portal.',
    ],
    'footer.categories' => ['es' => 'Categorías', 'en' => 'Categories'],
    'footer.info'       => ['es' => 'Info',        'en' => 'Info'],
    'footer.copyright'  => [
        'es' => 'GoalFeed. Las fuentes originales son citadas en cada artículo.',
        'en' => 'GoalFeed. Original sources are cited in every article.',
    ],

    // ── Base / Meta ──
    'meta.subtitle'       => ['es' => 'Noticias Deportivas', 'en' => 'Sports News'],
    'meta.description'    => [
        'es' => 'GoalFeed - Tu portal de noticias de fútbol.',
        'en' => 'GoalFeed - Your football news portal.',
    ],
    'meta.og_title' => [
        'es' => 'GoalFeed - Noticias Deportivas',
        'en' => 'GoalFeed - Sports News',
    ],
    'meta.og_description' => [
        'es' => 'Tu portal de noticias de fútbol.',
        'en' => 'Your football news portal.',
    ],

    // ── Auth ──
    'auth.login_title'    => ['es' => 'Iniciar sesión',  'en' => 'Log in'],
    'auth.register_title' => ['es' => 'Crear cuenta',    'en' => 'Create account'],
    'auth.email'          => ['es' => 'Correo electrónico', 'en' => 'Email'],
    'auth.password'       => ['es' => 'Contraseña',      'en' => 'Password'],
    'auth.confirm_password' => ['es' => 'Confirmar contraseña', 'en' => 'Confirm password'],
    'auth.display_name'   => ['es' => 'Nombre',          'en' => 'Name'],
    'auth.login_btn'      => ['es' => 'Iniciar sesión',  'en' => 'Log in'],
    'auth.register_btn'   => ['es' => 'Registrarse',     'en' => 'Sign up'],
    'auth.google_btn'     => ['es' => 'Continuar con Google', 'en' => 'Continue with Google'],
    'auth.or_separator'   => ['es' => 'o',               'en' => 'or'],
    'auth.no_account'     => ['es' => '¿No tienes cuenta?', 'en' => "Don't have an account?"],
    'auth.has_account'    => ['es' => '¿Ya tienes cuenta?', 'en' => 'Already have an account?'],
    'auth.profile_title'  => ['es' => 'Mi perfil',       'en' => 'My profile'],
    'auth.member_since'   => ['es' => 'Miembro desde',   'en' => 'Member since'],
    'auth.provider'       => ['es' => 'Método de acceso', 'en' => 'Sign-in method'],
    'auth.error_email_exists' => [
        'es' => 'Ya existe una cuenta con ese correo',
        'en' => 'An account with that email already exists',
    ],
    'auth.error_invalid' => [
        'es' => 'Correo o contraseña incorrectos',
        'en' => 'Invalid email or password',
    ],
    'auth.error_password_mismatch' => [
        'es' => 'Las contraseñas no coinciden',
        'en' => 'Passwords do not match',
    ],
    'auth.error_fields' => [
        'es' => 'Todos los campos son obligatorios',
        'en' => 'All fields are required',
    ],
    'auth.error_password_short' => [
        'es' => 'La contraseña debe tener al menos 8 caracteres',
        'en' => 'Password must be at least 8 characters',
    ],

    // ── JS time formatting (injected as window.GF_I18N) ──
    'js.now'           => ['es' => 'ahora',     'en' => 'now'],
    'js.min'           => ['es' => 'min',       'en' => 'min'],
    'js.h'             => ['es' => 'h',         'en' => 'h'],
    'js.halftime'      => ['es' => 'Descanso',  'en' => 'Half-time'],
    'js.fulltime'      => ['es' => 'Final',     'en' => 'Full-time'],
    'js.updated'       => ['es' => 'Actualizado', 'en' => 'Updated'],
    'js.comment_empty' => ['es' => 'Sé el primero en comentar', 'en' => 'Be the first to comment'],
    'js.comment_error' => ['es' => 'Error cargando comentarios', 'en' => 'Error loading comments'],
    'js.no_live'       => [
        'es' => 'No hay partidos en vivo en este momento.',
        'en' => 'No live matches at this time.',
    ],

    // ── Legal ──
    'legal.privacy_title' => ['es' => 'Política de Privacidad', 'en' => 'Privacy Policy'],
    'legal.terms_title'   => ['es' => 'Términos de Uso',        'en' => 'Terms of Use'],
    'legal.privacy'       => ['es' => 'Privacidad',             'en' => 'Privacy'],
    'legal.terms'         => ['es' => 'Términos',               'en' => 'Terms'],

    // ── Language switcher ──
    'lang.es' => ['es' => 'ES', 'en' => 'ES'],
    'lang.en' => ['es' => 'EN', 'en' => 'EN'],
];

/**
 * Translate key into lang.
 * Falls back: requested lang -> es -> key itself.
 * Supports {placeholder} interpolation via $args.
 */
function t(string $key, string $lang = 'es', array $args = []): string {
    global $TRANSLATIONS;
    $entry = $TRANSLATIONS[$key] ?? null;
    if ($entry === null) return $key;
    $text = $entry[$lang] ?? $entry['es'] ?? $key;
    foreach ($args as $k => $v) {
        $text = str_replace('{' . $k . '}', $v, $text);
    }
    return $text;
}

/** Read preferred language from gf_lang cookie (default 'es'). */
function getLang(): string {
    $lang = $_COOKIE['gf_lang'] ?? 'es';
    return in_array($lang, ['es', 'en']) ? $lang : 'es';
}

/** Return the JS-needed subset of translations. */
function getJsTranslations(string $lang): array {
    global $TRANSLATIONS;
    $out = [];
    foreach ($TRANSLATIONS as $k => $v) {
        if (strpos($k, 'js.') === 0) {
            $out[substr($k, 3)] = t($k, $lang);
        }
    }
    return $out;
}

/** HTML-escape helper. */
function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
