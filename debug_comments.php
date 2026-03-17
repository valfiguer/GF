<?php
/**
 * Fix: add missing user_id column to web_comments.
 * Access via: https://goal-feed.com/debug_comments.php
 * DELETE THIS FILE after running.
 */
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/config.php';
require __DIR__ . '/core/Database.php';

$results = [];

try {
    $pdo = Database::get();
    $results['db_connection'] = true;
} catch (\Throwable $e) {
    $results['db_connection'] = 'ERROR: ' . $e->getMessage();
    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// 1. Add missing user_id column
try {
    $pdo->exec("ALTER TABLE web_comments ADD COLUMN user_id INT NULL AFTER comment_text");
    $pdo->exec("ALTER TABLE web_comments ADD INDEX idx_web_comments_user (user_id)");
    $results['alter_table'] = 'OK — user_id column added';
} catch (\Throwable $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        $results['alter_table'] = 'SKIP — column already exists';
    } else {
        $results['alter_table'] = 'ERROR: ' . $e->getMessage();
    }
}

// 2. Verify columns now
try {
    $stmt = $pdo->query("DESCRIBE web_comments");
    $cols = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
    $results['columns'] = $cols;
    $results['has_user_id'] = in_array('user_id', $cols);
} catch (\Throwable $e) {
    $results['columns'] = 'ERROR: ' . $e->getMessage();
}

// 3. Test INSERT with rollback
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare(
        "INSERT INTO web_comments (web_article_id, user_name, user_initials, comment_text, user_id)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([1, 'TEST', 'TT', 'Test - rolled back', null]);
    $results['test_insert'] = 'OK — INSERT works';
    $pdo->rollBack();
} catch (\Throwable $e) {
    $results['test_insert'] = 'ERROR: ' . $e->getMessage();
    if ($pdo->inTransaction()) $pdo->rollBack();
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
