<?php
/**
 * =============================================================================
 * Reports - Not Working PCs
 * =============================================================================
 * List of all computers currently marked as Not Working.
 * =============================================================================
 */

$page_title = 'Not Working PCs Report';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/_data.php';

require_can('view_reports');

$type = 'faulty';
$rows = computer_report($type);
$reportTitle = 'Not Working PCs';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-x-circle me-2 text-danger"></i>Not Working PCs</h4>
    <a href="<?php echo base_url('reports/index.php'); ?>" class="btn btn-outline-secondary btn-sm">Back</a>
</div>

<?php include __DIR__ . '/_computer_table.php'; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
