<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';

require_login(['buyer']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('product_list.php');
}

$productId = (int) ($_POST['product_id'] ?? 0);
$quantity = max(1, (int) ($_POST['quantity'] ?? 1));
$returnTo = trim((string) ($_POST['return_to'] ?? 'cart.php'));

$stmt = db()->prepare(
    'SELECT p.*
     FROM products p
     WHERE p.id = :id
     LIMIT 1'
);
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product || $product['status'] === 'sold_out' || (int) $product['stock'] < $quantity) {
    set_flash('flash_error', '這張小卡目前無法加入購物車。');
    redirect('product_list.php');
}

add_cart_item($product, $quantity);
set_flash('flash_success', '已加入購物車。');

if ($returnTo === 'cart.php') {
    redirect('cart.php');
}

redirect($returnTo);
