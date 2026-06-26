<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';

$group = trim((string) ($_GET['group'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$isBuyer = is_logged_in() && (current_user()['role'] ?? '') === 'buyer';

$where = ['1=1'];
$params = [];

if ($group !== '') {
    $where[] = 'p.group_name = :group_name';
    $params['group_name'] = $group;
}

if (in_array($status, ['active', 'sold_out'], true)) {
    $where[] = 'p.status = :status';
    $params['status'] = $status;
}

$sql = 'SELECT
            p.*,
            u.display_name,
            u.id AS seller_user_id,
            (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS primary_image
        FROM products p
        INNER JOIN users u ON u.id = p.seller_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY
            CASE WHEN p.status = "active" AND p.stock > 0 THEN 0 ELSE 1 END,
            p.created_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = array_map('normalize_product_display', $stmt->fetchAll());

$pageTitle = '商品列表 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="app-section">
    <h2 class="section-title">商品列表</h2>
    <form method="get" class="filter-inline">
        <div class="field" style="flex: 1; margin-bottom: 0;">
            <label for="group">團體名稱</label>
            <select id="group" name="group">
                <option value="">全部團體</option>
                <?php foreach (group_name_options() as $item): ?>
                    <option value="<?= h($item) ?>" <?= $group === $item ? 'selected' : '' ?>><?= h($item) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field" style="flex: 1; margin-bottom: 0;">
            <label for="status">商品狀態</label>
            <select id="status" name="status">
                <option value="">全部狀態</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>可購買</option>
                <option value="sold_out" <?= $status === 'sold_out' ? 'selected' : '' ?>>SOLD OUT</option>
            </select>
        </div>
        <button type="submit">套用篩選</button>
    </form>
</section>

<section class="app-section">
    <?php if ($products === []): ?>
        <p class="muted">目前沒有符合條件的商品。</p>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <?php $isAvailable = $product['status'] !== 'sold_out' && (int) $product['stock'] > 0; ?>
                <article class="product-item">
                    <a class="product-link" href="product.php?id=<?= (int) $product['id'] ?>">
                        <div class="product-cover">
                            <?php if (!$isAvailable): ?>
                                <span class="soldout-badge">SOLD OUT</span>
                            <?php endif; ?>
                            <img src="<?= h(product_primary_image($product['primary_image'])) ?>" alt="<?= h((string) $product['name']) ?>">
                        </div>
                        <div class="product-meta">
                            <div class="badge-row">
                                <span class="badge"><?= h((string) $product['group_name']) ?></span>
                            </div>
                            <h3><?= h((string) $product['name']) ?></h3>
                            <p class="muted-small"><?= h((string) $product['member_name']) ?> / <?= h((string) $product['card_version']) ?></p>
                            <div class="price"><?= h(format_currency((float) $product['price'])) ?></div>
                        </div>
                    </a>

                    <div class="stack-row" style="margin-top: 14px;">
                        <a class="button secondary" href="product.php?id=<?= (int) $product['id'] ?>">查看商品</a>
                        <?php if (!$isAvailable): ?>
                            <button type="button" disabled>SOLD OUT</button>
                        <?php elseif ($isBuyer): ?>
                            <form method="post" action="add_to_cart.php" style="flex: 1;">
                                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit">加入購物車</button>
                            </form>
                        <?php else: ?>
                            <a class="button" href="signin.php">登入後購買</a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
