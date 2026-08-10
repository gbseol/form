<?php
/**
 * =============================================================================
 * Computers - Print Details (browser printable page)
 * =============================================================================
 * Renders a clean, print-friendly detail sheet for one computer. The browser's
 * "Print / Save as PDF" function is triggered via the print toolbar.
 * =============================================================================
 */

$page_title = 'Computer Details - Print';

require_once __DIR__ . '/../includes/print_header.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = db()->prepare(
    "SELECT c.*, l.name AS lab_name
       FROM computers c
       LEFT JOIN labs l ON l.id = c.lab_id
      WHERE c.id = ?"
);
$stmt->execute([$id]);
$computer = $stmt->fetch();

if (!$computer) {
    echo '<div class="alert alert-danger">Computer not found.</div>';
    require_once __DIR__ . '/../includes/print_footer.php';
    exit;
}

$photoStmt = db()->prepare("SELECT * FROM computer_photos WHERE computer_id = ? ORDER BY is_primary DESC, id ASC");
$photoStmt->execute([$id]);
$photos = $photoStmt->fetchAll();

// Helper to render a printable detail row.
function print_row($label, $value) {
    $value = ($value === null || $value === '' || $value === 'No') ? '—' : e($value);
    echo '<tr><th>' . e($label) . '</th><td>' . $value . '</td></tr>';
}
?>

<h2><?php echo e($computer['computer_id']); ?> &mdash; Computer Details</h2>
<div class="print-meta">
    <strong>Status:</strong> <?php echo e($computer['status']); ?>
    &nbsp;&middot;&nbsp;
    <strong>Generated:</strong> <?php echo date('d M Y H:i:s'); ?>
    &nbsp;&middot;&nbsp;
    <strong>Printed by:</strong> <?php echo e(current_user()['full_name']); ?>
</div>

<?php if (count($photos) > 0): ?>
    <table class="print-detail-table">
        <tr>
            <?php foreach ($photos as $p): ?>
                <td style="width:150px;text-align:center;">
                    <img src="<?php echo uploads_url('computers/' . rawurlencode($p['filename'])); ?>"
                         style="max-width:140px;max-height:100px;object-fit:cover;border-radius:4px;" alt="photo">
                </td>
            <?php endforeach; ?>
        </tr>
    </table>
<?php endif; ?>

<div class="print-section-title">Basic Information</div>
<table class="print-detail-table">
    <?php
    print_row('Computer ID', $computer['computer_id']);
    print_row('Lab', $computer['lab_name']);
    print_row('Status', $computer['status']);
    ?>
</table>

<div class="print-section-title">Hardware</div>
<table class="print-detail-table">
    <?php
    print_row('CPU', $computer['cpu']);
    print_row('RAM', $computer['ram']);
    print_row('Storage Capacity', $computer['storage_capacity']);
    ?>
</table>

<div class="print-section-title">Parts</div>
<table class="print-detail-table">
    <?php
    print_row('CPU Condition', $computer['cpu_condition']);
    print_row('Monitor Condition', $computer['monitor_condition']);
    print_row('Keyboard Condition', $computer['keyboard_condition']);
    print_row('Mouse Condition', $computer['mouse_condition']);
    ?>
</table>

<?php if ($computer['remarks']): ?>
<div class="print-section-title">Remarks</div>
<p><?php echo nl2br(e($computer['remarks'])); ?></p>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/print_footer.php'; ?>
