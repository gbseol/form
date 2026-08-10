<?php
/**
 * =============================================================================
 * Reports - PDF Export
 * =============================================================================
 * Exports any report as a downloadable PDF built with Dompdf. Tables are
 * rendered with full borders (proper boxes) and the issues report gets a
 * coloured title bar on top.
 *
 * Usage: export_pdf.php?type=inventory|faulty|working|lab|activity|issues[&lab_id=&from=&to=&user_id=&status=]
 * =============================================================================
 */

require_once __DIR__ . '/../includes/auth.php';
require_login();
require_can('export_reports');
require_once __DIR__ . '/_data.php';

$type = $_GET['type'] ?? 'inventory';

if ($type === 'issues') {
    $rows    = issues_report();
    $columns = issues_report_columns();
} elseif ($type === 'activity') {
    require_can('view_logs');
    $rows    = activity_report();
    $columns = activity_report_columns();
} else {
    $rows    = computer_report($type);
    $columns = computer_report_columns();
}

$map = [
    'inventory'   => 'Inventory_Report',
    'faulty'      => 'Not_Working_PCs',
    'working'     => 'Working_PCs',
    'maintenance' => 'Has_Some_Issues_PCs',
    'lab'         => 'Lab_Report',
    'activity'    => 'User_Activity',
    'issues'      => 'Issues_History',
];
$filename = ($map[$type] ?? 'Report') . '_' . date('Y-m-d') . '.pdf';

// Build the HTML document.
$html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
    body { font-family: sans-serif; font-size: 9pt; margin: 20px; }
    h1.title { background-color: #008080; color: #ffffff; text-align: center;
               font-size: 16pt; font-weight: bold; padding: 10px; margin: 0 0 12px 0; }
    table.report { border-collapse: collapse; width: 100%; }
    table.report th, table.report td { border: 1px solid #000000; padding: 4px 6px;
               vertical-align: top; word-wrap: break-word; }
    table.report th { background-color: #808080; color: #ffffff;
               font-weight: bold; text-align: center; }
</style></head><body>';

// Title bar (issues export only).
if ($type === 'issues') {
    $html .= '<h1 class="title">Computer Issue Report</h1>';
}

$html .= '<table class="report"><thead><tr>';
foreach ($columns as $col) {
    $html .= '<th>' . e($col) . '</th>';
}
$html .= '</tr></thead><tbody>';

foreach ($rows as $row) {
    $html .= '<tr>';
    foreach ($columns as $col) {
        $html .= '<td>' . nl2br(e($row[$col] ?? '')) . '</td>';
    }
    $html .= '</tr>';
}

$html .= '</tbody></table></body></html>';

require_once '/usr/share/php/dompdf/autoload.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf([
    'isRemoteEnabled' => false,
    'chroot'          => realpath(__DIR__ . '/..'),
    'fontDir'         => '/var/cache/php-dompdf/fonts',
    'fontCache'       => '/var/cache/php-dompdf/fonts',
]);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

while (ob_get_level()) {
    ob_end_clean();
}

$dompdf->stream($filename, ['Attachment' => true]);
exit;
