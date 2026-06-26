# T's cashop

T's cashop 是一個以年輕族群奶茶色系為主視覺的韓系小卡交易平台，採用 `PHP + MySQL + PWA` 架構，支援買家購物、賣家上架、管理員管理、圖片浮水印、訂單通知、評價系統與月報 PDF 匯出。

## 核心功能

- 會員系統：註冊、登入、登出、Remember Me、會員中心
- 商品交易：首頁推薦、商品列表、商品詳情、加入購物車、刪除商品、結帳
- 賣家功能：上架商品、多圖上傳、即時預覽、浮水印、每月銷售報表、PDF 匯出
- 評價功能：買家可針對完成訂單留下星級與文字評價
- 通知功能：結帳後寄送買家與賣家訂單通知信
- PWA：可加入手機主畫面，使用底部 tab 呈現 App 感

## 技術堆疊

- 前端：HTML5、CSS3、JavaScript、PWA
- 後端：PHP 8
- 資料庫：MySQL / MariaDB
- 圖片處理：GD
- 郵件：PHPMailer
- PDF：自製 JPEG 轉 PDF 匯出流程

## 本機執行

1. 將 `card_shop` 放入 XAMPP 的 `htdocs`
2. 開啟 Apache 與 MySQL
3. 確認 `php.ini` 已啟用 `extension=pdo_mysql` 與 `extension=gd`
4. 匯入 `card_shop.sql`，或先開 `http://localhost/card_shop/migrate_v2.php`
5. 開啟 `http://localhost/card_shop/signin.php`

## 測試帳號

- `admin / admin123`
- `seller01 / seller123`
- `buyer01 / buyer123`

## 重要提醒

- 正式寄信前請先修改 `config.php` 內的 `SMTP_USERNAME`、`SMTP_PASSWORD`、`MAIL_FROM_ADDRESS`
- 若要確認環境完整，可開啟 `health_check.php`
- 每月銷售 PDF 需要 `gd` 正常啟用

## 專案文件

- 完整介紹總整理：`C:\Users\Xiao\Documents\New project\期末報告\APP完整介紹總整理.md`
- 部署 SQL：`C:\Users\Xiao\Documents\New project\期末報告\card_shop_infinityfree.sql`
- 本機維護說明：`C:\Users\Xiao\Documents\New project\期末報告\card_shop\LOCAL_MAINTENANCE.md`
