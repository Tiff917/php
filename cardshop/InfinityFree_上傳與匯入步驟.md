# InfinityFree 上傳與匯入步驟

## 1. 上傳網站檔案

- 打開 InfinityFree File Manager
- 進入 `htdocs`
- 上傳 `card_shop` 資料夾內的所有內容到 `htdocs`
- 不要把最外層 `期末報告` 資料夾整包丟進去

## 2. 匯入資料庫

- 打開 `MySQL Databases`
- 點 `phpMyAdmin`
- 選擇資料庫 `if0_42221742_cardshop`
- 匯入 `card_shop.sql`

## 3. 如果匯入失敗

InfinityFree 常見情況是不能執行：

- `CREATE DATABASE`
- `USE card_shop`

如果遇到這種情況，請先把 SQL 檔最前面這兩段移除後再重新匯入：

- `CREATE DATABASE ...`
- `USE card_shop;`

## 4. 站點設定

目前部署版設定已改成：

- `APP_URL=https://cardshop.free.nf`
- `DB_HOST=sql305.infinityfree.com`
- `DB_NAME=if0_42221742_cardshop`
- `DB_USER=if0_42221742`

## 5. 上線後測試

- `https://cardshop.free.nf/signin.php`
- `https://cardshop.free.nf/register.php`
- `https://cardshop.free.nf/product_list.php`

## 6. 安全提醒

- 目前資料庫密碼已曝光，網站可用後建議立即更換資料庫密碼
- 改完後要同步更新：
  - `config.php`
  - `db_config.php`
