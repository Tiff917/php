<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=UTF-8');

const DELETE_TOKEN = 'tscashop-delete-kai-20260624';

if (($_GET['token'] ?? '') !== DELETE_TOKEN) {
    http_response_code(403);
    echo "forbidden";
    exit;
}

if (!extension_loaded('pdo_mysql')) {
    echo "pdo_mysql_not_loaded";
    @unlink(__FILE__);
    exit;
}

$dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
$pdo = new PDO($dsn, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$stmt = $pdo->prepare("SELECT id FROM products WHERE seller_id = (SELECT id FROM users WHERE username = 'sellerdemo' LIMIT 1) AND name = 'kai'");
$stmt->execute();
$ids = array_map(static fn(array $row): int => (int) $row['id'], $stmt->fetchAll());

if ($ids !== []) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $pdo->prepare("DELETE FROM product_images WHERE product_id IN ($placeholders)")->execute($ids);
    $pdo->prepare("DELETE FROM products WHERE id IN ($placeholders)")->execute($ids);
}

echo "delete_ok\n";
echo "products_deleted=" . count($ids) . "\n";

@unlink(__FILE__);
