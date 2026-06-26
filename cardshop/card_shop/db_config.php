<?php
$host = "sql305.infinityfree.com";
$user = "if0_42221742";
$pass = "ipiYSC3aivZZuy";
$db   = "if0_42221742_cardshop";

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("DB 連線失敗：" . $conn->connect_error);
}
?>
