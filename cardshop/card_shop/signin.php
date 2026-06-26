<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';

if (is_logged_in()) {
    redirect('index.php');
}

$pageTitle = '登入 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="auth-block">
    <h2>登入</h2>
    <p class="muted auth-copy">回到 T's cashop，繼續收藏你喜歡的小卡。</p>
    <form method="post" action="signin_process.php" class="auth-form">
        <div class="field">
            <div class="field-head">
                <label for="username">帳號</label>
            </div>
            <input id="username" name="username" autocomplete="username" placeholder="請輸入帳號" required>
        </div>
        <div class="field">
            <div class="field-head">
                <label for="password">密碼</label>
            </div>
            <input id="password" name="password" type="password" autocomplete="current-password" placeholder="請輸入密碼" required>
        </div>
        <label class="inline-check">
            <input type="checkbox" name="remember_me" value="1">
            <span>7 天內保持登入</span>
        </label>
        <button type="submit">立即登入</button>
    </form>
    <div class="auth-switch">
        <span>第一次來這裡？</span>
        <a href="register.php">立即註冊</a>
    </div>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
