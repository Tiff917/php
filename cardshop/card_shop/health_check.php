<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remoteAddr, ['127.0.0.1', '::1', ''], true)) {
    http_response_code(403);
    exit('Local access only.');
}

use PHPMailer\PHPMailer\PHPMailer;

function health_row(string $label, bool $ok, string $detail): array
{
    return [
        'label' => $label,
        'ok' => $ok,
        'detail' => $detail,
    ];
}

function smtp_socket_check(string $host, int $port): array
{
    $errorNumber = 0;
    $errorString = '';
    $fp = @fsockopen($host, $port, $errorNumber, $errorString, 8);

    if (!$fp) {
        return [false, $errorString !== '' ? $errorString : 'Connection failed'];
    }

    fclose($fp);
    return [true, 'Connected'];
}

function send_health_mail_test(): array
{
    $mailerFiles = [
        __DIR__ . '/vendor/phpmailer/src/Exception.php',
        __DIR__ . '/vendor/phpmailer/src/PHPMailer.php',
        __DIR__ . '/vendor/phpmailer/src/SMTP.php',
    ];

    foreach ($mailerFiles as $file) {
        if (!is_file($file)) {
            return [false, 'PHPMailer files missing'];
        }
    }

    require_once __DIR__ . '/vendor/phpmailer/src/Exception.php';
    require_once __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/vendor/phpmailer/src/SMTP.php';

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom(MAIL_FROM_ADDRESS, APP_NAME . ' Health Check');
        $mail->addAddress(MAIL_FROM_ADDRESS);
        $mail->Subject = '[Health Check] card_shop mail ok';
        $mail->Body = 'Health check mail sent at ' . date('Y-m-d H:i:s');
        $mail->send();

        return [true, 'Mail sent to ' . MAIL_FROM_ADDRESS];
    } catch (Throwable $e) {
        return [false, $e->getMessage()];
    }
}

$rows = [];
$rows[] = health_row('PHP Version', version_compare(PHP_VERSION, '8.2.0', '>='), PHP_VERSION);
$rows[] = health_row('Timezone', date_default_timezone_get() === 'Asia/Taipei', date_default_timezone_get());

foreach ([
    'pdo_mysql',
    'mysqli',
    'gd',
    'curl',
    'mbstring',
    'openssl',
    'fileinfo',
    'ftp',
] as $extension) {
    $rows[] = health_row('Extension: ' . $extension, extension_loaded($extension), extension_loaded($extension) ? 'Loaded' : 'Missing');
}

try {
    $pdo = db();
    $stats = $pdo->query(
        'SELECT
            (SELECT COUNT(*) FROM users) AS users_count,
            (SELECT COUNT(*) FROM products) AS products_count,
            (SELECT COUNT(*) FROM orders) AS orders_count,
            (SELECT COUNT(*) FROM reviews) AS reviews_count'
    )->fetch();
    $rows[] = health_row(
        'Database Connection',
        true,
        'users ' . (int) $stats['users_count'] .
        ' / products ' . (int) $stats['products_count'] .
        ' / orders ' . (int) $stats['orders_count'] .
        ' / reviews ' . (int) $stats['reviews_count']
    );
} catch (Throwable $e) {
    $rows[] = health_row('Database Connection', false, $e->getMessage());
}

foreach ([
    'Upload Dir' => UPLOAD_DIR,
    'Product Upload Dir' => UPLOAD_DIR . '/products',
    'Report Dir' => REPORT_DIR,
] as $label => $path) {
    $rows[] = health_row($label, is_dir($path) && is_writable($path), $path);
}

$mailerBase = __DIR__ . '/vendor/phpmailer/src';
$rows[] = health_row(
    'PHPMailer Files',
    is_file($mailerBase . '/PHPMailer.php') && is_file($mailerBase . '/SMTP.php') && is_file($mailerBase . '/Exception.php'),
    $mailerBase
);

