<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';
require_once __DIR__ . '/mail_helpers.php';

require_login(['buyer']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('cart.php');
}

$checkoutMode = (string) ($_POST['checkout_mode'] ?? '');
$cartProducts = [];

if ($checkoutMode === 'cart') {
    $cartProducts = fetch_cart_products();
    if ($cartProducts === []) {
        set_flash('flash_error', '購物車是空的，請先加入商品。');
        redirect('cart.php');
    }
} else {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

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
        set_flash('flash_error', '找不到這張商品。');
        redirect('product_list.php');
    }

    $product['quantity'] = $quantity;
    $product['line_total'] = $quantity * (float) $product['price'];
    $cartProducts = [normalize_product_display($product)];
}

$pdo = db();
$pdo->beginTransaction();
$checkedOutProducts = [];

try {
    foreach ($cartProducts as $product) {
        $productId = (int) $product['id'];
        $quantity = max(1, (int) ($product['quantity'] ?? 1));

        $stockStmt = $pdo->prepare(
            'SELECT p.*, u.display_name AS seller_name, u.email AS seller_email, u.phone AS seller_phone
             FROM products p
             INNER JOIN users u ON u.id = p.seller_id
             WHERE p.id = :id
             LIMIT 1'
        );
        $stockStmt->execute(['id' => $productId]);
        $liveProduct = $stockStmt->fetch();

        if (!$liveProduct || $liveProduct['status'] === 'sold_out' || (int) $liveProduct['stock'] < $quantity) {
            throw new RuntimeException('商品庫存不足，請返回購物車重新確認。');
        }

        $liveProduct = normalize_product_display($liveProduct);
        $newStock = (int) $liveProduct['stock'] - $quantity;
        $newStatus = $newStock <= 0 ? 'sold_out' : 'active';
        $paidAt = date('Y-m-d H:i:s');
        $lineTotal = $quantity * (float) $liveProduct['price'];

        $orderStmt = $pdo->prepare(
            'INSERT INTO orders
                (product_id, buyer_id, seller_id, quantity, total_amount, status, created_at, paid_at)
             VALUES
                (:product_id, :buyer_id, :seller_id, :quantity, :total_amount, :status, NOW(), :paid_at)'
        );
        $orderStmt->execute([
            'product_id' => $productId,
            'buyer_id' => current_user()['id'],
            'seller_id' => $liveProduct['seller_id'],
            'quantity' => $quantity,
            'total_amount' => $lineTotal,
            'status' => 'paid',
            'paid_at' => $paidAt,
        ]);

        $orderId = (int) $pdo->lastInsertId();

        $updateStmt = $pdo->prepare(
            'UPDATE products
             SET stock = :stock,
                 status = :status,
                 updated_at = NOW(),
                 sold_at = :sold_at
             WHERE id = :id'
        );
        $updateStmt->execute([
            'stock' => $newStock,
            'status' => $newStatus,
            'sold_at' => $newStock <= 0 ? $paidAt : null,
            'id' => $productId,
        ]);

        $checkedOutProducts[] = [
            'order_id' => $orderId,
            'quantity' => $quantity,
            'total_amount' => $lineTotal,
            'product' => $liveProduct,
        ];
    }

    $pdo->commit();

    $buyer = fetch_user_by_id((int) current_user()['id']);
    foreach ($checkedOutProducts as $item) {
        $seller = fetch_user_by_id((int) $item['product']['seller_id']);
        if (!$buyer || !$seller) {
            continue;
        }

        send_order_notifications([
            'id' => $item['order_id'],
            'quantity' => $item['quantity'],
            'total_amount' => $item['total_amount'],
            'paid_at' => date('Y-m-d H:i:s'),
        ], $buyer, $seller, $item['product']);
    }

    $_SESSION['latest_order_ids'] = array_map(
        static fn(array $item): int => (int) $item['order_id'],
        $checkedOutProducts
    );
    clear_cart();
    redirect('buyer_orders.php');
} catch (Throwable) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    set_flash('flash_error', '結帳失敗，請返回購物車再試一次。');
    redirect('cart.php');
}
