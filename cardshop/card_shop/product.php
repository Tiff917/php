<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';

$productId = (int) ($_GET['id'] ?? 0);

$stmt = db()->prepare(
    'SELECT
        p.*,
        u.display_name,
        u.id AS seller_user_id,
        (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS primary_image
     FROM products p
     INNER JOIN users u ON u.id = p.seller_id
     WHERE p.id = :id
     LIMIT 1'
);
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    $pageTitle = '找不到商品 | ' . APP_NAME;
    require_once __DIR__ . '/partials/header.php';
    ?>
    <section class="app-section">
        <h2 class="section-title">找不到這個商品</h2>
        <p class="muted-small">這張小卡可能已經被移除，或是連結已失效。你可以回到商品列表繼續逛。</p>
        <div class="stack-row" style="margin-top: 18px;">
            <a class="button secondary" href="product_list.php">前往商品列表</a>
        </div>
    </section>
    <?php
    require_once __DIR__ . '/partials/footer.php';
    exit;
}

$imagesStmt = db()->prepare(
    'SELECT image_path, is_primary
     FROM product_images
     WHERE product_id = :product_id
     ORDER BY is_primary DESC, id ASC'
);
$imagesStmt->execute(['product_id' => $productId]);
$galleryImages = $imagesStmt->fetchAll();

if ($galleryImages === []) {
    $galleryImages = [[
        'image_path' => $product['primary_image'] ?? null,
        'is_primary' => 1,
    ]];
}

$product = normalize_product_display($product);
$isBuyer = is_logged_in() && (current_user()['role'] ?? '') === 'buyer';
$isAvailable = $product['status'] !== 'sold_out' && (int) $product['stock'] > 0;

$pageTitle = $product['name'] . ' | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="app-section">
    <div class="product-cover product-detail-cover">
        <img src="<?= h(product_primary_image((string) ($galleryImages[0]['image_path'] ?? null))) ?>" alt="<?= h((string) $product['name']) ?>">
    </div>

    <?php if (count($galleryImages) > 1): ?>
        <div class="detail-gallery">
            <?php foreach ($galleryImages as $image): ?>
                <div class="detail-gallery-item">
                    <img src="<?= h(product_primary_image((string) ($image['image_path'] ?? null))) ?>" alt="<?= h((string) $product['name']) ?>">
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="badge-row" style="margin-top: 18px;">
        <span class="badge"><?= h((string) $product['group_name']) ?></span>
        <span class="badge"><?= h(product_status_label((string) $product['status'], (int) $product['stock'])) ?></span>
    </div>
    <h2 class="section-title" style="margin-top: 14px;"><?= h((string) $product['name']) ?></h2>
    <p class="muted-small"><?= h((string) $product['member_name']) ?> / <?= h((string) $product['card_version']) ?></p>
    <div class="price" style="margin-top: 10px;"><?= h(format_currency((float) $product['price'])) ?></div>
</section>

<section class="app-section">
    <div class="simple-list">
        <div class="list-row">
            <span>卡況標籤</span>
            <strong><?= h((string) ($product['condition_tags'] ?: '未填寫')) ?></strong>
        </div>
        <div class="list-row">
            <span>專輯或系列</span>
            <strong><?= h((string) ($product['album_name'] ?: '未填寫')) ?></strong>
        </div>
        <div class="list-row">
            <span>賣家</span>
            <strong><?= h((string) $product['display_name']) ?></strong>
        </div>
        <div class="list-row">
            <span>剩餘庫存</span>
            <strong><?= max(0, (int) $product['stock']) ?> 張</strong>
        </div>
    </div>

    <p class="muted-small" style="margin-top: 16px; line-height: 1.8;"><?= nl2br(h((string) $product['description'])) ?></p>

    <div class="stack-row" style="margin-top: 18px;">
        <a class="button secondary" href="product_list.php">返回商品</a>
        <?php if (!$isAvailable): ?>
            <button type="button" disabled>SOLD OUT</button>
        <?php elseif ($isBuyer): ?>
            <form method="post" action="add_to_cart.php" style="flex: 1;">
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="return_to" value="cart.php">
                <button type="submit">加入購物車</button>
            </form>
        <?php else: ?>
            <a class="button" href="signin.php">登入後購買</a>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
