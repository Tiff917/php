<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$vendorDir = __DIR__ . '/vendor/phpmailer';
$zipPath = __DIR__ . '/tmp_phpmailer.zip';
$sourceUrl = 'https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip';

$message = '';

try {
    if (is_dir($vendorDir . '/src')) {
        $message = 'PHPMailer 已經安裝完成，可直接使用。';
    } else {
        $content = @file_get_contents($sourceUrl);
        if ($content === false) {
            throw new RuntimeException('無法下載 PHPMailer，請確認 XAMPP 的 PHP 可連外。');
        }

        file_put_contents($zipPath, $content);
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Zip 檔無法開啟。');
        }

        $extractPath = __DIR__ . '/vendor';
        if (!is_dir($extractPath)) {
            mkdir($extractPath, 0775, true);
        }
        $zip->extractTo($extractPath);
        $zip->close();

        $src = $extractPath . '/PHPMailer-master';
        if (!is_dir($src)) {
            throw new RuntimeException('解壓後找不到 PHPMailer 目錄。');
        }

        if (is_dir($vendorDir)) {
            throw new RuntimeException('目標資料夾已存在但不完整，請先手動清理後重試。');
        }

        rename($src, $vendorDir);
        @unlink($zipPath);
        $message = 'PHPMailer 安裝成功。';
    }
} catch (Throwable $e) {
    $message = '安裝失敗：' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>安裝 PHPMailer</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="page-shell">
    <section class="auth-card" style="max-width: 720px; margin: 40px auto;">
        <h2>install_phpmailer.php</h2>
        <p><?= h($message) ?></p>
        <p class="muted">若使用 Gmail SMTP，請把 `checkout.php` 內的信箱帳密改成你的寄件設定。</p>
        <a class="button secondary" href="index.php">回到首頁</a>
    </section>
</main>
</body>
</html>
