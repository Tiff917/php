<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';
require_login(['seller']);

$productsStmt = db()->prepare(
    'SELECT
        p.*,
        (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS primary_image
     FROM products p
     WHERE p.seller_id = :seller_id
     ORDER BY p.created_at DESC'
);
$productsStmt->execute(['seller_id' => current_user()['id']]);
$products = array_map('normalize_product_display', $productsStmt->fetchAll());

$ordersStmt = db()->prepare(
    'SELECT
        o.*,
        p.name AS product_name,
        p.group_name,
        u.display_name AS buyer_name
     FROM orders o
     INNER JOIN products p ON p.id = o.product_id
     INNER JOIN users u ON u.id = o.buyer_id
     WHERE o.seller_id = :seller_id
     ORDER BY o.created_at DESC
     LIMIT 5'
);
$ordersStmt->execute(['seller_id' => current_user()['id']]);
$recentOrders = $ordersStmt->fetchAll();

$currentMonth = date('Y-m');
$summary = monthly_sales_summary((int) current_user()['id'], $currentMonth);

$activeCount = 0;
$soldOutCount = 0;
$lowStockCount = 0;

foreach ($products as $product) {
    $stock = (int) $product['stock'];
    $status = (string) $product['status'];

    if ($status === 'sold_out' || $stock <= 0) {
        $soldOutCount++;
        continue;
    }

    $activeCount++;
    if ($stock <= 1) {
        $lowStockCount++;
    }
}

$pageTitle = '賣家頁 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="app-section">
    <h2 class="section-title">賣家頁</h2>
    <div class="summary-grid">
        <article class="summary-card">
            <span class="metric-label">上架中商品</span>
            <strong class="metric-value"><?= $activeCount ?></strong>
        </article>
        <article class="summary-card">
            <span class="metric-label">已售完商品</span>
            <strong class="metric-value"><?= $soldOutCount ?></strong>
        </article>
        <article class="summary-card">
            <span class="metric-label">低庫存商品</span>
            <strong class="metric-value"><?= $lowStockCount ?></strong>
        </article>
        <article class="summary-card">
            <span class="metric-label">本月營收</span>
            <strong class="metric-value"><?= h(format_currency((float) $summary['total_revenue'])) ?></strong>
        </article>
    </div>
</section>

<section class="app-section" id="upload-form">
    <div class="section-head">
        <h2 class="section-title">新增商品</h2>
    </div>

    <form method="post" action="upload_product.php" enctype="multipart/form-data">
        <div class="field two-col">
            <div>
                <label for="group_name">團體名稱</label>
                <select id="group_name" name="group_name" required>
                    <option value="">請選擇團體</option>
                    <?php foreach (group_name_options() as $group): ?>
                        <option value="<?= h($group) ?>"><?= h($group) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="member_name">成員名稱</label>
                <input id="member_name" name="member_name" placeholder="例如 Kai" required>
            </div>
        </div>

        <div class="field two-col">
            <div>
                <label for="album_name">專輯或活動</label>
                <input id="album_name" name="album_name" placeholder="例如 minisode">
            </div>
            <div>
                <label for="card_version">版本</label>
                <input id="card_version" name="card_version" placeholder="例如 Lucky Draw">
            </div>
        </div>

        <div class="field two-col">
            <div>
                <label for="card_code">商品編號</label>
                <input id="card_code" name="card_code" placeholder="例如 TXT-KAI-001">
            </div>
            <div>
                <label for="name">商品名稱</label>
                <input id="name" name="name" placeholder="例如 TXT Kai 小卡" required>
            </div>
        </div>

        <div class="field">
            <label for="description">商品描述</label>
            <textarea id="description" name="description" required></textarea>
        </div>

        <div class="field two-col">
            <div>
                <label for="price">價格</label>
                <input id="price" type="number" name="price" min="1" required>
            </div>
            <div>
                <label for="stock">庫存</label>
                <input id="stock" type="number" name="stock" min="0" required>
            </div>
        </div>

        <div class="field">
            <label for="condition_tags">卡況標籤</label>
            <input id="condition_tags" name="condition_tags" placeholder="例如 全新, 官方, 微瑕">
        </div>

        <div class="field">
            <label for="images">商品圖片</label>
            <input id="images" type="file" name="images[]" multiple accept="image/jpeg,image/png" required>
        </div>

        <div id="preview" class="preview-grid"></div>
        <button type="submit">送出上架</button>
    </form>
</section>

<section class="app-section">
    <div class="section-head">
        <h2 class="section-title">最近訂單</h2>
    </div>
    <?php if ($recentOrders === []): ?>
        <p class="muted">目前還沒有新的訂單。</p>
    <?php else: ?>
        <div class="simple-list">
            <?php foreach ($recentOrders as $order): ?>
                <article class="list-row">
                    <div>
                        <strong><?= h((string) $order['product_name']) ?></strong>
                        <p class="muted-small"><?= h((string) $order['group_name']) ?> / 買家：<?= h((string) $order['buyer_name']) ?></p>
                    </div>
                    <div class="list-row-meta">
                        <strong><?= h(format_currency((float) $order['total_amount'])) ?></strong>
                        <span class="muted-small"><?= h((string) $order['created_at']) ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="app-section">
    <div class="section-head">
        <h2 class="section-title">我的商品</h2>
    </div>

    <?php if ($products === []): ?>
        <p class="muted">目前還沒有商品。</p>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <?php $isSoldOut = (string) $product['status'] === 'sold_out' || (int) $product['stock'] <= 0; ?>
                <article class="product-item">
                    <a class="product-link" href="product.php?id=<?= (int) $product['id'] ?>">
                        <div class="product-cover">
                            <?php if ($isSoldOut): ?>
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
                            <p class="muted-small"><?= h(format_currency((float) $product['price'])) ?> / 庫存 <?= (int) $product['stock'] ?></p>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script>
const input = document.getElementById('images');
const preview = document.getElementById('preview');

if (input && preview) {
    input.addEventListener('change', () => {
        preview.innerHTML = '';
        Array.from(input.files).slice(0, 5).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (event) => {
                const box = document.createElement('div');
                box.className = 'preview-item';

                const img = document.createElement('img');
                img.src = event.target.result;
                img.alt = file.name;
                box.appendChild(img);

                const badge = document.createElement('span');
                badge.className = 'badge';
                badge.textContent = index === 0 ? '主圖' : `圖片 ${index + 1}`;
                box.appendChild(badge);

                preview.appendChild(box);
            };
            reader.readAsDataURL(file);
        });
    });
}
</script>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
