<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';
require_once __DIR__ . '/report_helpers.php';
require_login(['seller']);

$month = preg_match('/^\d{4}-\d{2}$/', (string) ($_GET['month'] ?? '')) ? (string) $_GET['month'] : date('Y-m');
$sellerId = (int) current_user()['id'];
$seller = fetch_user_by_id($sellerId);
$summary = monthly_sales_summary($sellerId, $month);
$orders = seller_monthly_orders($sellerId, $month);
$groupStats = seller_monthly_group_revenue($sellerId, $month);
$dailyStats = seller_monthly_daily_orders($sellerId, $month);

$safeMonth = str_replace('-', '', $month);
$jpgPath = REPORT_DIR . '/seller_' . $sellerId . '_' . $safeMonth . '.jpg';
$pdfPath = REPORT_DIR . '/seller_' . $sellerId . '_' . $safeMonth . '.pdf';

generate_sales_report_jpeg($seller ?? [], $month, $summary, $orders, $groupStats, $dailyStats, $jpgPath);
build_pdf_from_jpeg($jpgPath, $pdfPath);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="ts-cashop-monthly-report-' . $safeMonth . '.pdf"');
readfile($pdfPath);
exit;
