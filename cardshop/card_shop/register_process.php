<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('register.php');
}

$displayName = trim((string) ($_POST['display_name'] ?? ''));
$username = trim((string) ($_POST['username'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$favoriteGroup = trim((string) ($_POST['favorite_group'] ?? ''));
$address = trim((string) ($_POST['address'] ?? ''));
$role = (string) ($_POST['role'] ?? 'buyer');
$password = (string) ($_POST['password'] ?? '');
$passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

if ($displayName === '' || $username === '' || $email === '' || $password === '' || $passwordConfirm === '') {
    set_flash('flash_error', '請把必填欄位完整填好。');
    redirect('register.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('flash_error', 'Email 格式不正確。');
    redirect('register.php');
}

if (!in_array($role, ['buyer', 'seller'], true)) {
    set_flash('flash_error', '身份選擇不正確。');
    redirect('register.php');
}

if (strlen($password) < 6) {
    set_flash('flash_error', '密碼至少需要 6 個字元。');
    redirect('register.php');
}

if ($password !== $passwordConfirm) {
    set_flash('flash_error', '兩次輸入的密碼不一致。');
    redirect('register.php');
}

$check = db()->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
$check->execute([
    'username' => $username,
    'email' => $email,
]);

if ($check->fetch()) {
    set_flash('flash_error', '這個帳號或 Email 已經被使用。');
    redirect('register.php');
}

$stmt = db()->prepare(
    'INSERT INTO users
        (username, password_hash, role, display_name, email, phone, favorite_group, address, created_at)
     VALUES
        (:username, :password_hash, :role, :display_name, :email, :phone, :favorite_group, :address, NOW())'
);
$stmt->execute([
    'username' => $username,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    'role' => $role,
    'display_name' => $displayName,
    'email' => $email,
    'phone' => $phone,
    'favorite_group' => $favoriteGroup,
    'address' => $address,
]);

$user = fetch_user_by_id((int) db()->lastInsertId());
if ($user) {
    login_user($user);
}

set_flash('flash_success', '註冊成功，已自動登入。');
redirect('member_center.php');
