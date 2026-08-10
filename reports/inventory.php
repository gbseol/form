<?php
/**
 * =============================================================================
 * Reports - Inventory
 * =============================================================================
 * Full inventory report (all computers) with year filter, print and exports.
 * =============================================================================
 */

$page_title = 'Inventory Report';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/_data.php';

require_can('view_reports');

$type = 'inventory';
$rows = computer_report($type);

$extraQuery = '';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0"><i class="bi bi-hdd-stack me-2"></i>Inventory Report</h4>
    <a href="<?php echo base_url('reports/index.php'); ?>" class="btn btn-outline-secondary btn-sm">Back</a>
</div>

<?php include __DIR__ . '/_computer_table.php'; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
