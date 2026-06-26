# 期末報告｜T's cashop

T's cashop 是一個以 PHP + MySQL + XAMPP 建立的韓系小卡交易平台，主打年輕感奶茶色 UI、PWA 手機化操作流程、完整會員系統、商品交易、郵件通知與每月銷售 PDF 匯出。

## 專案內容

- `card_shop/`：網站完整原始碼
- `card_shop.sql`：資料庫匯出檔
- `Demo_影片講稿.md`：錄影時可直接照著講
- `Canva_口頭報告版講稿.md`：投影片逐頁口頭報告版本
- `交付說明.md`：交付重點整理

## 功能重點

- 會員註冊、登入、登出、Remember Me
- 買家購物車、結帳、評價
- 賣家上架小卡、多圖預覽、浮水印
- 商品卡況標籤、團體名稱、價格、售出狀態管理
- 賣出後寄送通知信給買家與賣家
- 每月銷售報表匯出 PDF
- PWA 安裝與底部 Tab 手機化介面

## 本機啟動方式

1. 把 `card_shop` 放到 XAMPP 的 `htdocs`
2. 啟動 Apache 與 MySQL
3. 匯入 `card_shop.sql` 或開啟 `http://localhost/card_shop/migrate_v2.php`
4. 開啟 `http://localhost/card_shop/signin.php`

## 注意事項

- `card_shop/config.php` 內 SMTP 目前是範例值，要改成自己的寄信帳號
- PHP 需啟用 `pdo_mysql` 與 `gd`
- 若要檢查環境，可開 `http://localhost/card_shop/health_check.php`
