<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=UTF-8');

const DEMO_SEED_TOKEN = 'tscashop-demo-report-20260624-v2';

if (($_GET['token'] ?? '') !== DEMO_SEED_TOKEN) {
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

$sellerStmt = $pdo->prepare("SELECT id, display_name FROM users WHERE username = 'seller01' LIMIT 1");
$sellerStmt->execute();
$seller = $sellerStmt->fetch();

if (!$seller) {
    echo "seller_not_found";
    @unlink(__FILE__);
    exit;
}

$buyerStmt = $pdo->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
$insertBuyerStmt = $pdo->prepare(
    'INSERT INTO users
        (username, password_hash, role, display_name, email, phone, favorite_group, address, created_at)
     VALUES
        (:username, :password_hash, :role, :display_name, :email, :phone, :favorite_group, :address, NOW())'
);

$buyers = [];
for ($i = 1; $i <= 6; $i++) {
    $username = 'demo_report_buyer_v2_' . $i;
    $buyerStmt->execute(['username' => $username]);
    $buyer = $buyerStmt->fetch();

    if (!$buyer) {
        $insertBuyerStmt->execute([
            'username' => $username,
            'password_hash' => password_hash('buyer123', PASSWORD_DEFAULT),
            'role' => 'buyer',
            'display_name' => 'Report Buyer ' . $i,
            'email' => $username . '@example.com',
            'phone' => '091100000' . $i,
            'favorite_group' => 'TXT',
            'address' => 'Demo report address ' . $i,
        ]);
        $buyerId = (int) $pdo->lastInsertId();
    } else {
        $buyerId = (int) $buyer['id'];
    }

    $buyers[] = $buyerId;
}

$prefixes = ['DEMO-REPORT-202606%', 'DEMO-REPORT-V2-202606%'];
$deleteOrdersStmt = $pdo->prepare(
    "DELETE o FROM orders o
     INNER JOIN products p ON p.id = o.product_id
     WHERE p.card_code LIKE :prefix"
);
$deleteProductsStmt = $pdo->prepare("DELETE FROM products WHERE card_code LIKE :prefix");

foreach ($prefixes as $prefix) {
    $deleteOrdersStmt->execute(['prefix' => $prefix]);
    $deleteProductsStmt->execute(['prefix' => $prefix]);
}

$productStmt = $pdo->prepare(
    'INSERT INTO products
        (seller_id, name, description, price, stock, status, condition_tags, group_name, member_name, album_name, card_version, card_code, created_at, updated_at, sold_at)
     VALUES
        (:seller_id, :name, :description, :price, :stock, :status, :condition_tags, :group_name, :member_name, :album_name, :card_version, :card_code, NOW(), NOW(), NULL)'
);

$orderStmt = $pdo->prepare(
    'INSERT INTO orders
        (product_id, buyer_id, seller_id, quantity, total_amount, status, created_at, paid_at, notification_sent_at)
     VALUES
        (:product_id, :buyer_id, :seller_id, :quantity, :total_amount, :status, :created_at, :paid_at, :notification_sent_at)'
);

$products = [
    ['TXT Blue Hour Soobin Card', 'TXT', 'Soobin', 'Blue Hour', 'Broadcast', 390.0],
    ['TXT Freefall Yeonjun Card', 'TXT', 'Yeonjun', 'Freefall', 'Fan Sign', 430.0],
    ['TXT Thursday Kai Card', 'TXT', 'Kai', 'Thursday Child', 'Lucky Draw', 360.0],
    ['IVE Switch Yujin Card', 'IVE', 'Yujin', 'Switch', 'Lucky Draw', 370.0],
    ['IVE I AM Rei Card', 'IVE', 'Rei', 'I AM', 'Album Ver', 300.0],
    ['NewJeans Get Up Hanni Card', 'NewJeans', 'Hanni', 'Get Up', 'Broadcast', 470.0],
    ['NewJeans OMG Minji Card', 'NewJeans', 'Minji', 'OMG', 'Fan Call', 450.0],
    ['SEVENTEEN FML Wonwoo Card', 'SEVENTEEN', 'Wonwoo', 'FML', 'Weverse', 350.0],
    ['LE SSERAFIM Easy Chaewon Card', 'LE SSERAFIM', 'Chaewon', 'Easy', 'Album Ver', 340.0],
    ['aespa Drama Karina Card', 'aespa', 'Karina', 'Drama', 'Special Ver', 410.0],
];

$created = [];
foreach ($products as $index => [$name, $group, $member, $album, $version, $price]) {
    $cardCode = 'DEMO-REPORT-V2-202606-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
    $productStmt->execute([
        'seller_id' => (int) $seller['id'],
        'name' => $name,
        'description' => 'Enhanced demo monthly report product',
        'price' => $price,
        'stock' => 80,
        'status' => 'active',
        'condition_tags' => 'Like New,Official',
        'group_name' => $group,
        'member_name' => $member,
        'album_name' => $album,
        'card_version' => $version,
        'card_code' => $cardCode,
    ]);

    $created[] = [
        'id' => (int) $pdo->lastInsertId(),
        'price' => $price,
    ];
}

$seedOrders = [
    [0, 0, 1, '2026-06-01 10:05:00'],
    [3, 1, 2, '2026-06-01 19:12:00'],
    [5, 2, 1, '2026-06-02 13:24:00'],
    [1, 3, 1, '2026-06-03 15:40:00'],
    [8, 4, 2, '2026-06-03 21:18:00'],
    [4, 5, 1, '2026-06-04 09:12:00'],
    [6, 0, 1, '2026-06-05 14:22:00'],
    [7, 1, 2, '2026-06-05 20:44:00'],
    [2, 2, 1, '2026-06-06 11:00:00'],
    [9, 3, 1, '2026-06-07 17:32:00'],
    [0, 4, 2, '2026-06-08 12:48:00'],
    [3, 5, 1, '2026-06-08 18:27:00'],
    [5, 0, 1, '2026-06-09 08:55:00'],
    [6, 1, 2, '2026-06-10 22:05:00'],
    [1, 2, 1, '2026-06-11 16:45:00'],
    [8, 3, 1, '2026-06-11 20:16:00'],
    [4, 4, 3, '2026-06-12 10:28:00'],
    [7, 5, 1, '2026-06-13 19:09:00'],
    [2, 0, 1, '2026-06-14 09:36:00'],
    [9, 1, 2, '2026-06-14 23:01:00'],
    [0, 2, 1, '2026-06-15 12:13:00'],
    [5, 3, 2, '2026-06-16 18:18:00'],
    [6, 4, 1, '2026-06-17 14:42:00'],
    [3, 5, 2, '2026-06-18 11:31:00'],
    [1, 0, 1, '2026-06-18 21:24:00'],
    [8, 1, 2, '2026-06-19 13:14:00'],
    [4, 2, 1, '2026-06-20 17:33:00'],
    [7, 3, 1, '2026-06-20 22:48:00'],
    [9, 4, 1, '2026-06-21 09:11:00'],
    [2, 5, 2, '2026-06-22 15:20:00'],
    [0, 0, 1, '2026-06-22 19:40:00'],
    [5, 1, 1, '2026-06-23 10:15:00'],
    [6, 2, 2, '2026-06-23 21:38:00'],
    [3, 3, 1, '2026-06-24 12:00:00'],
    [1, 4, 2, '2026-06-24 18:22:00'],
    [9, 5, 1, '2026-06-25 08:47:00'],
];

foreach ($seedOrders as [$productIndex, $buyerIndex, $quantity, $createdAt]) {
    $product = $created[$productIndex];
    $orderStmt->execute([
        'product_id' => $product['id'],
        'buyer_id' => $buyers[$buyerIndex],
        'seller_id' => (int) $seller['id'],
        'quantity' => $quantity,
        'total_amount' => $product['price'] * $quantity,
        'status' => 'paid',
        'created_at' => $createdAt,
        'paid_at' => $createdAt,
        'notification_sent_at' => $createdAt,
    ]);
}

echo "seed_ok\n";
echo "seller_id=" . $seller['id'] . "\n";
echo "seller_name=" . $seller['display_name'] . "\n";
echo "orders_inserted=" . count($seedOrders) . "\n";
echo "products_created=" . count($created) . "\n";
echo "month=2026-06\n";

@unlink(__FILE__);
