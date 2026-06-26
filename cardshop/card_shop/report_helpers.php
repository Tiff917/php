<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function report_safe_text(string $text): string
{
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $text = preg_replace("/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F\\x7F]/u", '', $text) ?? $text;
    $text = preg_replace("/[ \t]+/u", ' ', $text) ?? $text;
    $text = preg_replace("/\n{2,}/u", "\n", $text) ?? $text;
    $text = trim($text);

    return $text !== '' ? $text : 'N/A';
}

function report_text($image, int $size, int $x, int $y, string $text, array $rgb, ?string $font): void
{
    $color = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
    $safeText = report_safe_text($text);
    if ($font && function_exists('imagettftext')) {
        $result = @imagettftext($image, $size, 0, $x, $y, $color, $font, $safeText);
        if ($result !== false) {
            return;
        }
    }

    // Chinese report text must be rendered with a real TTF/TTC font.
    if (preg_match('/[\p{Han}]/u', $safeText)) {
        throw new RuntimeException('月報表找不到可用的中文字型，請確認 assets/fonts/kaiu.ttf 存在。');
    }

    if ($font) {
        imagestring($image, 5, $x, $y - 16, $safeText, $color);
        return;
    }

    imagestring($image, 5, $x, $y - 16, $safeText, $color);
}

function report_round_rect($image, int $x1, int $y1, int $x2, int $y2, int $radius, int $fillColor): void
{
    imagefilledrectangle($image, $x1 + $radius, $y1, $x2 - $radius, $y2, $fillColor);
    imagefilledrectangle($image, $x1, $y1 + $radius, $x2, $y2 - $radius, $fillColor);
    imagefilledellipse($image, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $fillColor);
    imagefilledellipse($image, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $fillColor);
    imagefilledellipse($image, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $fillColor);
    imagefilledellipse($image, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $fillColor);
}

function report_stat_card($image, int $x, int $y, int $w, int $h, string $label, string $value, ?string $font): void
{
    $card = imagecolorallocate($image, 255, 249, 244);
    $labelColor = [140, 110, 94];
    $valueColor = [92, 70, 58];
    report_round_rect($image, $x, $y, $x + $w, $y + $h, 24, $card);
    report_text($image, 13, $x + 24, $y + 38, $label, $labelColor, $font);
    report_text($image, 22, $x + 24, $y + 78, $value, $valueColor, $font);
}

function report_group_cards(array $groupStats): array
{
    return array_slice($groupStats, 0, 4);
}

function report_recent_orders(array $orders): array
{
    return array_slice($orders, 0, 12);
}

function report_draw_daily_bars($image, int $x, int $y, int $w, int $h, array $dailyStats, ?string $font): void
{
    $track = imagecolorallocate($image, 232, 221, 210);
    $fill = imagecolorallocate($image, 156, 136, 103);
    $textColor = [116, 92, 79];

    report_round_rect($image, $x, $y, $x + $w, $y + $h, 22, imagecolorallocate($image, 255, 249, 244));
    report_text($image, 15, $x + 24, $y + 34, '每日訂單', $textColor, $font);

    if ($dailyStats === []) {
        report_text($image, 13, $x + 24, $y + 74, '這個月份還沒有每日訂單資料。', $textColor, $font);
        return;
    }

    $maxOrders = max(1, ...array_map(static fn(array $row): int => (int) $row['order_count'], $dailyStats));
    $count = count($dailyStats);
    $gap = 12;
    $chartX = $x + 24;
    $chartY = $y + 118;
    $chartHeight = 110;
    $barWidth = max(10, (int) floor(($w - 48 - (($count - 1) * $gap)) / max(1, $count)));

    foreach ($dailyStats as $index => $row) {
        $barHeight = max(14, (int) round((((int) $row['order_count']) / $maxOrders) * $chartHeight));
        $barX = $chartX + ($index * ($barWidth + $gap));
        $barY = $chartY + ($chartHeight - $barHeight);
        imagefilledrectangle($image, $barX, $chartY, $barX + $barWidth, $chartY + $chartHeight, $track);
        imagefilledrectangle($image, $barX, $barY, $barX + $barWidth, $chartY + $chartHeight, $fill);
        report_text($image, 10, $barX - 1, $chartY + $chartHeight + 26, substr((string) $row['order_date'], 8, 2), $textColor, $font);
    }
}

