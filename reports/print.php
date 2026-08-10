<?php
/**
 * =============================================================================
 * Reports - Printable Page (browser Print / Save as PDF)
 * =============================================================================
 * Renders a print-friendly version of any report. The browser's print dialog
 * is invoked via the print toolbar (window.print()).
 *
 * Usage: print.php?type=inventory|faulty|working|lab|activity[&lab_id=&from=&to=&user_id=&year=]
 * =============================================================================
 */

require_once __DIR__ . '/../includes/auth.php';
require_login();
require_can('view_reports');
require_once __DIR__ . '/_data.php';

$type = $_GET['type'] ?? 'inventory';
$site_name = get_setting('site_name', 'Computer Lab Management System');

$titles = [
    'inventory'   => 'Inventory Report',
    'faulty'      => 'Not Working PCs',
    'working'     => 'Working PCs',
    'maintenance' => 'Has Some Issues PCs',
    'lab'         => 'Lab Report',
    'activity'    => 'User Activity',
    'issues'      => 'Issues History',
];
$title = $titles[$type] ?? 'Report';

// If a specific lab was requested, include its name in the title.
if ($type === 'lab' && !empty($_GET['lab_id'])) {
    $ls = db()->prepare("SELECT name FROM labs WHERE id = ?");
    $ls->execute([(int)$_GET['lab_id']]);
    $labName = $ls->fetchColumn();
    if ($labName) {
        $title .= ': ' . $labName;
    }
}

$page_title = $title . ' - Print';
require_once __DIR__ . '/../includes/print_header.php';

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
?>

<h1 class="report-title"><?php echo e($title); ?></h1>
<div class="print-meta">
    <strong>Generated:</strong> <?php echo date('d M Y H:i:s'); ?>
    &nbsp;&middot;&nbsp;
    <strong>Printed by:</strong> <?php echo e(current_user()['full_name']); ?>
    &nbsp;&middot;&nbsp;
    <strong>Records:</strong> <?php echo count($rows); ?>
</div>

<?php
$colWidths  = report_col_widths($columns);
$totalWidth = array_sum($colWidths) ?: 1;
?>

<table class="print-table">
    <thead>
        <tr>
            <?php foreach ($columns as $i => $col): ?>
                <th style="width: <?php echo round(($colWidths[$i] / $totalWidth) * 100, 2); ?>%;"><?php echo e($col); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (count($rows) === 0): ?>
            <tr><td colspan="<?php echo count($columns); ?>" style="text-align:center;padding:1rem;">No records found.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $row): ?>
            <tr>
                <?php foreach ($columns as $col): ?>
                    <td class="<?php echo ($col === 'Status') ? 'status-' . strtolower(str_replace(' ', '-', $row[$col] ?? '')) : ''; ?>">
                        <?php echo e($row[$col] ?? ''); ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/print_footer.php'; ?>
