<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';
require_login(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin_dashboard.php');
}

$productId = (int) ($_POST['product_id'] ?? 0);
$targetStatus = (string) ($_POST['target_status'] ?? '');

if ($productId <= 0 || !in_array($targetStatus, ['active', 'sold_out'], true)) {
    set_flash('flash_error', '商品狀態資料不完整，請重新操作。');
    redirect('admin_dashboard.php');
}

$stmt = db()->prepare('SELECT id, stock FROM products WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('flash_error', '找不到要更新的商品。');
    redirect('admin_dashboard.php');
}

$stock = (int) $product['stock'];
$nextStock = $targetStatus === 'sold_out' ? 0 : max(1, $stock);
$soldAt = $targetStatus === 'sold_out' ? date('Y-m-d H:i:s') : null;

$update = db()->prepare(
    'UPDATE products
     SET status = :status,
         stock = :stock,
         sold_at = :sold_at,
         updated_at = NOW()
     WHERE id = :id'
);
$update->execute([
    'status' => $targetStatus,
    'stock' => $nextStock,
    'sold_at' => $soldAt,
    'id' => $productId,
]);

set_flash('flash_success', $targetStatus === 'sold_out' ? '商品已強制下架為 SOLD OUT。' : '商品已重新上架並恢復可購買狀態。');
redirect('admin_dashboard.php');
