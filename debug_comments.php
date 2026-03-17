<?php
/**
 * Diagnostic script for comments 500 error.
 * Access via: https://goal-feed.com/debug_comments.php
 * DELETE THIS FILE after debugging.
 */
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/config.php';
require __DIR__ . '/core/Database.php';

$results = [
    'db_connection' => false,
    'table_exists'  => false,
    'table_columns' => [],
    'sessions_table' => false,
    'users_table'    => false,
    'test_select'    => null,
    'test_insert'    => null,
    'php_errors'     => null,
];

// 1. Test DB connection
try {
    $pdo = Database::get();
    $results['db_connection'] = true;
} catch (\Throwable $e) {
    $results['db_connection'] = 'ERROR: ' . $e->getMessage();
    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. Check if web_comments table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'web_comments'");
    $results['table_exists'] = $stmt->rowCount() > 0;
} catch (\Throwable $e) {
    $results['table_exists'] = 'ERROR: ' . $e->getMessage();
}

// 3. Get table columns (if table exists)
if ($results['table_exists'] === true) {
    try {
        $stmt = $pdo->query("DESCRIBE web_comments");
        $results['table_columns'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Throwable $e) {
        $results['table_columns'] = 'ERROR: ' . $e->getMessage();
    }
}

// 4. Check sessions table
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'sessions'");
    $results['sessions_table'] = $stmt->rowCount() > 0;
} catch (\Throwable $e) {
    $results['sessions_table'] = 'ERROR: ' . $e->getMessage();
}

// 5. Check users table
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $results['users_table'] = $stmt->rowCount() > 0;
} catch (\Throwable $e) {
    $results['users_table'] = 'ERROR: ' . $e->getMessage();
}

// 6. Test SELECT on web_comments
try {
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM web_comments");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $results['test_select'] = 'OK — ' . $row['cnt'] . ' comments in table';
} catch (\Throwable $e) {
    $results['test_select'] = 'ERROR: ' . $e->getMessage();
}

// 7. Test INSERT + DELETE (dry run)
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare(
        "INSERT INTO web_comments (web_article_id, user_name, user_initials, comment_text, user_id)
         VALUES (?, ?, ?, ?, ?)"
    );
    // Use web_article_id=1 as test — FK check
    $stmt->execute([1, 'TEST_USER', 'TU', 'Test comment - will be rolled back', null]);
    $results['test_insert'] = 'OK — INSERT succeeded (rolled back)';
    $pdo->rollBack();
} catch (\Throwable $e) {
    $results['test_insert'] = 'ERROR: ' . $e->getMessage();
    if ($pdo->inTransaction()) $pdo->rollBack();
}

// 8. Check PHP error reporting
$results['php_errors'] = [
    'display_errors' => ini_get('display_errors'),
    'error_reporting' => ini_get('error_reporting'),
    'error_log'       => ini_get('error_log'),
];

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
