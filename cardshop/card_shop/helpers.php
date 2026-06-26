<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function ensure_upload_directories(): void
{
    $paths = [
        UPLOAD_DIR,
        UPLOAD_DIR . '/products',
        REPORT_DIR,
    ];

    foreach ($paths as $path) {
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
    }
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function set_flash(string $key, string $message): void
{
    $_SESSION[$key] = $message;
}

function pop_flash(string $key): ?string
{
    if (!isset($_SESSION[$key])) {
        return null;
    }

    $message = $_SESSION[$key];
    unset($_SESSION[$key]);

    return $message;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function nav_is_active(string $path): string
{
    return basename($_SERVER['PHP_SELF']) === $path ? 'is-active' : '';
}

function require_login(array $roles = []): void
{
    if (!is_logged_in()) {
        set_flash('flash_error', '請先登入後再使用這個功能。');
        redirect('signin.php');
    }

    if ($roles !== [] && !in_array((string) current_user()['role'], $roles, true)) {
        http_response_code(403);
        exit('你沒有權限查看這個頁面。');
    }
}

function login_user(array $user): void
{
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'username' => $user['username'],
        'display_name' => $user['display_name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'favorite_group' => $user['favorite_group'] ?? '',
    ];

    try {
        db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')->execute([
            'id' => (int) $user['id'],
        ]);
    } catch (Throwable) {
        // Keep login usable even if the live schema is behind the latest migration.
    }
}

function cookie_options(int $expires): array
{
    return [
        'expires' => $expires,
        'path' => '/',
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ];
}

function clear_remember_cookie(): void
{
    setcookie(REMEMBER_COOKIE, '', cookie_options(time() - 3600));
}

function issue_remember_token(int $userId): void
{
    $pdo = db();
    $selector = bin2hex(random_bytes(8));
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $expiresAt = date('Y-m-d H:i:s', time() + REMEMBER_DAYS * 86400);

    $pdo->prepare('DELETE FROM remember_tokens WHERE user_id = :user_id')->execute([
        'user_id' => $userId,
    ]);

    $pdo->prepare(
        'INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at, created_at, last_used_at)
         VALUES (:user_id, :selector, :token_hash, :expires_at, NOW(), NOW())'
    )->execute([
        'user_id' => $userId,
        'selector' => $selector,
        'token_hash' => $tokenHash,
        'expires_at' => $expiresAt,
    ]);

    setcookie(
        REMEMBER_COOKIE,
        $selector . ':' . $token,
        cookie_options(time() + REMEMBER_DAYS * 86400)
    );
}

function logout_user(): void
{
    if (is_logged_in()) {
        db()->prepare('DELETE FROM remember_tokens WHERE user_id = :user_id')->execute([
            'user_id' => current_user()['id'],
        ]);
    }

    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    clear_remember_cookie();
}

function fetch_user_by_id(int $userId): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function all_condition_tags(): array
{
    return ['全新', '近全新', '官方', '裸卡', '微瑕', '已拆封'];
}

function preferred_groups(): array
{
    return [
        'BTS',
        'SEVENTEEN',
        'IVE',
        'NewJeans',
        'aespa',
        'Stray Kids',
        'ATEEZ',
        'LE SSERAFIM',
        'TXT',
        'NCT',
    ];
}

function group_name_options(): array
{
    return preferred_groups();
}

function role_label(string $role): string
{
    return match ($role) {
        'admin' => '管理員',
        'seller' => '賣家',
        default => '買家',
    };
}

function profile_completion(array $user): int
{
    $score = 20;
    foreach (['display_name', 'email', 'username', 'phone', 'address', 'favorite_group'] as $field) {
        if (!empty($user[$field])) {
            $score += 13;
        }
    }

    return min($score, 100);
}

function product_primary_image(?string $path): string
{
    if ($path && is_file(__DIR__ . '/' . $path)) {
        return $path;
    }

    return 'assets/placeholder-card.svg';
}

function stars_label(float $rating): string
{
    return number_format($rating, 1) . ' / 5';
}

function format_currency(float $amount): string
{
    return 'NT$ ' . number_format($amount, 0);
}

function product_status_label(string $status, int $stock): string
{
    return ($status === 'sold_out' || $stock <= 0) ? 'SOLD OUT' : '可購買';
}

function month_options(int $months = 6): array
{
    $options = [];
    $cursor = new DateTimeImmutable('first day of this month');

    for ($i = 0; $i < $months; $i++) {
        $month = $cursor->modify("-{$i} month");
        $options[$month->format('Y-m')] = $month->format('Y 年 m 月');
    }

    return $options;
}

function monthly_sales_summary(int $sellerId, string $month): array
{
    $stmt = db()->prepare(
        'SELECT
            COUNT(DISTINCT o.product_id) AS total_orders,
            COUNT(*) AS total_transactions,
            COALESCE(SUM(o.quantity), 0) AS total_cards,
            COALESCE(SUM(o.total_amount), 0) AS total_revenue
         FROM orders o
         WHERE o.seller_id = :seller_id
           AND DATE_FORMAT(o.created_at, "%Y-%m") = :month'
    );
    $stmt->execute([
        'seller_id' => $sellerId,
        'month' => $month,
    ]);

    return $stmt->fetch() ?: [
        'total_orders' => 0,
        'total_transactions' => 0,
        'total_cards' => 0,
        'total_revenue' => 0,
    ];
}

