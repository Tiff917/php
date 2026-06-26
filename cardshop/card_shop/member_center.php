<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';
require_login();

$sessionUser = current_user();
$user = fetch_user_by_id((int) $sessionUser['id']);
$role = (string) ($user['role'] ?? 'buyer');

$pageTitle = '會員資料 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="app-section">
    <h2 class="section-title">會員資料</h2>
    <form method="post" action="update_profile.php">
        <div class="field">
            <label for="display_name">顯示名稱</label>
            <input id="display_name" name="display_name" value="<?= h((string) $user['display_name']) ?>" required>
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= h((string) $user['email']) ?>" required>
        </div>
        <div class="field">
            <label for="phone">手機</label>
            <input id="phone" name="phone" value="<?= h((string) ($user['phone'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="favorite_group">喜歡的團體</label>
            <input id="favorite_group" name="favorite_group" value="<?= h((string) ($user['favorite_group'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="address">地址</label>
            <input id="address" name="address" value="<?= h((string) ($user['address'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="new_password">新密碼</label>
            <input id="new_password" name="new_password" type="password" minlength="6" placeholder="留空就維持原密碼">
        </div>
        <button type="submit">儲存會員資料</button>
    </form>
</section>

<?php if ($role === 'buyer'): ?>
<section class="app-section">
    <div class="action-grid">
        <a class="button secondary action-chip" href="buyer_orders.php">查看訂單</a>
        <a class="button secondary action-chip" href="signout.php">登出</a>
    </div>
</section>
<?php else: ?>
<section class="app-section">
    <a class="button secondary" href="signout.php">登出</a>
</section>
<?php endif; ?>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
