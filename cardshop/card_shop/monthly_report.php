<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';
require_login(['seller']);

$month = preg_match('/^\d{4}-\d{2}$/', (string) ($_GET['month'] ?? '')) ? (string) $_GET['month'] : date('Y-m');
$userId = (int) current_user()['id'];
$summary = monthly_sales_summary($userId, $month);
$groupStats = seller_monthly_group_revenue($userId, $month);
$dailyStats = seller_monthly_daily_orders($userId, $month);
$orders = seller_monthly_orders($userId, $month);

$soldProducts = (int) ($summary['total_orders'] ?? 0);
$totalTransactions = (int) ($summary['total_transactions'] ?? 0);
$totalCards = (int) ($summary['total_cards'] ?? 0);
$totalRevenue = (float) ($summary['total_revenue'] ?? 0);
$averageOrderValue = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0.0;
$topGroup = $groupStats[0]['group_name'] ?? '本月尚無團體營收資料';
$topGroupRevenue = (float) ($groupStats[0]['total_revenue'] ?? 0);
$maxGroupRevenue = max(1.0, ...array_map(static fn(array $row): float => (float) $row['total_revenue'], $groupStats === [] ? [['total_revenue' => 0]] : $groupStats));
$maxDailyOrders = max(1, ...array_map(static fn(array $row): int => (int) $row['order_count'], $dailyStats === [] ? [['order_count' => 0]] : $dailyStats));

$pageTitle = '月報表 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="app-section">
    <div class="section-head">
        <h2 class="section-title">月報表</h2>
        <a class="button secondary compact-button" href="monthly_report_pdf.php?month=<?= h($month) ?>">匯出 PDF</a>
    </div>
</section>

