<?php
/**
 * =============================================================================
 * Reports - Lab Report
 * =============================================================================
 * List of all computers in a chosen lab.
 * =============================================================================
 */

$page_title = 'Lab Report';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/_data.php';

require_can('view_reports');

$labId = (int)($_GET['lab_id'] ?? 0);

$stmt = db()->prepare("SELECT * FROM labs WHERE id = ?");
$stmt->execute([$labId]);
$lab = $stmt->fetch();

if (!$lab) {
    set_flash('danger', 'Please select a valid lab.');
    redirect('reports/index.php');
}

$type = 'lab';
$rows = computer_report($type);
$reportTitle = 'Lab Report: ' . $lab['name'];
$extraQuery = '&lab_id=' . (int)$lab['id'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-building me-2"></i>Lab Report: <?php echo e($lab['name']); ?></h4>
    <div class="d-flex gap-2">
        <form method="get" action="<?php echo base_url('reports/lab.php'); ?>" class="d-flex gap-2">
            <select name="lab_id" class="form-select form-select-sm">
                <?php
                $allLabs = db()->query("SELECT id, name FROM labs ORDER BY name")->fetchAll();
                foreach ($allLabs as $l):
                ?>
                    <option value="<?php echo (int)$l['id']; ?>" <?php echo $labId === (int)$l['id'] ? 'selected' : ''; ?>><?php echo e($l['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-outline-primary btn-sm" type="submit">Go</button>
        </form>
        <a href="<?php echo base_url('reports/index.php'); ?>" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>
</div>

<?php include __DIR__ . '/_computer_table.php'; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
