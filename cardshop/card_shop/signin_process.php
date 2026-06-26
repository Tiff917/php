<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('signin.php');
}

try {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $rememberMe = isset($_POST['remember_me']);

    $stmt = db()->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, (string) ($user['password_hash'] ?? ''))) {
        set_flash('flash_error', '帳號或密碼錯誤，請重新輸入。');
        redirect('signin.php');
    }

    login_user($user);

    if ($rememberMe) {
        issue_remember_token((int) $user['id']);
    }

    set_flash('flash_success', '登入成功，歡迎回到 T\'s cashop。');
    redirect('index.php');
} catch (Throwable $e) {
    $logPath = UPLOAD_DIR . '/signin-error.log';
    $line = '[' . date('Y-m-d H:i:s') . '] ' . get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
    @file_put_contents($logPath, $line, FILE_APPEND);

    set_flash('flash_error', '登入時發生問題，請稍後再試一次。');
    redirect('signin.php');
}
