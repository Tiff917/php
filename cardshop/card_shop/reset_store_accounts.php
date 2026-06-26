<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=UTF-8');

const RESET_TOKEN = 'tscashop-reset-20260624';

if (($_GET['token'] ?? '') !== RESET_TOKEN) {
    http_response_code(403);
    echo "forbidden";
    exit;
}

if (!extension_loaded('pdo_mysql')) {
    echo "pdo_mysql_not_loaded";
    @unlink(__FILE__);
    exit;
}

function clear_directory_files(string $path): int
{
    if (!is_dir($path)) {
        return 0;
    }

    $deleted = 0;
    $items = scandir($path);
    if ($items === false) {
        return 0;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $fullPath = $path . DIRECTORY_SEPARATOR . $item;
        if (is_dir($fullPath)) {
            $deleted += clear_directory_files($fullPath);
            @rmdir($fullPath);
            continue;
        }

        if (@unlink($fullPath)) {
            $deleted++;
        }
    }

    return $deleted;
}

$dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
$pdo = new PDO($dsn, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$pdo->beginTransaction();

try {
    $orderCount = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    $productCount = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    $imageCount = (int) $pdo->query('SELECT COUNT(*) FROM product_images')->fetchColumn();
    $reviewCount = (int) $pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
    $tokenCount = (int) $pdo->query('SELECT COUNT(*) FROM remember_tokens')->fetchColumn();

    $pdo->exec('DELETE FROM reviews');
    $pdo->exec('DELETE FROM remember_tokens');
    $pdo->exec('DELETE FROM orders');
    $pdo->exec('DELETE FROM product_images');
    $pdo->exec('DELETE FROM products');
    $pdo->exec("DELETE FROM users WHERE role IN ('buyer', 'seller')");

    $insertUser = $pdo->prepare(
        'INSERT INTO users
            (username, password_hash, role, display_name, email, phone, favorite_group, address, created_at)
         VALUES
            (:username, :password_hash, :role, :display_name, :email, :phone, :favorite_group, :address, NOW())'
    );

    $insertUser->execute([
        'username' => 'sellerdemo',
        'password_hash' => password_hash('seller123', PASSWORD_DEFAULT),
        'role' => 'seller',
        'display_name' => 'Seller Demo',
        'email' => 'sellerdemo@example.com',
        'phone' => '0911000001',
        'favorite_group' => 'TXT',
        'address' => 'Seller demo address',
    ]);

    $insertUser->execute([
        'username' => 'buyerdemo',
        'password_hash' => password_hash('buyer123', PASSWORD_DEFAULT),
        'role' => 'buyer',
        'display_name' => 'Buyer Demo',
        'email' => 'buyerdemo@example.com',
        'phone' => '0911000002',
        'favorite_group' => 'IVE',
        'address' => 'Buyer demo address',
    ]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo 'reset_failed: ' . $e->getMessage();
    @unlink(__FILE__);
    exit;
}

$deletedProductFiles = clear_directory_files(__DIR__ . '/uploads/products');
$deletedReportFiles = clear_directory_files(__DIR__ . '/uploads/reports');

echo "reset_ok\n";
echo "orders_deleted={$orderCount}\n";
echo "products_deleted={$productCount}\n";
echo "product_images_deleted={$imageCount}\n";
echo "reviews_deleted={$reviewCount}\n";
echo "remember_tokens_deleted={$tokenCount}\n";
echo "product_files_deleted={$deletedProductFiles}\n";
echo "report_files_deleted={$deletedReportFiles}\n";
echo "seller_username=sellerdemo\n";
echo "seller_password=seller123\n";
echo "buyer_username=buyerdemo\n";
echo "buyer_password=buyer123\n";

@unlink(__FILE__);
