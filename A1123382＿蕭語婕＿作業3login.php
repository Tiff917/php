<?php
session_start();
$error = ""; // 初始化錯誤訊息

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $password = $_POST['password'];

    if ($user_id === "S001" && $password === "123") {
        $role = "student";
    } elseif ($user_id === "T001" && $password === "123") {
        $role = "teacher";
    } elseif ($user_id === "A001" && $password === "123") {
        $role = "admin";
    } else {
        $error = "帳號密碼錯誤！";
    }

    if (isset($role)) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role'] = $role;
        setcookie("remember_me", $user_id, time() + 3600, "/");
        header("Location: board.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>角色登入系統</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* 奶茶色調設計 */
        :root { --bg-color: #f3e9dc; --container-bg: #ffffff; --accent-color: #c6a49a; --main-text: #5d4037; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-color); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: var(--container-bg); padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 350px; text-align: center; }
        h2 { color: var(--main-text); margin-bottom: 20px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 12px; margin: 10px 0; border: 1.5px solid #e0d5c1; border-radius: 12px; box-sizing: border-box; outline: none; }
        input[type="submit"] { width: 100%; padding: 12px; background: var(--accent-color); border: none; color: white; border-radius: 12px; cursor: pointer; font-weight: 600; margin-top: 15px; transition: 0.3s; }
        input[type="submit"]:hover { background-color: #b89388; }
        .error-msg { color: #d32f2f; font-size: 0.85rem; background: #ffebee; padding: 10px; border-radius: 8px; margin-bottom: 15px; }
        .hint { font-size: 0.8rem; color: #8d6e63; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>系統登入</h2>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="text" name="user_id" placeholder="使用者 ID (如: S001)" required>
            <input type="password" name="password" placeholder="密碼 (123)" required>
            <input type="submit" value="立即登入">
        </form>

        <div class="hint">
            學生: S001 / 老師: T001 / 管理者: A001 <br>
            預設密碼均為: 123
        </div>
    </div>
</body>
</html>
