<?php
session_start();

// 檢查是否登入，沒登入直接踢回登入頁
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
// 從 Cookie 抓取資料，若不存在則顯示 Guest
$cookie_id = isset($_COOKIE['remember_me']) ? $_COOKIE['remember_me'] : "已清除";
?>

<!DOCTYPE html>
<html>
<head>
    <title>控制台</title>
</head>
<body>
    <h1>歡迎回來，<?php echo $user_id; ?>！</h1>
    <p>您是<strong><?php echo $role; ?></strong></p>
    <p>來自 Cookie 的紀錄：<?php echo $cookie_id; ?></p>

    <hr>

    <?php if ($role === 'student' || $role === 'admin' || $role === 'teacher'): ?>
        <div class="card">📚 學生專區：查看課程講義</div>
    <?php endif; ?>

    <?php if ($role === 'teacher' || $role === 'admin'): ?>
        <div class="card">📝 教師專區：進入評分系統</div>
    <?php endif; ?>

    <?php if ($role === 'admin'): ?>
        <div class="card" style="color:red;">⚙️ 管理者專區：系統設定與帳號管理</div>
    <?php endif; ?>

    <br>
    <a href="logout.php">登出系統並清除資料</a>
</body>
</html>