<section class="app-section">
    <form method="get" class="filter-inline">
        <div class="field" style="margin-bottom: 0; flex: 1;">
            <label for="month">統計月份</label>
            <select id="month" name="month">
                <?php foreach (month_options(12) as $value => $label): ?>
                    <option value="<?= h($value) ?>" <?= $month === $value ? 'selected' : '' ?>><?= h($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="compact-button">更新月份</button>
    </form>
</section>

<section class="app-section">
    <div class="summary-grid">
        <article class="summary-card">
            <span class="metric-label">售出商品數</span>
            <strong class="metric-value"><?= $soldProducts ?></strong>
            <p class="muted-small">本月有成交的商品種類數</p>
        </article>
        <article class="summary-card">
            <span class="metric-label">交易筆數</span>
            <strong class="metric-value"><?= $totalTransactions ?></strong>
            <p class="muted-small">本月完成的訂單筆數</p>
        </article>
        <article class="summary-card">
            <span class="metric-label">售出張數</span>
            <strong class="metric-value"><?= $totalCards ?></strong>
            <p class="muted-small">本月所有商品累積售出張數</p>
        </article>
        <article class="summary-card">
            <span class="metric-label">本月營收</span>
            <strong class="metric-value"><?= h(format_currency($totalRevenue)) ?></strong>
            <p class="muted-small">本月成交金額總和</p>
        </article>
    </div>
</section>

<section class="app-section">
    <div class="ranking-grid">
        <article class="ranking-card">
            <span class="metric-label">本月最佳團體</span>
            <strong class="ranking-title"><?= h((string) $topGroup) ?></strong>
            <p class="muted-small"><?= h(format_currency($topGroupRevenue)) ?> 的營收表現</p>
        </article>
        <article class="ranking-card">
            <span class="metric-label">平均客單價</span>
            <strong class="ranking-title"><?= h(format_currency($averageOrderValue)) ?></strong>
            <p class="muted-small">每筆訂單平均成交金額</p>
        </article>
    </div>
</section>

<section class="app-section section-card">
    <div class="section-head">
        <h3 class="section-title">團體營收排行</h3>
        <span class="muted-small">依營收由高到低排序</span>
    </div>
    <?php if ($groupStats === []): ?>
        <p class="muted">這個月份還沒有團體營收資料。</p>
    <?php else: ?>
        <div class="chart-list">
            <?php foreach ($groupStats as $index => $row): ?>
                <?php $width = max(12, (int) round(((float) $row['total_revenue'] / $maxGroupRevenue) * 100)); ?>
                <article class="chart-row chart-row-card">
                    <div class="chart-rank"><?= $index + 1 ?></div>
                    <div class="chart-meta">
                        <strong><?= h($row['group_name'] !== '' ? (string) $row['group_name'] : '未分類') ?></strong>
                        <span class="muted-small"><?= (int) $row['order_count'] ?> 種商品 / <?= (int) $row['transaction_count'] ?> 筆交易 / <?= (int) $row['total_cards'] ?> 張</span>
                    </div>
                    <div class="chart-bar-track">
                        <div class="chart-bar-fill" style="width: <?= $width ?>%;"></div>
                    </div>
                    <strong class="chart-value"><?= h(format_currency((float) $row['total_revenue'])) ?></strong>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="app-section section-card">
    <div class="section-head">
        <h3 class="section-title">每日訂單趨勢</h3>
        <span class="muted-small">本月每天的成交筆數</span>
    </div>
    <?php if ($dailyStats === []): ?>
        <p class="muted">這個月份還沒有每日訂單資料。</p>
    <?php else: ?>
        <div class="chart-list">
            <?php foreach ($dailyStats as $row): ?>
                <?php $width = max(12, (int) round(((int) $row['order_count'] / $maxDailyOrders) * 100)); ?>
                <article class="chart-row chart-row-card">
                    <div class="chart-meta">
                        <strong><?= h((string) $row['order_date']) ?></strong>
                        <span class="muted-small"><?= h(format_currency((float) $row['total_revenue'])) ?></span>
                    </div>
                    <div class="chart-bar-track">
                        <div class="chart-bar-fill soft" style="width: <?= $width ?>%;"></div>
                    </div>
                    <strong class="chart-value"><?= (int) $row['order_count'] ?> 筆</strong>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="app-section section-card">
    <div class="section-head">
        <h3 class="section-title">本月訂單明細</h3>
        <span class="muted-small">顯示本月所有成交訂單</span>
    </div>
    <?php if ($orders === []): ?>
        <p class="muted">本月還沒有訂單，可以先回賣家頁上架更多商品。</p>
        <div class="action-grid">
            <a class="button secondary action-chip" href="seller_dashboard.php">回賣家頁</a>
            <a class="button secondary action-chip" href="product_list.php">查看商品頁</a>
        </div>
    <?php else: ?>
        <div class="simple-list">
            <?php foreach ($orders as $order): ?>
                <article class="list-row">
                    <div>
                        <strong><?= h((string) $order['product_name']) ?></strong>
                        <p class="muted-small"><?= h((string) $order['group_name']) ?> / <?= h((string) $order['member_name']) ?></p>
                        <p class="muted-small">買家：<?= h((string) $order['buyer_name']) ?> / 數量：<?= (int) $order['quantity'] ?> 張</p>
                    </div>
                    <div class="list-row-meta">
                        <strong><?= h(format_currency((float) $order['total_amount'])) ?></strong>
                        <span class="muted-small"><?= h((string) $order['created_at']) ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="app-section">
    <div class="section-head">
        <h3 class="section-title">快速操作</h3>
    </div>
    <div class="action-grid">
        <a class="button secondary action-chip" href="seller_dashboard.php">賣家頁</a>
        <a class="button secondary action-chip" href="monthly_report_pdf.php?month=<?= h($month) ?>">下載 PDF</a>
        <a class="button secondary action-chip" href="member_center.php">會員資料</a>
        <a class="button secondary action-chip" href="product_list.php">查看商品</a>
    </div>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