$gdOk = false;
if (extension_loaded('gd') && function_exists('imagecreatetruecolor')) {
    $image = imagecreatetruecolor(20, 20);
    if ($image !== false) {
        $gdOk = true;
        imagedestroy($image);
    }
}
$rows[] = health_row('GD Runtime', $gdOk, $gdOk ? 'Image functions ready' : 'GD runtime failed');

$smtpConfigured = SMTP_HOST !== '' && SMTP_PORT > 0 && SMTP_USERNAME !== '' && SMTP_PASSWORD !== '' && MAIL_FROM_ADDRESS !== '';
$rows[] = health_row('SMTP Config', $smtpConfigured, SMTP_HOST . ':' . SMTP_PORT . ' / ' . MAIL_FROM_ADDRESS);

[$smtpSocketOk, $smtpSocketDetail] = smtp_socket_check(SMTP_HOST, SMTP_PORT);
$rows[] = health_row('SMTP Reachability', $smtpSocketOk, $smtpSocketDetail);

$apacheErrorLog = 'C:/xampp/apache/logs/error.log';
$phpErrorLog = 'C:/xampp/php/logs/php_error_log';
$rows[] = health_row('Apache Log Path', is_file($apacheErrorLog), $apacheErrorLog);
$rows[] = health_row('PHP Log Path', is_file($phpErrorLog), $phpErrorLog);

$mailTestResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mail_test') {
    [$mailOk, $mailDetail] = send_health_mail_test();
    $mailTestResult = health_row('Mail Test', $mailOk, $mailDetail);
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Check | <?= h(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .health-shell { width: min(760px, calc(100% - 32px)); margin: 24px auto 56px; }
        .health-head { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; margin-bottom: 28px; }
        .health-head p { margin: 8px 0 0; }
        .health-list { display: grid; gap: 14px; }
        .health-item { border-bottom: 1px solid #d9cfc3; padding: 0 0 14px; }
        .health-top { display: flex; justify-content: space-between; gap: 12px; align-items: center; }
        .health-status { font-weight: 700; }
        .health-status.ok { color: #665843; }
        .health-status.fail { color: #9c8867; }
        .health-detail { margin: 8px 0 0; opacity: 0.78; word-break: break-word; }
        .health-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 28px; }
        .health-actions form, .health-actions a { flex: 1 1 220px; }
        .health-actions .button { width: 100%; }
    </style>
</head>
<body class="app-page">
    <main class="health-shell">
        <section class="app-section">
            <div class="health-head">
                <div>
                    <h2>本機健康檢查</h2>
                    <p class="muted">一頁看完 XAMPP、資料庫、圖片、寄信與 card_shop 狀態。</p>
                </div>
                <a class="button secondary" href="signin.php">回登入</a>
            </div>
            <div class="health-list">
                <?php foreach ($rows as $row): ?>
                    <article class="health-item">
                        <div class="health-top">
                            <strong><?= h($row['label']) ?></strong>
                            <span class="health-status <?= $row['ok'] ? 'ok' : 'fail' ?>"><?= $row['ok'] ? '正常' : '需處理' ?></span>
                        </div>
                        <p class="health-detail"><?= h($row['detail']) ?></p>
                    </article>
                <?php endforeach; ?>

                <?php if ($mailTestResult): ?>
                    <article class="health-item">
                        <div class="health-top">
                            <strong><?= h($mailTestResult['label']) ?></strong>
                            <span class="health-status <?= $mailTestResult['ok'] ? 'ok' : 'fail' ?>"><?= $mailTestResult['ok'] ? '正常' : '需處理' ?></span>
                        </div>
                        <p class="health-detail"><?= h($mailTestResult['detail']) ?></p>
                    </article>
                <?php endif; ?>
            </div>
            <div class="health-actions">
                <form method="post">
                    <input type="hidden" name="action" value="mail_test">
                    <button type="submit">寄信實測</button>
                </form>
                <a class="button secondary" href="http://localhost/phpmyadmin/" target="_blank" rel="noopener">打開 phpMyAdmin</a>
            </div>
        </section>
    </main>
</body>
</html>
