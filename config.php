<?php
// 使用 127.0.0.1 替代 localhost，能有效解決 Windows 環境下 IPv6 (::1) 導致的連線延遲或連線失敗問題
$db_host = "127.0.0.1";
$db_user = "root";
$db_pass = "";
$db_name = "mail_system";

// 1. 嘗試連線 MySQL 伺服器
$link = @new mysqli($db_host, $db_user, $db_pass);

if ($link->connect_error) {
    die("<h3 style='color: red;'>❌ MySQL 伺服器連線失敗！請確保您的 XAMPP MySQL 服務已啟動。</h3><p>錯誤訊息: " . $link->connect_error . "</p>");
}

// 2. 檢查資料庫是否存在，若不存在則自動嘗試建立並匯入 schema
$db_selected = $link->select_db($db_name);
if (!$db_selected) {
    // 自動建立資料庫
    if ($link->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
        $link->select_db($db_name);
        
        // 讀取並匯入 mail_system.sql 檔案
        $sql_file = __DIR__ . '/mail_system.sql';
        if (file_exists($sql_file)) {
            $sql_content = file_get_contents($sql_file);
            if ($link->multi_query($sql_content)) {
                do {
                    if ($result = $link->store_result()) {
                        $result->free();
                    }
                } while ($link->next_result());
            }
        }
    } else {
        die("<h3 style='color: red;'>❌ 找不到資料庫 '{$db_name}'，且自動建立失敗。</h3><p>錯誤訊息: " . $link->error . "</p>");
    }
}

// 3. 設定編碼，確保中文不亂碼
$link->set_charset("utf8mb4");
?>
 