function report_draw_group_rankings($image, int $x, int $y, int $w, int $h, array $groupStats, ?string $font): void
{
    $panelColor = imagecolorallocate($image, 255, 249, 244);
    $track = imagecolorallocate($image, 232, 221, 210);
    $fill = imagecolorallocate($image, 102, 88, 67);
    $textColor = [116, 92, 79];
    $darkText = [92, 70, 58];

    report_round_rect($image, $x, $y, $x + $w, $y + $h, 22, $panelColor);
    report_text($image, 15, $x + 24, $y + 34, '團體營收排行', $textColor, $font);

    if ($groupStats === []) {
        report_text($image, 13, $x + 24, $y + 74, '目前還沒有排行資料。', $textColor, $font);
        return;
    }

    $maxRevenue = max(1.0, ...array_map(static fn(array $row): float => (float) $row['total_revenue'], $groupStats));
    $cursorY = $y + 76;
    foreach (report_group_cards($groupStats) as $index => $row) {
        $rank = (string) ($index + 1);
        $name = report_safe_text((string) ($row['group_name'] !== '' ? $row['group_name'] : '未分類'));
        $amount = format_currency((float) $row['total_revenue']);
        $ordersLabel = (int) $row['order_count'] . ' 種商品 / ' . (int) $row['total_cards'] . ' 張';
        $barWidth = (int) round((((float) $row['total_revenue']) / $maxRevenue) * 230);

        report_round_rect($image, $x + 24, $cursorY, $x + 80, $cursorY + 48, 18, imagecolorallocate($image, 241, 229, 216));
        report_text($image, 16, $x + 45, $cursorY + 31, $rank, $darkText, $font);
        report_text($image, 14, $x + 96, $cursorY + 20, $name, $darkText, $font);
        report_text($image, 11, $x + 96, $cursorY + 43, $ordersLabel, $textColor, $font);
        imagefilledrectangle($image, $x + 360, $cursorY + 12, $x + 360 + 230, $cursorY + 28, $track);
        imagefilledrectangle($image, $x + 360, $cursorY + 12, $x + 360 + max(18, $barWidth), $cursorY + 28, $fill);
        report_text($image, 13, $x + 610, $cursorY + 23, $amount, $darkText, $font);
        $cursorY += 64;
    }
}

function report_table_row_text(array $order): array
{
    return [
        substr((string) $order['created_at'], 0, 10),
        report_safe_text(trim((string) $order['group_name'] . ' / ' . (string) $order['member_name'])),
        report_safe_text((string) $order['product_name']),
        report_safe_text((string) $order['buyer_name']),
        format_currency((float) $order['total_amount']),
    ];
}

