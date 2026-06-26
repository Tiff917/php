<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';

require_login(['buyer']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('cart.php');
}

$productId = (int) ($_POST['product_id'] ?? 0);
if ($productId > 0) {
    remove_cart_item($productId);
    set_flash('flash_success', '已從購物車移除。');
}

redirect('cart.php');
