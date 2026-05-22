<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>作業4 - 寄信控制台</title>
</head>
<body>
    <h2>✉️ 基本郵件寄送介面</h2>
    
    <form action="send_action.php" method="POST">
        <h3>1. 郵件內容設定</h3>
        <p>郵件主旨：<input type="text" name="mail_subject" style="width: 300px;" required></p>
        <p>郵件內容：<br>
           <textarea name="mail_content" rows="6" cols="40" required></textarea>
        </p>
        
        <hr>
        
        <h3>2. 寄送發送設定</h3>
        <p>
            發送模式：
            <select name="send_mode" id="send_mode" onchange="toggleRandomInput()">
                <option value="all">全部寄送</option>
                <option value="random">隨機寄送特定筆數</option>
            </select>
            <span id="random_count_box" style="display: none;">
                ，隨機筆數：<input type="number" name="random_limit" value="1" min="1" style="width: 50px;"> 筆
            </span>
        </p>
        
        <p>
            發送間隔時間：
            <input type="number" name="delay_seconds" value="2" min="0" style="width: 50px;"> 秒
        </p>
        
        <button type="submit" style="padding: 5px 15px; font-size: 1em;">🚀 開始發送郵件</button>
    </form>

    <br><br>
    <a href="index.php">⬅️ 返回新增收件者</a>

    <script>
        // 控制隨機筆數輸入框的顯示與隱藏
        function toggleRandomInput() {
            var mode = document.getElementById('send_mode').value;
            var randomBox = document.getElementById('random_count_box');
            if (mode === 'random') {
                randomBox.style.display = 'inline';
            } else {
                randomBox.style.display = 'none';
            }
        }
    </script>
</body>
</html>