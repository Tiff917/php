<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';
require_login(['admin']);

$stats = [
    'users' => (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'sellers' => (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'seller'")->fetchColumn(),
    'buyers' => (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'buyer'")->fetchColumn(),
    'products' => (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'active_products' => (int) db()->query("SELECT COUNT(*) FROM products WHERE status = 'active' AND stock > 0")->fetchColumn(),
    'sold_out' => (int) db()->query("SELECT COUNT(*) FROM products WHERE status = 'sold_out' OR stock <= 0")->fetchColumn(),
    'orders' => (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'revenue' => (float) db()->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders")->fetchColumn(),
];

$orders = db()->query(
    'SELECT
        o.id,
        o.quantity,
        o.total_amount,
        o.status,
        o.created_at,
        p.name AS product_name,
        p.group_name,
        p.member_name,
        b.display_name AS buyer_name,
        s.display_name AS seller_name
     FROM orders o
     INNER JOIN products p ON p.id = o.product_id
     INNER JOIN users b ON b.id = o.buyer_id
     INNER JOIN users s ON s.id = o.seller_id
     ORDER BY o.created_at DESC'
)->fetchAll();

$products = db()->query(
    'SELECT
        p.id,
        p.name,
        p.group_name,
        p.member_name,
        p.price,
        p.stock,
        p.status,
        p.created_at,
        u.display_name AS seller_name
     FROM products p
     INNER JOIN users u ON u.id = p.seller_id
     ORDER BY p.created_at DESC'
)->fetchAll();

$users = db()->query(
    'SELECT id, username, display_name, email, role, created_at
     FROM users
     ORDER BY created_at DESC'
)->fetchAll();

$pageTitle = '管理後台 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="app-section">
    <h2 class="section-title">管理後台</h2>
    <div class="summary-grid">
        <article class="summary-card">
            <span class="metric-label">會員總數</span>
            <strong class="metric-value"><?= $stats['users'] ?></strong>
            <p class="muted-small">賣家 <?= $stats['sellers'] ?> / 買家 <?= $stats['buyers'] ?></p>
        </article>
        <article class="summary-card">
            <span class="metric-label">商品總數</span>
            <strong class="metric-value"><?= $stats['products'] ?></strong>
            <p class="muted-small">上架中 <?= $stats['active_products'] ?> / 已售完 <?= $stats['sold_out'] ?></p>
        </article>
        <article class="summary-card">
            <span class="metric-label">訂單總數</span>
            <strong class="metric-value"><?= $stats['orders'] ?></strong>
            <p class="muted-small">全站累積交易筆數</p>
        </article>
        <article class="summary-card">
            <span class="metric-label">累積營收</span>
            <strong class="metric-value"><?= h(format_currency($stats['revenue'])) ?></strong>
            <p class="muted-small">依所有已成立訂單統計</p>
        </article>
    </div>
</section>

<section class="app-section">
    <div class="section-head">
        <h3 class="section-title">全部訂單</h3>
        <span class="muted-small"><?= count($orders) ?> 筆</span>
    </div>
    <?php if ($orders === []): ?>
        <p class="muted">目前沒有訂單資料。</p>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>訂單編號</th>
                        <th>商品</th>
                        <th>買家</th>
                        <th>賣家</th>
                        <th>數量</th>
                        <th>金額</th>
                        <th>建立時間</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= (int) $order['id'] ?></td>
                            <td>
                                <strong><?= h((string) $order['product_name']) ?></strong>
                                <div class="muted-small"><?= h((string) $order['group_name']) ?> / <?= h((string) $order['member_name']) ?></div>
                            </td>
                            <td><?= h((string) $order['buyer_name']) ?></td>
                            <td><?= h((string) $order['seller_name']) ?></td>
                            <td><?= (int) $order['quantity'] ?></td>
                            <td><?= h(format_currency((float) $order['total_amount'])) ?></td>
                            <td><?= h((string) $order['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="app-section">
    <div class="section-head">
        <h3 class="section-title">商品管理</h3>
        <span class="muted-small">可強制上架或下架商品</span>
    </div>
    <?php if ($products === []): ?>
        <p class="muted">目前沒有商品資料。</p>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>商品</th>
                        <th>賣家</th>
                        <th>價格</th>
                        <th>庫存</th>
                        <th>狀態</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <strong><?= h((string) $product['name']) ?></strong>
                                <div class="muted-small"><?= h((string) $product['group_name']) ?> / <?= h((string) $product['member_name']) ?></div>
                            </td>
                            <td><?= h((string) $product['seller_name']) ?></td>
                            <td><?= h(format_currency((float) $product['price'])) ?></td>
                            <td><?= (int) $product['stock'] ?></td>
                            <td><?= h(product_status_label((string) $product['status'], (int) $product['stock'])) ?></td>
                            <td>
                                <div class="admin-actions">
                                    <form method="post" action="admin_update_product_status.php">
                                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                        <input type="hidden" name="target_status" value="active">
                                        <button type="submit" class="button secondary compact-button">強制上架</button>
                                    </form>
                                    <form method="post" action="admin_update_product_status.php">
                                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                        <input type="hidden" name="target_status" value="sold_out">
                                        <button type="submit" class="button secondary compact-button">強制下架</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="app-section">
    <div class="section-head">
        <h3 class="section-title">會員列表</h3>
        <span class="muted-small"><?= count($users) ?> 位</span>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>帳號</th>
                    <th>名稱</th>
                    <th>Email</th>
                    <th>角色</th>
                    <th>建立時間</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>@<?= h((string) $user['username']) ?></td>
                        <td><?= h((string) $user['display_name']) ?></td>
                        <td><?= h((string) $user['email']) ?></td>
                        <td><?= h(role_label((string) $user['role'])) ?></td>
                        <td><?= h((string) $user['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
