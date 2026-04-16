<?php
session_start();

// 1. 清除所有 Session
session_unset();
session_destroy();

// 2. 清除 Cookie (將過期時間設定為「過去」的時間，例如 -3600 秒)
if (isset($_COOKIE['remember_me'])) {
    setcookie("remember_me", "", time() - 3600, "/");
}

// 3. 導向回登入頁
header("Location: login.php");
exit;
?>
