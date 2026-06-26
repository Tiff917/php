# card_shop 本機維護手冊

## 啟動與停止

- 啟動整組環境：`C:\xampp\start_card_shop_stack.bat`
- 重新啟動整組環境：`C:\xampp\restart_card_shop_stack.bat`
- 停止整組環境：`C:\xampp\stop_card_shop_stack.bat`

建議平常直接用這三支，不要手動混著點不同 bat。

## 一鍵健康檢查

- 開啟：`http://localhost/card_shop/health_check.php`
- 這個頁面會檢查：
  - PHP 與時區
  - 必要擴充
  - MySQL 連線
  - 上傳與報表資料夾
  - PHPMailer 檔案
  - GD 是否可用
  - SMTP 設定與連線
  - Apache / PHP log 路徑

如果想直接測試是否真的能寄信，可以按頁面上的 `寄信實測`。

## 重要設定位置

- 專案設定：[config.php](C:/Users/Xiao/Documents/New%20project/card_shop/config.php)
- 共用函式：[helpers.php](C:/Users/Xiao/Documents/New%20project/card_shop/helpers.php)
- 寄信邏輯：[mail_helpers.php](C:/Users/Xiao/Documents/New%20project/card_shop/mail_helpers.php)
- 樣式：[assets/style.css](C:/Users/Xiao/Documents/New%20project/card_shop/assets/style.css)
- PWA 快取：[sw.js](C:/Users/Xiao/Documents/New%20project/card_shop/sw.js)

## XAMPP 目前整理過的重點

- PHP
  - `display_errors=Off`
  - `log_errors=On`
  - `date.timezone=Asia/Taipei`
  - `extension_dir=C:\xampp\php\ext`
  - 上傳限制提高到 `64M`
- MySQL
  - 僅綁定 `127.0.0.1`
  - `max_allowed_packet=32M`
  - `max_connections=50`
- Apache / MySQL 啟停
  - 已修正 bat 腳本與啟動環境路徑
- phpMyAdmin
  - 改為 `cookie` 登入
- sendmail / SMTP
  - 已配置 Gmail SMTP 並完成實測

## log 位置

- Apache：`C:\xampp\apache\logs\error.log`
- PHP：`C:\xampp\php\logs\php_error_log`
- MySQL：`C:\xampp\mysql\data\mysql_error.log`
- sendmail：`C:\xampp\sendmail\error.log`

## mail 維護

- 專案寄信主要走 [mail_helpers.php](C:/Users/Xiao/Documents/New%20project/card_shop/mail_helpers.php)
- SMTP 設定在 [config.php](C:/Users/Xiao/Documents/New%20project/card_shop/config.php)
- 如果 Gmail 改密碼或重發應用程式密碼，要同步更新：
  - `SMTP_USERNAME`
  - `SMTP_PASSWORD`
  - `MAIL_FROM_ADDRESS`

## DB 維護

- migration：`http://localhost/card_shop/migrate_v2.php`
- phpMyAdmin：`http://localhost/phpmyadmin/`
- 主要表：
  - `users`
  - `products`
  - `product_images`
  - `orders`
  - `reviews`
  - `remember_tokens`

## GD 與圖片維護

- 上架浮水印邏輯：[upload_product.php](C:/Users/Xiao/Documents/New%20project/card_shop/upload_product.php)
- 圖表邏輯：[gd_chart.php](C:/Users/Xiao/Documents/New%20project/card_shop/gd_chart.php)
- 如果圖片處理壞掉，先看健康檢查頁面的 `GD Runtime`

## 常見問題處理

### 1. localhost 打不開

- 先執行：`C:\xampp\restart_card_shop_stack.bat`
- 再開：`http://localhost/card_shop/signin.php`

### 2. 畫面還是舊版

- 先按 `Ctrl + F5`
- 如果是安裝成 PWA，先關掉再重開
- 再看：`http://localhost/card_shop/health_check.php`

### 3. 圖片上傳失敗

- 看 `health_check.php` 的 `GD Runtime`
- 看 `C:\xampp\php\logs\php_error_log`
- 確認上傳檔案是有效 `jpg/png`

### 4. 寄信失敗

- 先按健康檢查頁的 `寄信實測`
- 再看：
  - `SMTP Config`
  - `SMTP Reachability`
  - `C:\xampp\sendmail\error.log`

### 5. DB 連不上

- 確認 `C:\xampp\mysql\data\mysql_error.log`
- 確認 `http://localhost/phpmyadmin/` 能開
- 再跑一次：`C:\xampp\restart_card_shop_stack.bat`

## 建議你之後的使用方式

1. 開發前先跑 `start_card_shop_stack.bat`
2. 改完功能先看 `health_check.php`
3. 再測 `signin.php`、`product_list.php`、`seller_dashboard.php`
4. 若有大改，再用 `restart_card_shop_stack.bat`