function generate_sales_report_jpeg(
    array $seller,
    string $month,
    array $summary,
    array $orders,
    array $groupStats,
    array $dailyStats,
    string $targetPath
): void {
    $font = first_existing_font();
    $recentOrders = report_recent_orders($orders);

    $width = 1400;
    $height = 1500;
    $image = imagecreatetruecolor($width, $height);

    $bg = imagecolorallocate($image, 247, 240, 233);
    $panel = imagecolorallocate($image, 255, 251, 247);
    $accent = imagecolorallocate($image, 198, 145, 111);
    $text = [92, 70, 58];
    $muted = [130, 106, 93];
    $line = imagecolorallocate($image, 229, 216, 204);

    imagefilledrectangle($image, 0, 0, $width, $height, $bg);
    report_round_rect($image, 48, 40, $width - 48, $height - 40, 34, $panel);
    report_round_rect($image, 76, 70, $width - 76, 218, 30, $accent);

    report_text($image, 28, 110, 134, "T's cashop 月銷售報表", [255, 255, 255], $font);
    report_text($image, 14, 110, 174, '月份：' . $month, [255, 246, 240], $font);
    report_text($image, 14, 930, 134, '賣家：' . (string) ($seller['display_name'] ?? 'Seller'), [255, 255, 255], $font);
    report_text($image, 14, 930, 174, '統計月份：' . $month, [255, 246, 240], $font);

    report_stat_card($image, 84, 252, 286, 118, '售出商品數', (string) ($summary['total_orders'] ?? 0), $font);
    report_stat_card($image, 388, 252, 286, 118, '售出張數', (string) ($summary['total_cards'] ?? 0), $font);
    report_stat_card($image, 692, 252, 286, 118, '本月營收', format_currency((float) ($summary['total_revenue'] ?? 0)), $font);
    $avgOrder = (int) ($summary['total_orders'] ?? 0) > 0
        ? format_currency((float) $summary['total_revenue'] / (int) $summary['total_orders'])
        : format_currency(0);
    report_stat_card($image, 996, 252, 286, 118, '平均客單價', $avgOrder, $font);

    report_draw_group_rankings($image, 84, 406, 614, 342, $groupStats, $font);
    report_draw_daily_bars($image, 716, 406, 566, 342, $dailyStats, $font);

    report_round_rect($image, 84, 782, $width - 84, $height - 86, 24, imagecolorallocate($image, 255, 249, 244));
    report_text($image, 18, 112, 822, '最近訂單明細', $muted, $font);
    report_text($image, 12, 112, 854, '顯示本月最新 12 筆訂單。', $muted, $font);

    imageline($image, 112, 892, $width - 112, 892, $line);
    report_text($image, 12, 112, 926, '日期', $muted, $font);
    report_text($image, 12, 250, 926, '團體 / 成員', $muted, $font);
    report_text($image, 12, 555, 926, '商品', $muted, $font);
    report_text($image, 12, 980, 926, '買家', $muted, $font);
    report_text($image, 12, 1140, 926, '金額', $muted, $font);

    $rowY = 972;
    foreach ($recentOrders as $order) {
        imageline($image, 112, $rowY - 16, $width - 112, $rowY - 16, $line);
        [$date, $groupMember, $product, $buyer, $amount] = report_table_row_text($order);
        report_text($image, 11, 112, $rowY, $date, $text, $font);
        report_text($image, 11, 250, $rowY, $groupMember, $text, $font);
        report_text($image, 11, 555, $rowY, $product, $text, $font);
        report_text($image, 11, 980, $rowY, $buyer, $text, $font);
        report_text($image, 11, 1140, $rowY, $amount, $text, $font);
        $rowY += 46;
    }

    imagejpeg($image, $targetPath, 92);
    imagedestroy($image);
}

function build_pdf_from_jpeg(string $jpegPath, string $pdfPath): void
{
    $jpeg = file_get_contents($jpegPath);
    if ($jpeg === false) {
        throw new RuntimeException('Unable to read the report image.');
    }

    [$widthPx, $heightPx] = getimagesize($jpegPath);
    $pageWidth = 595.28;
    $pageHeight = ($heightPx / $widthPx) * $pageWidth;

    $objects = [];
    $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
    $objects[] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
    $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$pageWidth} {$pageHeight}] /Resources << /XObject << /Im0 4 0 R >> >> /Contents 5 0 R >>";
    $objects[] = "<< /Type /XObject /Subtype /Image /Width {$widthPx} /Height {$heightPx} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($jpeg) . " >>\nstream\n{$jpeg}\nendstream";

    $content = "q\n{$pageWidth} 0 0 {$pageHeight} 0 0 cm\n/Im0 Do\nQ";
    $objects[] = "<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream";

    $pdf = "%PDF-1.4\n";
    $offsets = [];
    foreach ($objects as $index => $object) {
        $offsets[] = strlen($pdf);
        $pdf .= ($index + 1) . " 0 obj\n{$object}\nendobj\n";
    }

    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    foreach ($offsets as $offset) {
        $pdf .= sprintf("%010d 00000 n \n", $offset);
    }
    $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n{$xref}\n%%EOF";

    file_put_contents($pdfPath, $pdf);
}
