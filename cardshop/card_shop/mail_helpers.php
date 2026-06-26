<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

use PHPMailer\PHPMailer\PHPMailer;

function build_buyer_order_mail_html(array $order, array $buyer, array $seller, array $product): string
{
    $productName = h((string) ($product['name'] ?? ''));
    $groupName = h((string) ($product['group_name'] ?? '未填寫'));
    $memberName = h((string) ($product['member_name'] ?? '未填寫'));
    $sellerName = h((string) ($seller['display_name'] ?? ''));
    $buyerName = h((string) ($buyer['display_name'] ?? ''));
    $quantity = (int) ($order['quantity'] ?? 0);
    $totalAmount = h(format_currency((float) ($order['total_amount'] ?? 0)));
    $paidAt = h((string) ($order['paid_at'] ?? ''));
    $orderId = (int) ($order['id'] ?? 0);

    return <<<HTML
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>T's cashop 買家訂單通知</title>
</head>
<body style="margin:0;padding:24px;background:#f5efe8;color:#665843;font-family:'Segoe UI','Microsoft JhengHei',sans-serif;">
    <div style="max-width:640px;margin:0 auto;background:#fffaf5;border:1px solid #ddcfbf;border-radius:24px;padding:28px 24px;">
        <p style="margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.08em;color:#9c8867;">T'S CASHOP</p>
        <h1 style="margin:0 0 14px;font-size:28px;line-height:1.3;color:#665843;">買家訂單成立通知</h1>
        <p style="margin:0 0 22px;font-size:15px;line-height:1.8;color:#665843;">
            嗨，{$buyerName}。你的小卡訂單已成立，以下是這次購買的資訊。
        </p>

        <div style="border-top:1px solid #eadfce;padding-top:18px;">
            <p style="margin:0 0 10px;font-size:15px;"><strong>訂單編號：</strong>#{$orderId}</p>
            <p style="margin:0 0 10px;font-size:15px;"><strong>商品名稱：</strong>{$productName}</p>
            <p style="margin:0 0 10px;font-size:15px;"><strong>團體 / 成員：</strong>{$groupName} / {$memberName}</p>
            <p style="margin:0 0 10px;font-size:15px;"><strong>賣家：</strong>{$sellerName}</p>
            <p style="margin:0 0 10px;font-size:15px;"><strong>購買數量：</strong>{$quantity} 張</p>
            <p style="margin:0 0 10px;font-size:15px;"><strong>付款金額：</strong>{$totalAmount}</p>
            <p style="margin:0;font-size:15px;"><strong>成立時間：</strong>{$paidAt}</p>
        </div>

        <div style="margin-top:24px;padding-top:18px;border-top:1px solid #eadfce;">
            <p style="margin:0;font-size:14px;line-height:1.8;color:#7b6d58;">
                你可以回到會員中心查看購買紀錄，也可以在完成交易後留下評價。
            </p>
        </div>
    </div>
</body>
</html>
HTML;
}

function build_seller_order_mail_html(array $order, array $buyer, array $seller, array $product): string
{
    $productName = h((string) ($product['name'] ?? ''));
    $groupName = h((string) ($product['group_name'] ?? '未填寫'));
    $memberName = h((string) ($product['member_name'] ?? '未填寫'));
    $sellerName = h((string) ($seller['display_name'] ?? ''));
    $buyerName = h((string) ($buyer['display_name'] ?? ''));
    $quantity = (int) ($order['quantity'] ?? 0);
    $totalAmount = h(format_currency((float) ($order['total_amount'] ?? 0)));
    $paidAt = h((string) ($order['paid_at'] ?? ''));
    $orderId = (int) ($order['id'] ?? 0);

    return <<<HTML
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>T's cashop 賣家售出通知</title>
</head>
<body style="margin:0;padding:24px;background:#f5efe8;color:#665843;font-family:'Segoe UI','Microsoft JhengHei',sans-serif;">
    <div style="max-width:640px;margin:0 auto;background:#fffaf5;border:1px solid #ddcfbf;border-radius:24px;padding:28px 24px;">
        <p style="margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.08em;color:#9c8867;">T'S CASHOP</p>
        <h1 style="margin:0 0 14px;font-size:28px;line-height:1.3;color:#665843;">賣家商品售出通知</h1>
        <p style="margin:0 0 22px;font-size:15px;line-height:1.8;color:#665843;">
            嗨，{$sellerName}。你的商品已成功售出，以下是這次成交資訊。
        </p>

        <div style="border-top:1px solid #eadfce;padding-top:18px;">
            <p style="margin:0 0 10px;font-size:15px;"><strong>訂單編號：</strong>#{$orderId}</p>
            <p style="margin:0 0 10px;font-size:15px;"><strong>商品名稱：</strong>{$productName}</p>
            <p style="margin:0 0 10px;font-size:15px;"><strong>團體 / 成員：</strong>{$groupName} / {$memberName}</p>
            <p style="margin:0 0 10px;font-size:15px;"><strong>買家：</strong>{$buyerName}</p>
            <p style="margin:0 0 10px;font-size:15px;"><strong>售出數量：</strong>{$quantity} 張</p>
            <p style="margin:0 0 10px;font-size:15px;"><strong>成交金額：</strong>{$totalAmount}</p>
            <p style="margin:0;font-size:15px;"><strong>成立時間：</strong>{$paidAt}</p>
        </div>

        <div style="margin-top:24px;padding-top:18px;border-top:1px solid #eadfce;">
            <p style="margin:0;font-size:14px;line-height:1.8;color:#7b6d58;">
                你可以回到賣家中心查看最近訂單，也可以到月報頁查看本月銷售變化。
            </p>
        </div>
    </div>
</body>
</html>
HTML;
}

