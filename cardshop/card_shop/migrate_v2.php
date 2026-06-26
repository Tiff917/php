<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function create_database_if_missing(): void
{
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
}

function column_exists(string $table, string $column): bool
{
    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = :schema_name
           AND TABLE_NAME = :table_name
           AND COLUMN_NAME = :column_name'
    );
    $stmt->execute([
        'schema_name' => DB_NAME,
        'table_name' => $table,
        'column_name' => $column,
    ]);

    return (int) $stmt->fetchColumn() > 0;
}

function ensure_column(string $table, string $column, string $definition): void
{
    if (!column_exists($table, $column)) {
        db()->exec("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
    }
}

function seed_users(): void
{
    $users = [
        ['admin', 'admin123', 'admin', 'Milky Admin', 'admin@example.com', '0912000000', 'NewJeans', '台北市信義區松仁路 100 號'],
        ['seller01', 'seller123', 'seller', 'Cream Seller', 'seller@example.com', '0922000000', 'IVE', '高雄市鼓山區美術東路 20 號'],
        ['buyer01', 'buyer123', 'buyer', 'Latte Buyer', 'buyer@example.com', '0933000000', 'SEVENTEEN', '台中市西屯區台灣大道 99 號'],
    ];

    $check = db()->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
    $insert = db()->prepare(
        'INSERT INTO users
            (username, password_hash, role, display_name, email, phone, favorite_group, address, created_at)
         VALUES
            (:username, :password_hash, :role, :display_name, :email, :phone, :favorite_group, :address, NOW())'
    );

    foreach ($users as [$username, $password, $role, $displayName, $email, $phone, $favoriteGroup, $address]) {
        $check->execute(['username' => $username]);
        if ($check->fetch()) {
            continue;
        }

        $insert->execute([
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'display_name' => $displayName,
            'email' => $email,
            'phone' => $phone,
            'favorite_group' => $favoriteGroup,
            'address' => $address,
        ]);
    }
}

function seed_products(): void
{
    $sellerId = (int) db()->query("SELECT id FROM users WHERE role = 'seller' ORDER BY id LIMIT 1")->fetchColumn();
    if ($sellerId === 0) {
        return;
    }

    $existing = (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn();
    if ($existing > 0) {
        return;
    }

    $products = [
        ['IVE 安兪真首週特典', '奶油色保護套裝，卡況漂亮適合收藏。', 320.00, 1, 'active', '近全新,限量特典', 'IVE', '安兪真', 'I AM', '首週特典', 'IVE-YUJIN-001'],
        ['NewJeans Hanni 拍立得卡', '實拍柔霧感，適合韓系收藏牆。', 450.00, 2, 'active', '未拆封,官方卡套', 'NewJeans', 'Hanni', 'Get Up', '拍立得', 'NJ-HANNI-003'],
        ['SEVENTEEN 團卡組', '一次入手三張，適合新手入坑。', 590.00, 0, 'sold_out', '輕微瑕疵,稀有閃卡', 'SEVENTEEN', '團卡', 'FML', '收藏卡組', 'SVT-GROUP-007'],
    ];

    $insert = db()->prepare(
        'INSERT INTO products
            (seller_id, name, description, price, stock, status, condition_tags, group_name, member_name, album_name, card_version, card_code, created_at, updated_at, sold_at)
         VALUES
            (:seller_id, :name, :description, :price, :stock, :status, :condition_tags, :group_name, :member_name, :album_name, :card_version, :card_code, NOW(), NOW(), :sold_at)'
    );

    foreach ($products as [$name, $description, $price, $stock, $status, $tags, $groupName, $memberName, $albumName, $version, $cardCode]) {
        $insert->execute([
            'seller_id' => $sellerId,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'status' => $status,
            'condition_tags' => $tags,
            'group_name' => $groupName,
            'member_name' => $memberName,
            'album_name' => $albumName,
            'card_version' => $version,
            'card_code' => $cardCode,
            'sold_at' => $status === 'sold_out' ? date('Y-m-d H:i:s') : null,
        ]);
    }
}

create_database_if_missing();

$pdo = db();
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'seller', 'buyer') NOT NULL DEFAULT 'buyer',
        display_name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        created_at DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS products (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        seller_id INT UNSIGNED NOT NULL,
        name VARCHAR(150) NOT NULL,
        description TEXT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        stock INT NOT NULL DEFAULT 0,
        status ENUM('active', 'sold_out') NOT NULL DEFAULT 'active',
        condition_tags VARCHAR(255) NOT NULL DEFAULT '',
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        CONSTRAINT fk_products_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS product_images (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id INT UNSIGNED NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        is_primary TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL,
        CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS orders (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id INT UNSIGNED NOT NULL,
        buyer_id INT UNSIGNED NOT NULL,
        seller_id INT UNSIGNED NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('paid', 'completed') NOT NULL DEFAULT 'paid',
        created_at DATETIME NOT NULL,
        CONSTRAINT fk_orders_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        CONSTRAINT fk_orders_buyer FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_orders_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS remember_tokens (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        selector VARCHAR(32) NOT NULL UNIQUE,
        token_hash VARCHAR(64) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at DATETIME NOT NULL,
        last_used_at DATETIME NOT NULL,
        CONSTRAINT fk_remember_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS reviews (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id INT UNSIGNED NOT NULL,
        buyer_id INT UNSIGNED NOT NULL,
        seller_id INT UNSIGNED NOT NULL,
        rating TINYINT UNSIGNED NOT NULL,
        comment TEXT NOT NULL,
        created_at DATETIME NOT NULL,
        UNIQUE KEY uq_review_once (order_id, buyer_id, seller_id),
        CONSTRAINT fk_reviews_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        CONSTRAINT fk_reviews_buyer FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_reviews_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

ensure_column('users', 'phone', "VARCHAR(30) NOT NULL DEFAULT '' AFTER email");
ensure_column('users', 'favorite_group', "VARCHAR(100) NOT NULL DEFAULT '' AFTER phone");
ensure_column('users', 'address', "VARCHAR(255) NOT NULL DEFAULT '' AFTER favorite_group");
ensure_column('users', 'last_login_at', 'DATETIME NULL AFTER address');

ensure_column('products', 'group_name', "VARCHAR(120) NOT NULL DEFAULT '' AFTER condition_tags");
ensure_column('products', 'member_name', "VARCHAR(120) NOT NULL DEFAULT '' AFTER group_name");
ensure_column('products', 'album_name', "VARCHAR(120) NOT NULL DEFAULT '' AFTER member_name");
ensure_column('products', 'card_version', "VARCHAR(120) NOT NULL DEFAULT '' AFTER album_name");
ensure_column('products', 'card_code', "VARCHAR(80) NOT NULL DEFAULT '' AFTER card_version");
ensure_column('products', 'sold_at', 'DATETIME NULL AFTER updated_at');

ensure_column('orders', 'paid_at', 'DATETIME NULL AFTER created_at');
ensure_column('orders', 'notification_sent_at', 'DATETIME NULL AFTER paid_at');

$pdo->exec('UPDATE orders SET paid_at = COALESCE(paid_at, created_at) WHERE paid_at IS NULL');
$pdo->exec("UPDATE products SET sold_at = updated_at WHERE status = 'sold_out' AND sold_at IS NULL");

seed_users();
seed_products();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>資料庫升級完成</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="page-shell">
    <section class="hero-card">
        <div>
            <h1>資料庫升級完成</h1>
            <p>會員資料已支援電話、喜歡的團體、地址與最後登入時間；小卡資料已支援團體名稱、成員、專輯版本、卡片代碼與售出時間。</p>
            <div class="badge-row">
                <span class="badge">admin / admin123</span>
                <span class="badge">seller01 / seller123</span>
                <span class="badge">buyer01 / buyer123</span>
            </div>
        </div>
        <div class="glass-card">
            <h2>現在可測的功能</h2>
            <p>1. 註冊與登入 / 記住我</p>
            <p>2. 賣家上架與月報表</p>
            <p>3. 買家購買、評價與通知信</p>
        </div>
    </section>
</main>
</body>
</html>
