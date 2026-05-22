<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "mail_system";

echo "=== 開始檢測資料庫連線 ===\n";

// 1. 測試 MySQL 服務是否存活（不指定資料庫）
$link = @new mysqli($db_host, $db_user, $db_pass);

if ($link->connect_error) {
    echo "【失敗】MySQL 服務未啟動，或帳號密碼錯誤。\n";
    echo "錯誤代碼: " . $link->connect_errno . "\n";
    echo "錯誤訊息: " . $link->connect_error . "\n";
    exit;
}

echo "【成功】MySQL 服務連線成功！\n";

// 2. 檢查資料庫是否存在
$db_selected = $link->select_db($db_name);
if (!$db_selected) {
    echo "【警告】資料庫 '{$db_name}' 不存在。嘗試自動建立並匯入 schema...\n";
    
    // 建立資料庫
    if ($link->query("CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
        echo "【成功】建立資料庫 '{$db_name}' 成功！\n";
        $link->select_db($db_name);
        
        // 讀取並匯入 mail_system.sql
        $sql_file = __DIR__ . '/mail_system.sql';
        if (file_exists($sql_file)) {
            $sql_content = file_get_contents($sql_file);
            
            // 簡單的 SQL 分割執行（以分號+換行分割）
            // 更好的方式是使用 multi_query
            if ($link->multi_query($sql_content)) {
                do {
                    // 清空結果集，以防影響後續操作
                    if ($result = $link->store_result()) {
                        $result->free();
                    }
                } while ($link->next_result());
                echo "【成功】已成功匯入 mail_system.sql！\n";
            } else {
                echo "【失敗】匯入 SQL 失敗: " . $link->error . "\n";
            }
        } else {
            echo "【錯誤】找不到 mail_system.sql 檔案，無法匯入資料表。\n";
        }
    } else {
        echo "【失敗】建立資料庫失敗: " . $link->error . "\n";
    }
} else {
    echo "【成功】資料庫 '{$db_name}' 存在。\n";
    
    // 檢查是否有 subscribers 表
    $result = $link->query("SHOW TABLES LIKE 'subscribers'");
    if ($result->num_rows == 0) {
        echo "【警告】找不到 subscribers 資料表。嘗試匯入 schema...\n";
        $sql_file = __DIR__ . '/mail_system.sql';
        if (file_exists($sql_file)) {
            $sql_content = file_get_contents($sql_file);
            if ($link->multi_query($sql_content)) {
                do {
                    if ($result_set = $link->store_result()) {
                        $result_set->free();
                    }
                } while ($link->next_result());
                echo "【成功】已成功匯入 mail_system.sql！\n";
            } else {
                echo "【失敗】匯入 SQL 失敗: " . $link->error . "\n";
            }
        } else {
            echo "【錯誤】找不到 mail_system.sql 檔案。\n";
        }
    } else {
        echo "【成功】資料表 'subscribers' 存在，資料庫一切正常！\n";
    }
}

$link->close();
?>
