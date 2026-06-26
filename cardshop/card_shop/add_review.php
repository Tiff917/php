<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';
require_login(['buyer']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('buyer_orders.php');
}

$orderId = (int) ($_POST['order_id'] ?? 0);
$rating = (int) ($_POST['rating'] ?? 0);
$comment = trim((string) ($_POST['comment'] ?? ''));

if ($rating < 1 || $rating > 5 || $comment === '') {
    set_flash('flash_error', '請完整填寫評分與評價內容。');
    redirect('review.php?order_id=' . $orderId);
}

$stmt = db()->prepare('SELECT * FROM orders WHERE id = :id AND buyer_id = :buyer_id LIMIT 1');
$stmt->execute([
    'id' => $orderId,
    'buyer_id' => current_user()['id'],
]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('flash_error', '找不到這筆可評價的訂單。');
    redirect('member_center.php');
}

$check = db()->prepare('SELECT id FROM reviews WHERE order_id = :order_id AND buyer_id = :buyer_id AND seller_id = :seller_id LIMIT 1');
$check->execute([
    'order_id' => $orderId,
    'buyer_id' => current_user()['id'],
    'seller_id' => $order['seller_id'],
]);

if ($check->fetch()) {
    set_flash('flash_error', '這筆訂單已經評價過了。');
    redirect('seller_profile.php?seller_id=' . (int) $order['seller_id']);
}

$insert = db()->prepare(
    'INSERT INTO reviews (order_id, buyer_id, seller_id, rating, comment, created_at)
     VALUES (:order_id, :buyer_id, :seller_id, :rating, :comment, NOW())'
);
$insert->execute([
    'order_id' => $orderId,
    'buyer_id' => current_user()['id'],
    'seller_id' => $order['seller_id'],
    'rating' => $rating,
    'comment' => $comment,
]);

set_flash('flash_success', '評價已送出，謝謝你的回饋。');
redirect('seller_profile.php?seller_id=' . (int) $order['seller_id']);
