# T's cashop APP 完整介紹總整理

這份文件可直接作為期末報告、講稿母稿或 PPT 內容基礎，內容已依照系統架構、功能流程、技術實作、資料庫與部署方式完整整理。

## 第一部分：專案總覽

### 專案名稱

`T's cashop`

### 專案定位

T's cashop 是一個以韓系小卡交易為主題的 Web App。平台讓買家可以瀏覽、加入購物車、完成結帳與留下評價，也讓賣家可以上架多圖商品、查看每月銷售結果並匯出 PDF。整體介面採用年輕感的奶茶色系，並透過 PWA 與底部 tab 提升手機使用體驗。

### 主要目標

- 建立完整的會員、商品、交易、通知與評價流程
- 讓平台同時支援買家、賣家、管理員三種角色
- 提供接近手機 App 的畫面與操作感
- 支援正式展示與公開網域部署

### 技術堆疊

| 類別 | 技術 |
|------|------|
| 前端 | HTML5、CSS3、JavaScript |
| UI 風格 | 奶茶色系、單色背景、底部 tab、PWA |
| 後端 | PHP 8 |
| 資料庫 | MySQL / MariaDB |
| 圖片處理 | GD |
| 郵件通知 | PHPMailer |
| 匯出報表 | GD 生成 JPEG + PHP 組合 PDF |
| 部署 | XAMPP、InfinityFree |

## 第二部分：系統角色與權限

### 1. 管理員 Admin

- 查看管理頁面
- 管理平台整體商品狀態
- 可進入前台與後台頁面

### 2. 賣家 Seller

- 上架小卡商品
- 上傳多張商品圖片
- 自動加上浮水印
- 查看賣家儀表板
- 查看每月銷售統計
- 匯出每月銷售 PDF
- 收到商品售出通知信

### 3. 買家 Buyer

- 註冊登入
- 瀏覽首頁與商品列表
- 查看商品詳情
- 加入購物車
- 刪除購物車商品
- 完成結帳
- 收到訂單通知信
- 到會員中心查看訂單與評價

### 權限對照表

| 功能 | 管理員 | 賣家 | 買家 |
|------|--------|------|------|
| 註冊 / 登入 / 登出 | 是 | 是 | 是 |
| 修改會員資料 | 是 | 是 | 是 |
| 瀏覽商品 | 是 | 是 | 是 |
| 加入購物車 | 否 | 否 | 是 |
| 結帳購買 | 否 | 否 | 是 |
| 留下評價 | 否 | 否 | 是 |
| 上架商品 | 是 | 是 | 否 |
| 匯出月報 PDF | 是 | 是 | 否 |
| 管理商品狀態 | 是 | 部分 | 否 |

## 第三部分：資料庫設計

系統目前核心資料表共 6 張。

### 1. `users`

用途：儲存會員基本資料與角色資訊

主要欄位：

- `id`
- `username`
- `password_hash`
- `role`
- `display_name`
- `email`
- `phone`
- `favorite_group`
- `address`
- `last_login_at`
- `created_at`

### 2. `products`

用途：儲存小卡商品資料

主要欄位：

- `id`
- `seller_id`
- `name`
- `description`
- `price`
- `stock`
- `status`
- `condition_tags`
- `group_name`
- `member_name`
- `album_name`
- `card_version`
- `card_code`
- `created_at`
- `updated_at`
- `sold_at`

### 3. `product_images`

用途：儲存商品多圖資料

主要欄位：

- `id`
- `product_id`
- `image_path`
- `is_primary`
- `created_at`

### 4. `orders`

用途：儲存訂單與交易紀錄

主要欄位：

- `id`
- `product_id`
- `buyer_id`
- `seller_id`
- `quantity`
- `total_amount`
- `status`
- `created_at`
- `paid_at`
- `notification_sent_at`

### 5. `remember_tokens`

用途：儲存 Remember Me 的 selector / token hash 與有效時間

主要欄位：

- `id`
- `user_id`
- `selector`
- `token_hash`
- `expires_at`
- `created_at`
- `last_used_at`

### 6. `reviews`

用途：儲存訂單評價

主要欄位：

- `id`
- `order_id`
- `buyer_id`
- `seller_id`
- `rating`
- `comment`
- `created_at`

### 資料表關聯

- `users` 1 對多 `products`
- `products` 1 對多 `product_images`
- `users` 1 對多 `orders`，分別扮演買家與賣家
- `orders` 1 對 1 或 0 對 1 `reviews`
- `users` 1 對多 `remember_tokens`

## 第四部分：主要 PHP 頁面清單

| 頁面 | 功能 | 是否需登入 | 角色限制 |
|------|------|------------|----------|
| `signin.php` | 登入頁 | 否 | 全部 |
| `register.php` | 註冊頁 | 否 | 全部 |
| `index.php` | 首頁推薦商品 | 否 / 登入後體驗更完整 | 全部 |
| `product_list.php` | 商品列表 | 否 / 登入後可加入購物車 | 全部 |
| `product.php` | 商品詳情 | 否 / 登入後可加入購物車 | 全部 |
| `cart.php` | 購物車 | 是 | 買家 |
| `checkout.php` | 結帳處理與成功頁 | 是 | 買家 |
| `member_center.php` | 會員中心 | 是 | 全部 |
| `review.php` | 評價頁 | 是 | 買家 |
| `seller_dashboard.php` | 賣家上架與管理 | 是 | 賣家 / 管理員 |
| `monthly_report.php` | 每月銷售頁 | 是 | 賣家 / 管理員 |
| `monthly_report_pdf.php` | 匯出 PDF | 是 | 賣家 / 管理員 |
| `admin_dashboard.php` | 管理頁 | 是 | 管理員 |
| `health_check.php` | 環境健康檢查 | 否 | 開發維護用 |
| `migrate_v2.php` | 資料庫升級 | 否 | 開發維護用 |

