<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';

$sellerId = (int) ($_GET['seller_id'] ?? 0);
if ($sellerId <= 0) {
    set_flash('flash_error', '找不到賣家資料。');
    redirect('product_list.php');
}

$sellerStmt = db()->prepare(
    "SELECT
        u.*,
        COUNT(DISTINCT p.id) AS total_products,
        COUNT(DISTINCT r.id) AS total_reviews,
        AVG(r.rating) AS avg_rating
     FROM users u
     LEFT JOIN products p ON p.seller_id = u.id
     LEFT JOIN reviews r ON r.seller_id = u.id
     WHERE u.id = :id AND u.role IN ('seller', 'admin')
     GROUP BY u.id
     LIMIT 1"
);
$sellerStmt->execute(['id' => $sellerId]);
$seller = $sellerStmt->fetch();

if (!$seller) {
    set_flash('flash_error', '找不到賣家資料。');
    redirect('product_list.php');
}

$productsStmt = db()->prepare(
    'SELECT
        p.*,
        (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS primary_image
     FROM products p
     WHERE p.seller_id = :seller_id
     ORDER BY p.created_at DESC'
);
$productsStmt->execute(['seller_id' => $sellerId]);
$products = $productsStmt->fetchAll();

$reviewsStmt = db()->prepare(
    'SELECT r.*, u.display_name AS buyer_name
     FROM reviews r
     INNER JOIN users u ON u.id = r.buyer_id
     WHERE r.seller_id = :seller_id
     ORDER BY r.created_at DESC'
);
$reviewsStmt->execute(['seller_id' => $sellerId]);
$reviews = $reviewsStmt->fetchAll();

$avgRating = (float) ($seller['avg_rating'] ?? 0);

$pageTitle = '賣家頁面 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="app-section">
    <h2 class="section-title"><?= h((string) $seller['display_name']) ?></h2>
    <p class="muted-small">平均評分 <?= h(stars_label($avgRating)) ?>，累積 <?= (int) $seller['total_reviews'] ?> 則評價，已上架 <?= (int) $seller['total_products'] ?> 件商品。</p>
</section>

<section class="app-section">
    <div class="badge-row">
        <?php if (($seller['favorite_group'] ?? '') !== ''): ?>
            <span class="badge">常上架團體：<?= h((string) $seller['favorite_group']) ?></span>
        <?php endif; ?>
        <?php if (($seller['phone'] ?? '') !== ''): ?>
            <span class="badge">聯絡電話：<?= h((string) $seller['phone']) ?></span>
        <?php endif; ?>
    </div>
</section>

<section class="app-section">
    <div class="section-head">
        <h2 class="section-title">賣家商品</h2>
    </div>
    <?php if ($products === []): ?>
        <p class="muted">目前還沒有上架商品。</p>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <article class="product-item">
                    <a class="product-link" href="product.php?id=<?= (int) $product['id'] ?>">
                        <div class="product-cover">
                            <img src="<?= h(product_primary_image((string) $product['primary_image'])) ?>" alt="<?= h((string) $product['name']) ?>">
                        </div>
                        <div class="product-meta">
                            <div class="badge-row">
                                <span class="badge"><?= h(product_status_label((string) $product['status'], (int) $product['stock'])) ?></span>
                                <span class="badge"><?= h((string) $product['group_name']) ?></span>
                            </div>
                            <h3><?= h((string) $product['name']) ?></h3>
                            <p class="muted-small"><?= h((string) $product['member_name']) ?> / <?= h((string) $product['album_name']) ?> / <?= h((string) $product['card_version']) ?></p>
                            <p class="muted-small"><?= h(format_currency((float) $product['price'])) ?> / 庫存 <?= (int) $product['stock'] ?> 張</p>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="app-section">
    <div class="section-head">
        <h2 class="section-title">買家評價</h2>
    </div>
    <?php if ($reviews === []): ?>
        <p class="muted">目前還沒有評價，之後完成交易後會顯示在這裡。</p>
    <?php else: ?>
        <div class="simple-list">
            <?php foreach ($reviews as $review): ?>
                <article class="list-row">
                    <div>
                        <strong><?= h(str_repeat('★', (int) $review['rating'])) ?></strong>
                        <p><?= nl2br(h((string) $review['comment'])) ?></p>
                        <p class="muted-small">來自買家：<?= h((string) $review['buyer_name']) ?></p>
                    </div>
                    <div class="list-row-meta">
                        <span class="muted-small"><?= h((string) $review['created_at']) ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
