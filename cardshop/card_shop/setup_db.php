<?php
/**
 * setup_db.php — 一次性資料庫初始化腳本
 * 建立所有資料表 + 插入測試帳號
 * 執行完後請刪除本檔！
 */
$host = "localhost";
$user = "root";
$pass = "";

// 先不指定 DB，建立資料庫
$conn = new mysqli($host, $user, $pass);
$conn->set_charset("utf8mb4");
if ($conn->connect_error) die("連線失敗：" . $conn->connect_error);

$ok = []; $err = [];

// 1. 建立資料庫
$r = $conn->query("CREATE DATABASE IF NOT EXISTS card_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$ok[] = $r ? "✅ 資料庫 card_shop 建立/確認成功" : "❌ DB: " . $conn->error;

$conn->select_db("card_shop");

// 2. users
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(50) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,
    real_name       VARCHAR(100) DEFAULT '',
    email           VARCHAR(100) DEFAULT '',
    birthday        DATE DEFAULT NULL,
    role            ENUM('buyer','seller','admin') DEFAULT 'buyer',
    favorite_groups TEXT DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$ok[] = "✅ users table";

// 3. products
$conn->query("CREATE TABLE IF NOT EXISTS products (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    seller_id       INT NOT NULL,
    group_name      VARCHAR(50) NOT NULL,
    condition_tags  VARCHAR(255) DEFAULT '',
    name            VARCHAR(100) NOT NULL,
    price           INT NOT NULL,
    image_path      VARCHAR(255) NOT NULL,
    status          ENUM('available','sold_out') DEFAULT 'available',
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$ok[] = "✅ products table";

// 4. product_images (多圖)
$conn->query("CREATE TABLE IF NOT EXISTS product_images (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    product_id      INT NOT NULL,
    image_path      VARCHAR(255) NOT NULL,
    is_primary      TINYINT(1) DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$ok[] = "✅ product_images table";

// 5. orders
$conn->query("CREATE TABLE IF NOT EXISTS orders (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id        INT NOT NULL,
    total_price     INT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$ok[] = "✅ orders table";

// 6. order_items
$conn->query("CREATE TABLE IF NOT EXISTS order_items (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT NOT NULL,
    product_id      INT NOT NULL,
    product_name    VARCHAR(100) NOT NULL,
    price           INT NOT NULL,
    quantity        INT NOT NULL DEFAULT 1,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$ok[] = "✅ order_items table";

// 7. remember_tokens (Cookie 記住我)
$conn->query("CREATE TABLE IF NOT EXISTS remember_tokens (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    token           VARCHAR(64) NOT NULL UNIQUE,
    expires_at      DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$ok[] = "✅ remember_tokens table";

// 8. reviews (評價)
$conn->query("CREATE TABLE IF NOT EXISTS reviews (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT NOT NULL,
    rater_id        INT NOT NULL,
    ratee_id        INT NOT NULL,
    rating_stars    TINYINT(1) NOT NULL DEFAULT 5,
    comment         TEXT DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id)  REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (rater_id)  REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ratee_id)  REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$ok[] = "✅ reviews table";

// ── 插入預設帳號 ─────────────────────────────────────────────────
$accounts = [
    ['admin',  password_hash('admin123', PASSWORD_DEFAULT), '', '', 'admin'],
    ['seller1', password_hash('seller1',  PASSWORD_DEFAULT), '賣家小美', 'seller1@example.com', 'seller'],
    ['buyer1',  password_hash('buyer1',   PASSWORD_DEFAULT), '買家小明', 'buyer1@example.com', 'buyer'],
];

$ins = $conn->prepare("INSERT IGNORE INTO users (username, password, real_name, email, role) VALUES (?, ?, ?, ?, ?)");
foreach ($accounts as $a) {
    $ins->bind_param("sssss", $a[0], $a[1], $a[2], $a[3], $a[4]);
    $ins->execute();
}
$ok[] = "✅ 預設帳號插入完成（admin/admin123, seller1/seller1, buyer1/buyer1）";

foreach ($ok  as $m) echo $m . "<br>";
foreach ($err as $m) echo $m . "<br>";

echo "<br><hr><h3>🎉 資料庫初始化完成！</h3>";
echo "<strong>請立刻刪除此檔案（setup_db.php）！</strong><br><br>";
echo "<a href='index.php'>→ 前往網站首頁</a>";
?>
