<?php
function make_icon(string $path, int $size): void {
    $img = imagecreatetruecolor($size, $size);
    imagesavealpha($img, true);
    $bg = imagecolorallocate($img, 245, 239, 232);
    $panel = imagecolorallocate($img, 255, 250, 246);
    $line = imagecolorallocate($img, 216, 195, 181);
    $accent = imagecolorallocate($img, 191, 143, 116);
    $accentStrong = imagecolorallocate($img, 167, 115, 87);
    imagefilledrectangle($img, 0, 0, $size, $size, $bg);
    imagefilledroundedrectangle($img, (int)($size * 0.18), (int)($size * 0.16), (int)($size * 0.82), (int)($size * 0.84), (int)($size * 0.1), $panel);
    imagefilledellipse($img, (int)($size * 0.5), (int)($size * 0.42), (int)($size * 0.34), (int)($size * 0.34), $accent);
    imagefilledrectangle($img, (int)($size * 0.32), (int)($size * 0.66), (int)($size * 0.68), (int)($size * 0.71), $accentStrong);
    imagepng($img, $path);
    imagedestroy($img);
}
if (!function_exists('imagefilledroundedrectangle')) {
    function imagefilledroundedrectangle($im, $x1, $y1, $x2, $y2, $radius, $color) {
        imagefilledrectangle($im, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        imagefilledrectangle($im, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
        imagefilledellipse($im, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($im, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($im, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($im, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    }
}
make_icon('C:/Users/Xiao/Documents/New project/card_shop/assets/app-icon-192.png', 192);
make_icon('C:/Users/Xiao/Documents/New project/card_shop/assets/app-icon-512.png', 512);
?>
