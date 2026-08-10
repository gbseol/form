<?php
/**
 * =============================================================================
 * Reports - CSV Export
 * =============================================================================
 * Exports any report as a CSV file. Includes a UTF-8 BOM so it opens with
 * correct character encoding in Microsoft Excel.
 *
 * Usage: export_csv.php?type=inventory|faulty|working|lab|activity[&lab_id=&from=&to=&user_id=&year=]
 * =============================================================================
 */

require_once __DIR__ . '/../includes/auth.php';
require_login();
require_can('export_reports');
require_once __DIR__ . '/_data.php';

$type = $_GET['type'] ?? 'inventory';

// Choose the data source and columns based on the report type.
if ($type === 'activity') {
    require_can('view_logs');
    $rows    = activity_report();
    $columns = activity_report_columns();
} else {
    $rows    = computer_report($type);
    $columns = computer_report_columns();
}

// Build a safe filename.
$map = [
    'inventory' => 'Inventory_Report',
    'faulty'    => 'Not_Working_PCs',
    'working'   => 'Working_PCs',
    'maintenance' => 'Has_Some_Issues_PCs',
    'lab'       => 'Lab_Report',
    'activity'  => 'User_Activity',
];
$filename = ($map[$type] ?? 'Report') . '_' . date('Y-m-d') . '.csv';

// Prevent any further output from breaking the file.
while (ob_get_level()) {
    ob_end_clean();
}

// Force download.
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');

$out = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility.
fwrite($out, "\xEF\xBB\xBF");

// Header row.
fputcsv($out, $columns);

// Data rows (fputcsv escapes commas / quotes correctly).
foreach ($rows as $row) {
    $values = [];
    foreach ($columns as $col) {
        $values[] = $row[$col] ?? '';
    }
    fputcsv($out, $values);
}

fclose($out);
exit;
