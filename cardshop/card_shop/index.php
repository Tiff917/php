<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';

$role = (string) (current_user()['role'] ?? '');

if ($role === 'seller') {
    redirect('seller_dashboard.php');
}

if ($role === 'admin') {
    redirect('admin_dashboard.php');
}

redirect('product_list.php');
