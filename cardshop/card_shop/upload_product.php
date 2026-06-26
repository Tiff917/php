<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';
require_login(['seller']);

function gd_add_watermark(string $sourcePath, string $destinationPath): bool
{
    $mime = mime_content_type($sourcePath);
    if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
        return false;
    }

    $image = $mime === 'image/png' ? imagecreatefrompng($sourcePath) : imagecreatefromjpeg($sourcePath);
    if (!$image) {
        return false;
    }

    $width = imagesx($image);
    $height = imagesy($image);
    $font = first_existing_font();

    if ($font && function_exists('imagettftext')) {
        $shadow = imagecolorallocatealpha($image, 255, 255, 255, 85);
        $textColor = imagecolorallocatealpha($image, 180, 120, 92, 58);
        imagettftext($image, 14, 0, max(18, $width - 260), max(36, $height - 26), $shadow, $font, "T's cashop");
        imagettftext($image, 14, 0, max(16, $width - 262), max(34, $height - 28), $textColor, $font, "T's cashop");
    } else {
        $textColor = imagecolorallocatealpha($image, 180, 120, 92, 58);
        imagestring($image, 5, max(16, $width - 180), max(16, $height - 28), "T's cashop", $textColor);
    }

    $saved = $mime === 'image/png'
        ? imagepng($image, $destinationPath)
        : imagejpeg($image, $destinationPath, 90);

    imagedestroy($image);
    return $saved;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('seller_dashboard.php');
}

$groupName = trim((string) ($_POST['group_name'] ?? ''));
$memberName = trim((string) ($_POST['member_name'] ?? ''));
$albumName = trim((string) ($_POST['album_name'] ?? ''));
$cardVersion = trim((string) ($_POST['card_version'] ?? ''));
$cardCode = trim((string) ($_POST['card_code'] ?? ''));
$name = trim((string) ($_POST['name'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$price = (float) ($_POST['price'] ?? 0);
$stock = (int) ($_POST['stock'] ?? 0);
$conditionTags = trim((string) ($_POST['condition_tags'] ?? ''));

if ($groupName === '' || $name === '' || $description === '' || $price <= 0 || $stock < 0 || $conditionTags === '') {
    set_flash('flash_error', '請完整填寫商品資料。');
    redirect('seller_dashboard.php');
}

if (!isset($_FILES['images']) || !is_array($_FILES['images']['name'])) {
    set_flash('flash_error', '請至少上傳一張圖片。');
    redirect('seller_dashboard.php');
}

$filesCount = count($_FILES['images']['name']);
if ($filesCount < 1 || $filesCount > MAX_UPLOAD_IMAGES) {
    set_flash('flash_error', '圖片數量需介於 1 到 ' . MAX_UPLOAD_IMAGES . ' 張。');
    redirect('seller_dashboard.php');
}

$pdo = db();
$pdo->beginTransaction();

try {
    $status = $stock > 0 ? 'active' : 'sold_out';

    $stmt = $pdo->prepare(
        'INSERT INTO products
            (seller_id, name, description, price, stock, status, condition_tags, group_name, member_name, album_name, card_version, card_code, created_at, updated_at, sold_at)
         VALUES
            (:seller_id, :name, :description, :price, :stock, :status, :condition_tags, :group_name, :member_name, :album_name, :card_version, :card_code, NOW(), NOW(), :sold_at)'
    );
    $stmt->execute([
        'seller_id' => current_user()['id'],
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'stock' => $stock,
        'status' => $status,
        'condition_tags' => $conditionTags,
        'group_name' => $groupName,
        'member_name' => $memberName,
        'album_name' => $albumName,
        'card_version' => $cardVersion,
        'card_code' => $cardCode,
        'sold_at' => $status === 'sold_out' ? date('Y-m-d H:i:s') : null,
    ]);

    $productId = (int) $pdo->lastInsertId();
    $imageStmt = $pdo->prepare(
        'INSERT INTO product_images (product_id, image_path, is_primary, created_at)
         VALUES (:product_id, :image_path, :is_primary, NOW())'
    );

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    for ($i = 0; $i < $filesCount; $i++) {
        if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('有圖片上傳失敗，請重新選擇檔案。');
        }

        $tmpName = $_FILES['images']['tmp_name'][$i];
        if (!is_uploaded_file($tmpName)) {
            throw new RuntimeException('偵測到非合法上傳檔案。');
        }

        $mime = finfo_file($finfo, $tmpName) ?: mime_content_type($tmpName);
        if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
            throw new RuntimeException('只接受 JPG 或 PNG 圖片。');
        }

        $extension = $mime === 'image/png' ? 'png' : 'jpg';
        $filename = uniqid('card_' . $productId . '_', true) . '.' . $extension;
        $relativePath = UPLOAD_URL . '/products/' . $filename;
        $absolutePath = __DIR__ . '/' . $relativePath;

        if (!gd_add_watermark($tmpName, $absolutePath)) {
            throw new RuntimeException('圖片處理失敗，請確認 GD 已啟用。');
        }

        $imageStmt->execute([
            'product_id' => $productId,
            'image_path' => $relativePath,
            'is_primary' => $i === 0 ? 1 : 0,
        ]);
    }
    finfo_close($finfo);

    $pdo->commit();
    set_flash('flash_success', '商品上架成功，主圖已加上浮水印。');
} catch (Throwable $e) {
    $pdo->rollBack();
    set_flash('flash_error', '上架失敗：' . $e->getMessage());
}

redirect('seller_dashboard.php');