function build_buyer_order_mail_text(array $order, array $buyer, array $seller, array $product): string
{
    return implode(PHP_EOL, [
        'T\'s cashop 買家訂單成立通知',
        '',
        '訂單編號：#' . (int) ($order['id'] ?? 0),
        '商品名稱：' . (string) ($product['name'] ?? ''),
        '團體 / 成員：' . (string) ($product['group_name'] ?? '未填寫') . ' / ' . (string) ($product['member_name'] ?? '未填寫'),
        '賣家：' . (string) ($seller['display_name'] ?? ''),
        '購買數量：' . (int) ($order['quantity'] ?? 0) . ' 張',
        '付款金額：' . format_currency((float) ($order['total_amount'] ?? 0)),
        '成立時間：' . (string) ($order['paid_at'] ?? ''),
    ]);
}

function build_seller_order_mail_text(array $order, array $buyer, array $seller, array $product): string
{
    return implode(PHP_EOL, [
        'T\'s cashop 賣家商品售出通知',
        '',
        '訂單編號：#' . (int) ($order['id'] ?? 0),
        '商品名稱：' . (string) ($product['name'] ?? ''),
        '團體 / 成員：' . (string) ($product['group_name'] ?? '未填寫') . ' / ' . (string) ($product['member_name'] ?? '未填寫'),
        '買家：' . (string) ($buyer['display_name'] ?? ''),
        '售出數量：' . (int) ($order['quantity'] ?? 0) . ' 張',
        '成交金額：' . format_currency((float) ($order['total_amount'] ?? 0)),
        '成立時間：' . (string) ($order['paid_at'] ?? ''),
    ]);
}

function create_mailer(): PHPMailer
{
    require_once __DIR__ . '/vendor/phpmailer/src/Exception.php';
    require_once __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/vendor/phpmailer/src/SMTP.php';

    $mailer = new PHPMailer(true);
    $mailer->isSMTP();
    $mailer->Host = SMTP_HOST;
    $mailer->Port = SMTP_PORT;
    $mailer->SMTPAuth = true;
    $mailer->SMTPSecure = SMTP_ENCRYPTION === 'ssl'
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;
    $mailer->Username = SMTP_USERNAME;
    $mailer->Password = SMTP_PASSWORD;
    $mailer->CharSet = 'UTF-8';
    $mailer->Encoding = PHPMailer::ENCODING_BASE64;
    $mailer->ContentType = 'text/html; charset=UTF-8';
    $mailer->setLanguage('zh_tw');
    $mailer->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
    $mailer->isHTML(true);

    return $mailer;
}

function send_order_notifications(array $order, array $buyer, array $seller, array $product): ?string
{
    $mailerPath = __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
    if (!is_file($mailerPath)) {
        return 'PHPMailer 尚未安裝，無法寄送通知信。';
    }

    if (SMTP_USERNAME === '' || SMTP_PASSWORD === '' || MAIL_FROM_ADDRESS === '') {
        return 'SMTP 設定尚未完成，無法寄送通知信。';
    }

    $errors = [];

    try {
        $buyerMailer = create_mailer();
        $buyerMailer->addAddress((string) $buyer['email'], (string) $buyer['display_name']);
        $buyerMailer->Subject = APP_NAME . ' 買家訂單成立通知';
        $buyerMailer->Body = build_buyer_order_mail_html($order, $buyer, $seller, $product);
        $buyerMailer->AltBody = build_buyer_order_mail_text($order, $buyer, $seller, $product);
        $buyerMailer->send();
    } catch (Throwable $e) {
        $errors[] = '買家通知失敗：' . $e->getMessage();
    }

    try {
        $sellerMailer = create_mailer();
        $sellerMailer->addAddress((string) $seller['email'], (string) $seller['display_name']);
        $sellerMailer->Subject = APP_NAME . ' 賣家商品售出通知';
        $sellerMailer->Body = build_seller_order_mail_html($order, $buyer, $seller, $product);
        $sellerMailer->AltBody = build_seller_order_mail_text($order, $buyer, $seller, $product);
        $sellerMailer->send();
    } catch (Throwable $e) {
        $errors[] = '賣家通知失敗：' . $e->getMessage();
    }

    if ($errors === []) {
        db()->prepare('UPDATE orders SET notification_sent_at = NOW() WHERE id = :id')->execute([
            'id' => (int) $order['id'],
        ]);
        return null;
    }

    return implode(' / ', $errors);
}
