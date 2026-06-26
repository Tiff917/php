<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';

require_login(['buyer']);

$cartProducts = fetch_cart_products();
$total = 0.0;
foreach ($cartProducts as $item) {
    $total += (float) $item['line_total'];
}

$pageTitle = '購物車 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="app-section">
    <h2 class="section-title">購物車</h2>
    <?php if ($cartProducts === []): ?>
        <p class="muted-small">你的購物車目前是空的，可以先去挑選喜歡的小卡。</p>
        <div class="stack-row" style="margin-top: 18px;">
            <a class="button secondary" href="product_list.php">返回商品</a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($cartProducts as $item): ?>
                <article class="product-item">
                    <div class="product-link">
                        <div class="product-cover">
                            <img src="<?= h(product_primary_image($item['primary_image'])) ?>" alt="<?= h((string) $item['name']) ?>">
                        </div>
                        <div class="product-meta">
                            <div class="badge-row">
                                <span class="badge"><?= h((string) $item['group_name']) ?></span>
                                <span class="badge"><?= h(product_status_label((string) $item['status'], (int) $item['stock'])) ?></span>
                            </div>
                            <h3><?= h((string) $item['name']) ?></h3>
                            <p class="muted-small"><?= h((string) $item['member_name']) ?> / <?= h((string) $item['card_version']) ?></p>
                            <p class="muted-small">數量 x<?= (int) $item['quantity'] ?></p>
                            <div class="price"><?= h(format_currency((float) $item['line_total'])) ?></div>
                            <form method="post" action="remove_from_cart.php" style="margin-top: 12px;">
                                <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                                <button type="submit" class="button secondary">刪除商品</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="summary-grid" style="margin-top: 18px;">
            <article class="summary-card">
                <span class="metric-label">商品數量</span>
                <strong class="metric-value"><?= count($cartProducts) ?></strong>
            </article>
            <article class="summary-card">
                <span class="metric-label">結帳總額</span>
                <strong class="metric-value"><?= h(format_currency($total)) ?></strong>
            </article>
        </div>

        <div class="action-grid" style="margin-top: 18px;">
            <a class="button secondary action-chip" href="product_list.php">繼續購物</a>
            <form method="post" action="checkout.php" style="margin: 0;">
                <input type="hidden" name="checkout_mode" value="cart">
                <button type="submit" class="action-chip">前往結帳</button>
            </form>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