function seller_monthly_group_revenue(int $sellerId, string $month): array
{
    $stmt = db()->prepare(
        'SELECT
            p.group_name,
            COUNT(DISTINCT o.product_id) AS order_count,
            COUNT(*) AS transaction_count,
            COALESCE(SUM(o.quantity), 0) AS total_cards,
            COALESCE(SUM(o.total_amount), 0) AS total_revenue
         FROM orders o
         INNER JOIN products p ON p.id = o.product_id
         WHERE o.seller_id = :seller_id
           AND DATE_FORMAT(o.created_at, "%Y-%m") = :month
         GROUP BY p.group_name
         ORDER BY total_revenue DESC, p.group_name ASC'
    );
    $stmt->execute([
        'seller_id' => $sellerId,
        'month' => $month,
    ]);

    return $stmt->fetchAll();
}

function seller_monthly_daily_orders(int $sellerId, string $month): array
{
    $stmt = db()->prepare(
        'SELECT
            DATE(o.created_at) AS order_date,
            COUNT(*) AS order_count,
            COALESCE(SUM(o.total_amount), 0) AS total_revenue
         FROM orders o
         WHERE o.seller_id = :seller_id
           AND DATE_FORMAT(o.created_at, "%Y-%m") = :month
         GROUP BY DATE(o.created_at)
         ORDER BY order_date ASC'
    );
    $stmt->execute([
        'seller_id' => $sellerId,
        'month' => $month,
    ]);

    return $stmt->fetchAll();
}

function seller_monthly_orders(int $sellerId, string $month): array
{
    $stmt = db()->prepare(
        'SELECT
            o.*,
            p.name AS product_name,
            p.group_name,
            p.member_name,
            p.card_version,
            u.display_name AS buyer_name
         FROM orders o
         INNER JOIN products p ON p.id = o.product_id
         INNER JOIN users u ON u.id = o.buyer_id
         WHERE o.seller_id = :seller_id
           AND DATE_FORMAT(o.created_at, "%Y-%m") = :month
         ORDER BY o.created_at DESC'
    );
    $stmt->execute([
        'seller_id' => $sellerId,
        'month' => $month,
    ]);

    return $stmt->fetchAll();
}

function font_candidates(): array
{
    return [
        __DIR__ . '/assets/fonts/kaiu.ttf',
        __DIR__ . '/assets/fonts/msjh.ttc',
        'C:/Windows/Fonts/NotoSansTC-VF.ttf',
        'C:/Windows/Fonts/NotoSansHK-VF.ttf',
        'C:/Windows/Fonts/kaiu.ttf',
        'C:/Windows/Fonts/STXIHEI.TTF',
        'C:/Windows/Fonts/STKAITI.TTF',
        'C:/Windows/Fonts/arial.ttf',
    ];
}

function first_existing_font(): ?string
{
    foreach (font_candidates() as $font) {
        if (is_file($font)) {
            return $font;
        }
    }

    return null;
}

function normalize_product_display(array $product): array
{
    return $product;
}

function cart_items(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    $count = 0;
    foreach (cart_items() as $item) {
        $count += max(0, (int) ($item['quantity'] ?? 0));
    }

    return $count;
}

function save_cart(array $items): void
{
    $_SESSION['cart'] = $items;
}

function add_cart_item(array $product, int $quantity = 1): void
{
    $quantity = max(1, $quantity);
    $items = cart_items();
    $key = (string) $product['id'];

    if (isset($items[$key])) {
        $items[$key]['quantity'] += $quantity;
    } else {
        $items[$key] = [
            'product_id' => (int) $product['id'],
            'quantity' => $quantity,
        ];
    }

    save_cart($items);
}

function remove_cart_item(int $productId): void
{
    $items = cart_items();
    unset($items[(string) $productId]);
    save_cart($items);
}

function clear_cart(): void
{
    unset($_SESSION['cart']);
}

function fetch_cart_products(): array
{
    $items = cart_items();
    if ($items === []) {
        return [];
    }

    $productIds = array_map(
        static fn(array $item): int => (int) ($item['product_id'] ?? 0),
        array_values($items)
    );
    $productIds = array_values(array_filter($productIds));

    if ($productIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = db()->prepare(
        'SELECT
            p.*,
            u.display_name,
            u.id AS seller_user_id,
            (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS primary_image
         FROM products p
         INNER JOIN users u ON u.id = p.seller_id
         WHERE p.id IN (' . $placeholders . ')'
    );
    $stmt->execute($productIds);

    $productsById = [];
    foreach ($stmt->fetchAll() as $product) {
        $productsById[(int) $product['id']] = $product;
    }

    $cartProducts = [];
    foreach ($items as $item) {
        $productId = (int) ($item['product_id'] ?? 0);
        if (!isset($productsById[$productId])) {
            continue;
        }

        $product = $productsById[$productId];
        $product['quantity'] = max(1, (int) ($item['quantity'] ?? 1));
        $product['line_total'] = $product['quantity'] * (float) $product['price'];
        $cartProducts[] = normalize_product_display($product);
    }

    return $cartProducts;
}

ensure_upload_directories();
