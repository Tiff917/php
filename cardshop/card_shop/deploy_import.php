<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$token = $_GET['token'] ?? '';
$expectedToken = 'codex-import-20260619';
$logPath = __DIR__ . '/uploads/deploy-import.log';

if (!hash_equals($expectedToken, $token)) {
    http_response_code(403);
    exit('forbidden');
}

try {
    $sqlFile = __DIR__ . '/card_shop_infinityfree.sql';
    if (!is_file($sqlFile)) {
        throw new RuntimeException('sql file missing');
    }

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int) DB_PORT);
    if ($mysqli->connect_error) {
        throw new RuntimeException('db connect failed: ' . $mysqli->connect_error);
    }

    $mysqli->set_charset('utf8mb4');
    $sql = file_get_contents($sqlFile);
    if ($sql === false) {
        throw new RuntimeException('failed to read sql');
    }
    $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql) ?? $sql;

    if (!$mysqli->multi_query($sql)) {
        throw new RuntimeException('sql import failed: ' . $mysqli->error);
    }

    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());

    if ($mysqli->errno) {
        throw new RuntimeException('sql import failed: ' . $mysqli->error);
    }

    echo 'IMPORT_OK';
} catch (Throwable $e) {
    http_response_code(500);
    $line = '[' . date('Y-m-d H:i:s') . '] ' . get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
    @file_put_contents($logPath, $line, FILE_APPEND);
    echo 'IMPORT_FAIL';
}
