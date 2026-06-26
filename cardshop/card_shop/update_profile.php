<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('member_center.php');
}

$displayName = trim((string) ($_POST['display_name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$favoriteGroup = trim((string) ($_POST['favorite_group'] ?? ''));
$address = trim((string) ($_POST['address'] ?? ''));
$newPassword = (string) ($_POST['new_password'] ?? '');

if ($displayName === '' || $email === '') {
    set_flash('flash_error', '顯示名稱和 Email 都是必填欄位。');
    redirect('member_center.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('flash_error', 'Email 格式不正確。');
    redirect('member_center.php');
}

$check = db()->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
$check->execute([
    'email' => $email,
    'id' => current_user()['id'],
]);

if ($check->fetch()) {
    set_flash('flash_error', '這個 Email 已經被其他帳號使用。');
    redirect('member_center.php');
}

if ($newPassword !== '' && strlen($newPassword) < 6) {
    set_flash('flash_error', '新密碼至少要 6 個字元。');
    redirect('member_center.php');
}

$params = [
    'display_name' => $displayName,
    'email' => $email,
    'phone' => $phone,
    'favorite_group' => $favoriteGroup,
    'address' => $address,
    'id' => current_user()['id'],
];

if ($newPassword !== '') {
    $params['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = db()->prepare(
        'UPDATE users
         SET display_name = :display_name,
             email = :email,
             phone = :phone,
             favorite_group = :favorite_group,
             address = :address,
             password_hash = :password_hash
         WHERE id = :id'
    );
} else {
    $stmt = db()->prepare(
        'UPDATE users
         SET display_name = :display_name,
             email = :email,
             phone = :phone,
             favorite_group = :favorite_group,
             address = :address
         WHERE id = :id'
    );
}

$stmt->execute($params);
$updatedUser = fetch_user_by_id((int) current_user()['id']);
if ($updatedUser) {
    login_user($updatedUser);
}

redirect('member_center.php');
