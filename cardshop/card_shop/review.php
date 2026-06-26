<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';
require_login(['buyer']);

$orderId = (int) ($_GET['order_id'] ?? 0);
$stmt = db()->prepare(
    'SELECT o.*, p.name AS product_name, p.group_name, p.member_name, u.display_name AS seller_name
     FROM orders o
     INNER JOIN products p ON p.id = o.product_id
     INNER JOIN users u ON u.id = o.seller_id
     WHERE o.id = :id AND o.buyer_id = :buyer_id
     LIMIT 1'
);
$stmt->execute([
    'id' => $orderId,
    'buyer_id' => current_user()['id'],
]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('flash_error', '找不到這筆可評價的訂單。');
    redirect('member_center.php');
}

$pageTitle = '留下評價 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="app-section">
    <h2 class="section-title">留下評價</h2>
    <p class="muted-small">你的評價會顯示在賣家頁面，幫助之後的買家更安心下單。</p>
</section>

<section class="app-section">
    <div class="simple-list">
        <div class="list-row">
            <span>商品</span>
            <strong><?= h((string) $order['product_name']) ?></strong>
        </div>
        <div class="list-row">
            <span>團體 / 成員</span>
            <strong><?= h((string) $order['group_name']) ?> / <?= h((string) $order['member_name']) ?></strong>
        </div>
        <div class="list-row">
            <span>賣家</span>
            <strong><?= h((string) $order['seller_name']) ?></strong>
        </div>
    </div>
</section>

<section class="app-section">
    <form method="post" action="add_review.php">
        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">

        <div class="field">
            <label for="rating">星級評分</label>
            <select id="rating" name="rating" required>
                <option value="">請選擇分數</option>
                <option value="5">5 星</option>
                <option value="4">4 星</option>
                <option value="3">3 星</option>
                <option value="2">2 星</option>
                <option value="1">1 星</option>
            </select>
        </div>

        <div class="field">
            <label for="comment">評價內容</label>
            <textarea id="comment" name="comment" maxlength="500" required placeholder="例如：出貨快速、卡況和描述一致、包裝完整。"></textarea>
        </div>

        <div class="action-grid">
            <a class="button secondary action-chip" href="buyer_orders.php">回購買紀錄</a>
            <button type="submit" class="action-chip">送出評價</button>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
