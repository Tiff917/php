    </main>
    <?php if (is_logged_in() && !in_array(basename($_SERVER['PHP_SELF']), ['signin.php', 'register.php'], true)): ?>
        <?php $currentRole = (string) (current_user()['role'] ?? 'buyer'); ?>
        <nav class="bottom-tabs bottom-tabs-<?= h($currentRole) ?>">
            <a class="<?= in_array(basename($_SERVER['PHP_SELF']), ['product_list.php', 'product.php', 'index.php'], true) ? 'is-active' : '' ?>" href="product_list.php">
                <span>商品</span>
            </a>
            <?php if ($currentRole === 'buyer'): ?>
                <a class="<?= in_array(basename($_SERVER['PHP_SELF']), ['cart.php'], true) ? 'is-active' : '' ?>" href="cart.php">
                    <span>購物車</span>
                </a>
            <?php elseif ($currentRole === 'admin'): ?>
                <a class="<?= in_array(basename($_SERVER['PHP_SELF']), ['admin_dashboard.php'], true) ? 'is-active' : '' ?>" href="admin_dashboard.php">
                    <span>管理</span>
                </a>
            <?php else: ?>
                <a class="<?= in_array(basename($_SERVER['PHP_SELF']), ['seller_dashboard.php', 'monthly_report.php'], true) ? 'is-active' : '' ?>" href="seller_dashboard.php">
                    <span>賣家</span>
                </a>
            <?php endif; ?>
            <a class="<?= in_array(basename($_SERVER['PHP_SELF']), ['member_center.php', 'buyer_orders.php'], true) ? 'is-active' : '' ?>" href="member_center.php">
                <span>會員</span>
            </a>
        </nav>
    <?php endif; ?>
</body>
</html>
