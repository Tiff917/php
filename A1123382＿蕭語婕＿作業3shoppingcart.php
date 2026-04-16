<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>我的購物車</title>
    <style>
        .cart-item { padding: 15px; border-bottom: 1px solid #e0d5c1; color: #5d4037; }
        .empty-msg { color: #8d6e63; text-align: center; margin-top: 50px; }
    </style>
</head>
<body>
    <h1>🛒 您的購物清單</h1>

    <?php
    // 檢查是否有 cart 這個 Cookie 陣列
    if (isset($_COOKIE['cart'])) {
        foreach ($_COOKIE['cart'] as $id => $name) {
            echo "<div class='cart-item'>";
            echo "商品編號: " . htmlspecialchars($id) . " | ";
            echo "商品名稱: " . htmlspecialchars($name);
            echo " <a href='delete.php?id=$id'>[刪除]</a>";
            echo "</div>";
        }
    } else {
        echo "<p class='empty-msg'>購物車是空的喔，快去選購吧！</p>";
    }
    ?>

    <br>
    <a href="cartlog.php">← 繼續購物</a>
</body>
</html>
