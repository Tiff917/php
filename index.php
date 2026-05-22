<?php
require_once 'config.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // 使用預處理陳述式防止 SQL 注入
        $stmt = $link->prepare("INSERT INTO subscribers (email) VALUES (?)");
        $stmt->bind_param("s", $email);
        
        if ($stmt->execute()) {
            $message = "<p style='color: green;'>✨ Email 成功儲存至資料庫！</p>";
        } else {
            $message = "<p style='color: red;'>❌ 儲存失敗（可能該 Email 已存在）。</p>";
        }
        $stmt->close();
    } else {
        $message = "<p style='color: red;'>❌ 請輸入正確的 Email 格式！</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>作業4 - 建構資料庫</title>
</head>
<body>
    <h2>📁 郵件系統：建構資料庫</h2>
    
    <?php echo $message; ?>
    
    <form action="index.php" method="POST">
        <label>請輸入收件者 Email：</label>
        <input type="email" name="email" required placeholder="example@mail.com">
        <button type="submit">新增至資料庫</button>
    </form>

    <br><hr><br>
    <a href="mail_panel.php" style="font-size: 1.1em; font-weight: bold;">➡️ 前往基本郵件寄送控制台</a>
</body>
</html>