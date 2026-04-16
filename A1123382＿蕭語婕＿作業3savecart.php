<?php
// 1. 取得網址傳過來的商品編號與名稱
$id = $_GET['id'];
$name = $_GET['name'];

if (isset($id)) {
    // 2. 將商品存入 Cookie
    // 這裡我們用 cart[$id] 作為名稱，這樣可以存多個不同商品
    // time() + 3600 代表這台購物車可以在瀏覽器留一小時
    setcookie("cart[$id]", $name, time() + 3600, "/");
}

// 3. 存完後，立刻轉址到購物車清單頁
header("Location: shoppingcart.php");
exit;
?>
