<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';

require_login(['buyer']);

$latestOrderIds = array_map('intval', $_SESSION['latest_order_ids'] ?? []);
unset($_SESSION['latest_order_ids']);

$stmt = db()->prepare(
    'SELECT
        o.*,
        p.name AS product_name,
        p.group_name,
        p.member_name,
        p.card_version,
        u.display_name AS seller_name,
        (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS primary_image
     FROM orders o
     INNER JOIN products p ON p.id = o.product_id
     INNER JOIN users u ON u.id = o.seller_id
     WHERE o.buyer_id = :buyer_id
     ORDER BY o.created_at DESC, o.id DESC'
);
$stmt->execute([
    'buyer_id' => (int) current_user()['id'],
]);
$orders = $stmt->fetchAll();

$pageTitle = '我的訂單 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="app-section">
    <div class="section-head">
        <h2 class="section-title">我的訂單</h2>
        <?php if ($latestOrderIds !== []): ?>
            <span class="muted-small">已完成最新訂單</span>
        <?php endif; ?>
    </div>

    <?php if ($orders === []): ?>
        <p class="muted-small">目前還沒有訂單紀錄。</p>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($orders as $order): ?>
                <?php $isLatest = in_array((int) $order['id'], $latestOrderIds, true); ?>
                <article class="product-item">
                    <div class="product-link">
                        <div class="product-cover">
                            <img src="<?= h(product_primary_image($order['primary_image'])) ?>" alt="<?= h((string) $order['product_name']) ?>">
                            <?php if ($isLatest): ?>
                                <span class="soldout-badge" style="background: #665843;">最新訂單</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-meta">
                            <div class="badge-row">
                                <span class="badge"><?= h((string) $order['group_name']) ?></span>
                                <span class="badge">訂單 #<?= (int) $order['id'] ?></span>
                            </div>
                            <h3><?= h((string) $order['product_name']) ?></h3>
                            <p class="muted-small"><?= h((string) $order['member_name']) ?> / <?= h((string) $order['card_version']) ?></p>
                            <p class="muted-small">賣家：<?= h((string) $order['seller_name']) ?></p>
                            <p class="muted-small">下單時間：<?= h((string) $order['created_at']) ?></p>
                            <p class="muted-small">購買數量：<?= (int) $order['quantity'] ?></p>
                            <div class="price"><?= h(format_currency((float) $order['total_amount'])) ?></div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="app-section">
    <div class="action-grid">
        <a class="button secondary action-chip" href="product_list.php">繼續購物</a>
        <a class="button secondary action-chip" href="member_center.php">返回會員</a>
    </div>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
