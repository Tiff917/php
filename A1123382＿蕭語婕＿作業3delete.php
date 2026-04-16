<?php
// 1. 取得要刪除的商品 ID
$id = $_GET['id'] ?? "";

if ($id !== "") {
    /* 2. 刪除 Cookie 的秘訣：
       設定一個「過去的時間」。
       我們將過期時間設定為 time() - 3600 (即一小時前)，
       瀏覽器發現它已經過期了，就會自動把它從電腦裡刪除。
    */
    setcookie("cart[$id]", "", time() - 3600, "/");
}

// 3. 刪除完畢後，跳轉回購物車頁面
header("Location: shoppingcart.php");
exit;
?>
