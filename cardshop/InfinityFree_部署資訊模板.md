# InfinityFree 部署資訊模板

目前已確認的 InfinityFree 資訊如下：

- Hosting Account Username：`if0_42221742`
- Domain：
  - `cardshop.free.nf`
- DB_HOST：`sql305.infinityfree.com`
- DB_NAME：`if0_42221742_cardshop`
- DB_USER：`if0_42221742`
- DB_PASSWORD：`ipiYSC3aivZZuy`
- FTP Host：
- FTP Username：
- FTP Password：

## 部署完成後要改的檔案

- `card_shop/config.php`
  - `DB_HOST`
  - `DB_NAME`
  - `DB_USER`
- `DB_PASS`
- `SMTP_USERNAME`
- `SMTP_PASSWORD`
- `MAIL_FROM_ADDRESS`

## 安全提醒

- 你這組資料庫密碼已經出現在截圖裡，部署完成後建議立刻改掉，並同步更新 `config.php` 與 `db_config.php`。
