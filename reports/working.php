<?php
/**
 * =============================================================================
 * Reports - Working PCs
 * =============================================================================
 * List of all computers currently marked as Working.
 * =============================================================================
 */

$page_title = 'Working PCs Report';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/_data.php';

require_can('view_reports');

$type = 'working';
$rows = computer_report($type);
$reportTitle = 'Working PCs';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-check-circle me-2 text-success"></i>Working PCs</h4>
    <a href="<?php echo base_url('reports/index.php'); ?>" class="btn btn-outline-secondary btn-sm">Back</a>
</div>

<?php include __DIR__ . '/_computer_table.php'; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
