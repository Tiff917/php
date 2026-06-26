<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

header('Content-Type: text/plain; charset=UTF-8');

try {
    $stmt = db()->query('SELECT 1 AS ok');
    $row = $stmt->fetch();
    echo "DB_OK\n";
    echo json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo "DB_FAIL\n";
    echo get_class($e) . "\n";
    echo $e->getMessage() . "\n";
}
