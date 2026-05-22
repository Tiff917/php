<?php
require_once 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("不允許的存取方式");
}

// 取得表單參數
$subject = $_POST['mail_subject'];
$content = $_POST['mail_content'];
$mode    = $_POST['send_mode'];
$delay   = intval($_POST['delay_seconds']);

// 根據模式篩選名單
if ($mode === 'random') {
    $limit = intval($_POST['random_limit']);
    $query = "SELECT email FROM subscribers ORDER BY RAND() LIMIT $limit";
} else {
    $query = "SELECT email FROM subscribers";
}

$result = $link->query($query);
$email_list = [];

while ($row = $result->fetch_assoc()) {
    $email_list[] = $row['email'];
}

$total_emails = count($email_list);

if ($total_emails === 0) {
    die("<h3>❌ 目前資料庫中沒有符合的收件者名單！</h3><a href='mail_panel.php'>返回</a>");
}

echo "<h2>📢 郵件大量發送任務啟動</h2>";
echo "<p>預計發送總數：<strong>$total_emails</strong> 封</p>";
echo "<hr>";

// 逐筆發送並即時輸出進度
foreach ($email_list as $index => $to_email) {
    $current_index = $index + 1;
    // 計算百分比進度
    $progress_percent = round(($current_index / $total_emails) * 100);
    
    echo "<div><strong>[進度: {$progress_percent}%]</strong> 正在發送第 {$current_index} 封郵件至 {$to_email} ... ";
    
    // 初始化 PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        // ================= 【請在此處更換你新申請的 16 位金鑰】 =================
        $mail->Username   = 'a1123382@mail.nuk.edu.tw'; 
        $mail->Password   = 'xgczxkxubszbkuro';          
        // ========================================================================
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // 對應 465 埠口
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';
        
        // 發件人設定 (自動帶入上面填寫的帳號)
        $mail->setFrom($mail->Username, '垃圾郵件系統');
        $mail->addAddress($to_email);
        
        $mail->Subject = $subject;
        $mail->Body    = $content;
        
        $mail->send();
        echo "<span style='color: green; font-weight: bold;'>成功 ✔️</span></div>";
    } catch (Exception $e) {
        echo "<span style='color: red; font-weight: bold;'>失敗 ❌ (錯誤: {$mail->ErrorInfo})</span></div>";
    }
    
    // 強制將當前處理完的 HTML 輸出到瀏覽器畫面上
    if (ob_get_level() > 0) {
        ob_flush();
    }
    flush();
    
    // 檢查是否還有下一封，若有則依照設定秒數進行休眠間隔
    if ($current_index < $total_emails && $delay > 0) {
        sleep($delay);
    }
}

echo "<hr>";
echo "<h3>任務全數執行完畢！</h3>";
echo "<a href='mail_panel.php'>返回寄信控制台</a>";
?>