## 第五部分：完整流程說明

### A. 買家購物流程

1. 使用者進入登入頁或註冊頁
2. 登入後進入首頁
3. 點選商品進入商品詳情
4. 按下「馬上加入購物車」
5. 前往購物車確認商品
6. 可刪除商品、返回繼續購物、或直接結帳
7. 結帳成功後看到成功頁
8. 系統建立訂單、更新庫存、必要時改為 `sold_out`
9. 系統寄送訂單通知信給買家與賣家
10. 買家可到會員中心查看訂單並留下評價

### B. 賣家上架流程

1. 賣家登入
2. 進入賣家中心
3. 填寫小卡名稱、團體、成員、價格、描述等資料
4. 上傳最多 5 張圖片
5. 前端即時預覽圖片
6. 後端使用 MIME 驗證圖片
7. 上傳成功後使用 GD 加入浮水印
8. 寫入 `products` 與 `product_images`
9. 商品出現在首頁或商品列表中

### C. 賣家月報流程

1. 賣家進入每月銷售頁
2. 選擇想查看的月份
3. 系統統計訂單數、售出張數、營收、平均客單
4. 顯示銷售明細
5. 點擊下載 PDF
6. 後端先用 GD 生成圖片
7. 再由 PHP 組合出 PDF 讓賣家下載

## 第六部分：安全機制

1. 密碼使用 `password_hash()` 儲存
2. 登入使用 `password_verify()`
3. Remember Me 採 selector + token hash 架構
4. 自動登入後會輪替 token，降低重放攻擊風險
5. 上傳圖片時使用 `mime_content_type()` 驗證真實類型
6. 使用 `is_uploaded_file()` 驗證上傳來源
7. 上傳檔名使用唯一值避免覆蓋
8. 所有需登入頁面都透過 `require_login()` 控制
9. 評價系統有防重複評價機制

## 第七部分：SOLD OUT 機制

### 觸發條件

- 當商品庫存扣到 0
- 系統會把 `products.status` 改成 `sold_out`
- 同時寫入 `sold_at`

### 前台呈現

- 首頁與商品列表顯示 `SOLD OUT`
- 已售完商品不可再加入購物車
- 商品詳情頁會顯示不可購買狀態

### 功能意義

- 避免重複販售
- 前台與後台狀態同步
- 統一庫存、訂單、展示頁行為

## 第八部分：設計規格

### 視覺風格

- 商店名稱：`T's cashop`
- 主文字色：`#665843`
- 聚焦邊線色：`#9c8867`
- 背景：奶茶色單色系，不使用漸層
- 導覽：底部 tab 風格

### 輸入欄位規則

- 所有輸入框聚焦後統一變成深咖啡色邊線
- 當前輸入欄位會顯示提示文字
- 元件上下留白加大，避免擁擠

### 圖片規格

- 商品最多 5 張圖
- 第一張可作為主圖
- 支援真實商品照
- 上架後自動加浮水印

## 第九部分：測試帳號

| 角色 | 帳號 | 密碼 |
|------|------|------|
| 管理員 | `admin` | `admin123` |
| 賣家 | `seller01` | `seller123` |
| 買家 | `buyer01` | `buyer123` |

## 第十部分：完整檔案重點結構

```text
card_shop/
├─ assets/
│  ├─ style.css
│  ├─ txt-main.png
│  ├─ txt-kai.png
│  └─ app-icon-*.png
├─ partials/
│  ├─ header.php
│  └─ footer.php
├─ vendor/phpmailer/
├─ uploads/
├─ signin.php
├─ register.php
├─ signin_process.php
├─ register_process.php
├─ signout.php
├─ index.php
├─ product_list.php
├─ product.php
├─ add_to_cart.php
├─ remove_from_cart.php
├─ cart.php
├─ checkout.php
├─ member_center.php
├─ update_profile.php
├─ review.php
├─ add_review.php
├─ seller_dashboard.php
├─ upload_product.php
├─ monthly_report.php
├─ monthly_report_pdf.php
├─ report_helpers.php
├─ admin_dashboard.php
├─ admin_panel.php
├─ admin_action.php
├─ helpers.php
├─ mail_helpers.php
├─ gd_chart.php
├─ check_remember.php
├─ migrate_v2.php
├─ health_check.php
├─ manifest.webmanifest
└─ sw.js
```

## 補充：PWA 與 App 化特色

- 可加入手機主畫面
- 使用 `manifest.webmanifest`
- 使用 `service worker`
- 首頁、商品、購物車、會員中心皆改為底部 tab 操作
- 介面盡量簡潔，保留每個流程的下一步與返回步驟

## 補充：對外部署資訊

- 公開網址：[https://cardshop.free.nf/signin.php](https://cardshop.free.nf/signin.php)
- 本機開發環境：XAMPP
- 線上部署平台：InfinityFree

## 補充：本次實作完成重點

- 已把首頁商品改為真實 TXT 小卡照片
- 已修正商品點擊 404 問題
- 已完成加入購物車、刪除商品、返回繼續購物、結帳流程
- 已完成會員中心整理
- 已完成結帳成功頁整理
- 已完成賣家月報與 PDF 匯出頁整理
- 已完成手機化底部 tab 與 PWA 基本結構

這份文件可直接延伸成簡報逐頁內容，也可以作為 Demo 講稿的完整基礎版本。
