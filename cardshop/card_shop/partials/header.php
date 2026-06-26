<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$isAuthPage = in_array($currentPage, ['signin.php', 'register.php'], true);
$showBottomTabs = !$isAuthPage && is_logged_in();
$currentRole = (string) (current_user()['role'] ?? '');

$topLinkHref = '';
$topLinkText = '';

if (!$isAuthPage) {
    if ($currentRole === 'seller') {
        $topLinkHref = 'monthly_report.php';
        $topLinkText = '月報表';
    } elseif ($currentRole === 'admin') {
        $topLinkHref = 'admin_dashboard.php';
        $topLinkText = '管理頁面';
    } elseif (!is_logged_in()) {
        $topLinkHref = 'register.php';
        $topLinkText = '立即註冊';
    }
}

$brandHref = 'signin.php';
if (is_logged_in()) {
    if ($currentRole === 'seller') {
        $brandHref = 'seller_dashboard.php';
    } elseif ($currentRole === 'admin') {
        $brandHref = 'admin_dashboard.php';
    } else {
        $brandHref = 'product_list.php';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#f5efe8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="T's cashop">
    <title><?= h($pageTitle ?? APP_NAME) ?></title>
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="apple-touch-icon" href="assets/app-icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/app-icon-192.png">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= $isAuthPage ? 'auth-page' : 'app-page' ?>">
<header class="site-header <?= $isAuthPage ? 'compact-header' : '' ?>">
    <a class="brand" href="<?= h($brandHref) ?>">T's cashop</a>
    <?php if ($topLinkHref !== '' && $topLinkText !== ''): ?>
        <a class="top-link" href="<?= h($topLinkHref) ?>"><?= h($topLinkText) ?></a>
    <?php endif; ?>
</header>
<main class="page-shell <?= $isAuthPage ? 'auth-shell' : '' ?> <?= $showBottomTabs ? 'with-tabs' : '' ?>">
<?php if ($flash = pop_flash('flash_error')): ?>
    <div class="flash flash-error"><?= h($flash) ?></div>
<?php endif; ?>
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js').catch(() => {});
    });
}
</script>
