<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$sellerId = (int) ($_GET['seller_id'] ?? 0);
$counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

$stmt = db()->prepare(
    'SELECT rating, COUNT(*) AS total
     FROM reviews
     WHERE seller_id = :seller_id
     GROUP BY rating'
);
$stmt->execute(['seller_id' => $sellerId]);

foreach ($stmt->fetchAll() as $row) {
    $counts[(int) $row['rating']] = (int) $row['total'];
}

$width = 360;
$height = 220;
$img = imagecreatetruecolor($width, $height);
$bg = imagecolorallocate($img, 255, 249, 243);
$text = imagecolorallocate($img, 99, 74, 60);
$bar = imagecolorallocate($img, 200, 144, 114);
$barSoft = imagecolorallocate($img, 236, 211, 194);

imagefilledrectangle($img, 0, 0, $width, $height, $bg);
imagestring($img, 5, 16, 14, 'Seller Rating Chart', $text);

$max = max(1, max($counts));
$y = 52;
for ($star = 5; $star >= 1; $star--) {
    imagestring($img, 4, 28, $y + 4, $star . ' star', $text);
    imagefilledrectangle($img, 100, $y, 320, $y + 22, $barSoft);
    $barWidth = (int) round(($counts[$star] / $max) * 220);
    imagefilledrectangle($img, 100, $y, 100 + $barWidth, $y + 22, $bar);
    imagestring($img, 4, 326, $y + 4, (string) $counts[$star], $text);
    $y += 30;
}

header('Content-Type: image/png');
imagepng($img);
imagedestroy($img);
