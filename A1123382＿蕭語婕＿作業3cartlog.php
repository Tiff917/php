<?php
session_start(); // 關鍵：一定要啟動才能讀取登入訊息

// 檢查是否有登入資訊
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['user_id'] : "";
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>購物網站</title>
    <style>
        h1 { 
            color: #333; 
            font-family: 'Arial', sans-serif;
            text-align: center;}
        h3 { 
            color: #333; 
            font-family: 'Arial', sans-serif;
            text-align: center;}
        /* 外層的大容器 */
        .product-container {
            display: grid;
            /* 關鍵：設定三欄，每欄寬度平均分配 (1fr 代表一份剩餘空間) */
            grid-template-columns: repeat(3, 1fr); 
            
            /* 設定格子之間的間距 */
            gap: 20px; 
            
            /* 讓整個 3x3 區域在頁面置中 */
            width: 700px; /* 大約 (200px * 3) + 間距 */
            margin: 40px auto;
        }

        .product {
            width: 200px;
            height: 200px;
            margin: 20px;
            border: 1.5px solid #e0d5c1;
            border-radius: 12px;
            background-color: #fff;
            
            /* 關鍵：設定為相對定位，作為內層絕對定位的基準點 */
            position: relative; 
            
            display: flex;
            justify-content: center;
            align-items: center;
            transition: transform 0.2s;
        }

        .product:hover {
            transform: translateY(-5px); /* 滑鼠經過時微微浮起 */
        }

        .add-btn {
            /* 關鍵：絕對定位 */
            position: absolute;
            bottom: 10px;  /* 距離底部 10px */
            right: 10px;   /* 距離右側 10px */
            
            width: 32px;
            height: 32px;
            border-radius: 50%; /* 圓形按鈕 */
            border: none;
            background-color: var(--accent-color, #c6a49a); /* 使用你的奶茶色 */
            color: white;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            
            /* 讓加號在圓圈內置中 */
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: background 0.3s;
        }

        .add-btn:hover {
            background-color: #b89388; /* 懸停時深一點 */
        }
    </style>
</head>
<body>
    <h1>購物網站</h1>
    <div style="text-align: center; margin-bottom: 20px;">
        <?php if (isset($_SESSION['user_id'])): ?>
            <h3 style="color: var(--sub-text);">
                你好，<?php echo htmlspecialchars($_SESSION['user_id']); ?> 
                (<?php echo $_SESSION['role']; ?>)！ 
                <a href="logout.php" style="font-size: 0.8rem; color: #888;">登出</a>
            </h3>
        <?php else: ?>
            <h3><a href="cartlogin.php">請先登入獲得更好的購物體驗</a></h3>
        <?php endif; ?>
    </div>

    <div class="product-container">
    <div class="product">
        <span class="product-info">apple</span>
            
        <a href="savecart.php?id=p001&name=apple" class="add-btn">+</a>
    </div>
    <div class="product">
        <span class="product-info">book</span>
            
        <a href="savecart.php?id=p002&name=book" class="add-btn">+</a>
    </div>
    <div class="product">
        <span class="product-info">cap</span>
            
        <a href="savecart.php?id=p003&name=cap" class="add-btn">+</a>
    </div><div class="product">
        <span class="product-info">dora</span>
            
        <a href="savecart.php?id=p004&name=dora" class="add-btn">+</a>
    </div><div class="product">
        <span class="product-info">earring</span>
            
        <a href="savecart.php?id=p005&name=earring" class="add-btn">+</a>
    </div><div class="product">
        <span class="product-info">fish</span>
            
        <a href="savecart.php?id=p006&name=fish" class="add-btn">+</a>
    </div><div class="product">
        <span class="product-info">guitar</span>
            
        <a href="savecart.php?id=p007&name=guitar" class="add-btn">+</a>
    </div><div class="product">
        <span class="product-info">hat</span>
            
        <a href="savecart.php?id=p008&name=hat" class="add-btn">+</a>
    </div><div class="product">
        <span class="product-info">ive</span>
            
        <a href="savecart.php?id=p009&name=ive" class="add-btn">+</a>
    </div>
</div>
</body>
</html>
