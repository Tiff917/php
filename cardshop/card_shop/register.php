<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';

if (is_logged_in()) {
    redirect('member_center.php');
}

$pageTitle = '註冊 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="auth-block">
    <a class="back-link" href="signin.php">返回登入</a>
    <h2>註冊</h2>
    <p class="muted auth-copy">建立你的 T's cashop 帳號，開始收藏與上架喜歡的小卡。</p>
    <form method="post" action="register_process.php" class="auth-form">
        <div class="field">
            <div class="field-head">
                <label for="display_name">顯示名稱</label>
            </div>
            <input id="display_name" name="display_name" placeholder="請輸入你的顯示名稱" required>
        </div>
        <div class="field">
            <div class="field-head">
                <label for="username">帳號</label>
            </div>
            <input id="username" name="username" placeholder="登入時會使用這個帳號" required>
        </div>
        <div class="field">
            <div class="field-head">
                <label for="email">Email</label>
            </div>
            <input id="email" name="email" type="email" placeholder="請輸入可收信的 Email" required>
        </div>
        <div class="field">
            <div class="field-head">
                <label for="phone">手機</label>
            </div>
            <input id="phone" name="phone" placeholder="例如 0912345678">
        </div>
        <div class="field">
            <div class="field-head">
                <label for="favorite_group">喜歡的團體</label>
            </div>
            <input id="favorite_group" name="favorite_group" placeholder="例如 TXT">
        </div>
        <div class="field">
            <div class="field-head">
                <label for="address">地址</label>
            </div>
            <input id="address" name="address" placeholder="可填寫收件地址">
        </div>
        <div class="field">
            <div class="field-head">
                <label for="role">身分</label>
            </div>
            <select id="role" name="role" required>
                <option value="buyer">買家</option>
                <option value="seller">賣家</option>
            </select>
        </div>
        <div class="field">
            <div class="field-head">
                <label for="password">密碼</label>
            </div>
            <input id="password" name="password" type="password" minlength="6" placeholder="至少 6 碼" required>
        </div>
        <div class="field">
            <div class="field-head">
                <label for="password_confirm">確認密碼</label>
            </div>
            <input id="password_confirm" name="password_confirm" type="password" minlength="6" placeholder="請再次輸入密碼" required>
        </div>
        <button type="submit">建立帳號</button>
    </form>
    <div class="auth-switch">
        <span>已經有帳號了？</span>
        <a href="signin.php">立即登入</a>
    </div>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
