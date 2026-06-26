<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=UTF-8');

const CLEANUP_TOKEN = 'tscashop-cleanup-20260624';

if (($_GET['token'] ?? '') !== CLEANUP_TOKEN) {
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

$patterns = [
    'DEMO-REPORT-202606%',
    'DEMO-REPORT-V2-202606%',
    'TXT-SOOBIN-REDPOP',
    'TXT-KAI-WINK',
];

$names = [
    'IVE 安兪真粉卡',
    'NewJeans Hanni 拍立得卡',
    'FlowCard_20260619171641',
    'MilkyTest_20260618155120',
    'TestCard_20260616212041',
    'TXT Soobin Red Pop Photocard',
    'TXT Kai Wink Photocard',
    'TXT Blue Hour Soobin Card',
    'TXT Freefall Yeonjun Card',
    'TXT Thursday Kai Card',
    'IVE Switch Yujin Card',
    'IVE I AM Rei Card',
    'NewJeans Get Up Hanni Card',
    'NewJeans OMG Minji Card',
    'SEVENTEEN FML Wonwoo Card',
    'LE SSERAFIM Easy Chaewon Card',
    'aespa Drama Karina Card',
];

$selectByPattern = $pdo->prepare('SELECT id FROM products WHERE card_code LIKE :pattern');
$selectByCode = $pdo->prepare('SELECT id FROM products WHERE card_code = :code');
$selectByName = $pdo->prepare('SELECT id FROM products WHERE name = :name');

$productIds = [];
foreach ($patterns as $pattern) {
    if (str_contains($pattern, '%')) {
        $selectByPattern->execute(['pattern' => $pattern]);
        foreach ($selectByPattern->fetchAll() as $row) {
            $productIds[(int) $row['id']] = true;
        }
    } else {
        $selectByCode->execute(['code' => $pattern]);
        foreach ($selectByCode->fetchAll() as $row) {
            $productIds[(int) $row['id']] = true;
        }
    }
}

foreach ($names as $name) {
    $selectByName->execute(['name' => $name]);
    foreach ($selectByName->fetchAll() as $row) {
        $productIds[(int) $row['id']] = true;
    }
}

$productIdList = array_keys($productIds);
$deletedOrders = 0;
$deletedImages = 0;
$deletedProducts = 0;

if ($productIdList !== []) {
    $placeholders = implode(',', array_fill(0, count($productIdList), '?'));

    $stmt = $pdo->prepare("DELETE FROM orders WHERE product_id IN ($placeholders)");
    $stmt->execute($productIdList);
    $deletedOrders = $stmt->rowCount();

    $stmt = $pdo->prepare("DELETE FROM product_images WHERE product_id IN ($placeholders)");
    $stmt->execute($productIdList);
    $deletedImages = $stmt->rowCount();

    $stmt = $pdo->prepare("DELETE FROM products WHERE id IN ($placeholders)");
    $stmt->execute($productIdList);
    $deletedProducts = $stmt->rowCount();
}

$demoUsernames = [];
for ($i = 1; $i <= 4; $i++) {
    $demoUsernames[] = 'demo_report_buyer_' . $i;
}
for ($i = 1; $i <= 6; $i++) {
    $demoUsernames[] = 'demo_report_buyer_v2_' . $i;
}

$deletedUsers = 0;
if ($demoUsernames !== []) {
    $placeholders = implode(',', array_fill(0, count($demoUsernames), '?'));
    $stmt = $pdo->prepare("DELETE FROM users WHERE username IN ($placeholders)");
    $stmt->execute($demoUsernames);
    $deletedUsers = $stmt->rowCount();
}

echo "cleanup_ok\n";
echo "products_deleted=" . $deletedProducts . "\n";
echo "product_images_deleted=" . $deletedImages . "\n";
echo "orders_deleted=" . $deletedOrders . "\n";
echo "users_deleted=" . $deletedUsers . "\n";

@unlink(__FILE__);
