<?php
session_start();
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'] ?? "";
    $password = $_POST['password'] ?? "";

    if ($user_id === "test" && $password === "123") {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role'] = "buyer";
        
        // 設定 Cookie 紀錄使用者名稱
        setcookie("remember_me", $user_id, time() + 3600, "/");
        
        // 成功後跳轉到產品頁
        header("Location: cartlog.php");
        exit; 
    } else {
        $error = "帳號或密碼錯誤，請重新輸入！";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>登入 - 購物網站</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --bg-color: #f3e9dc; --container-bg: #ffffff; --accent-color: #c6a49a; --main-text: #5d4037; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-color); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: var(--container-bg); padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 350px; text-align: center; }
        h2 { color: var(--main-text); margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1.5px solid #e0d5c1; border-radius: 12px; box-sizing: border-box; outline: none; }
        input[type="submit"] { background: var(--accent-color); border: none; color: white; cursor: pointer; font-weight: 600; transition: 0.3s; }
        input[type="submit"]:hover { background: #b89388; }
        .error-msg { color: #d32f2f; font-size: 0.85rem; background: #ffebee; padding: 10px; border-radius: 8px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>系統登入</h2>
        <?php if ($error !== ""): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="cartlog.php">
            <input type="text" name="user_id" placeholder="帳號 (test)" required>
            <input type="password" name="password" placeholder="密碼 (123)" required>
            <input type="submit" value="立即登入">
        </form>
    </div>
</body>
</html>
