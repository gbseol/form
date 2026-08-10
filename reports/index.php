<?php
/**
 * =============================================================================
 * Reports - Hub
 * =============================================================================
 * Entry point for all reports: inventory, faulty, working, lab, activity,
 * plus CSV / Excel exports and printable pages. Super Admin and Admin only.
 * =============================================================================
 */

$page_title = 'Reports';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/_data.php';

require_can('view_reports');

$counts = [
    'inventory'    => (int)db()->query("SELECT COUNT(*) FROM computers")->fetchColumn(),
    'not_working'  => (int)db()->query("SELECT COUNT(*) FROM computers WHERE status = 'Not Working'")->fetchColumn(),
    'working'      => (int)db()->query("SELECT COUNT(*) FROM computers WHERE status = 'Working'")->fetchColumn(),
    'some_issues'  => (int)db()->query("SELECT COUNT(*) FROM computers WHERE status = 'Has Some Issues'")->fetchColumn(),
];

$labs = db()->query("SELECT id, name FROM labs ORDER BY name")->fetchAll();

// A single base URL that holds the current filter set.
$base = base_url('reports/');

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Reports &amp; Exports</h4>
    <a href="<?php echo base_url('computers/index.php'); ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to computers</a>
</div>

<div class="row g-3">
    <!-- Inventory -->
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <h5><i class="bi bi-hdd-stack me-1 text-primary"></i>Inventory Report</h5>
                <p class="text-muted">All computers (<?php echo $counts['inventory']; ?> records) with every hardware and software detail.</p>
                <a href="<?php echo $base; ?>inventory.php" class="btn btn-sm btn-primary"><i class="bi bi-eye me-1"></i>View</a>
                <a href="<?php echo $base; ?>print.php?type=inventory" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-printer me-1"></i>Print</a>
            </div>
        </div>
    </div>

    <!-- Not Working -->
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <h5><i class="bi bi-x-circle me-1 text-danger"></i>Not Working PCs</h5>
                <p class="text-muted">All computers with status "Not Working" (<?php echo $counts['not_working']; ?> records) for service planning.</p>
                <a href="<?php echo $base; ?>faulty.php" class="btn btn-sm btn-danger"><i class="bi bi-eye me-1"></i>View</a>
                <a href="<?php echo $base; ?>print.php?type=faulty" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-printer me-1"></i>Print</a>
            </div>
        </div>
    </div>

    <!-- Working -->
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <h5><i class="bi bi-check-circle me-1 text-success"></i>Working PCs</h5>
                <p class="text-muted">All computers with status "Working" (<?php echo $counts['working']; ?> records).</p>
                <a href="<?php echo $base; ?>working.php" class="btn btn-sm btn-success"><i class="bi bi-eye me-1"></i>View</a>
                <a href="<?php echo $base; ?>print.php?type=working" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-printer me-1"></i>Print</a>
            </div>
        </div>
    </div>

    <!-- Lab report -->
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <h5><i class="bi bi-building me-1 text-primary"></i>Lab Report</h5>
                <p class="text-muted">Computers of one specific lab. Choose a lab below.</p>
                <form method="get" action="<?php echo $base; ?>lab.php" class="d-flex gap-2">
                    <select name="lab_id" class="form-select form-select-sm" required>
                        <option value="">Select lab...</option>
                        <?php foreach ($labs as $lab): ?>
                            <option value="<?php echo (int)$lab['id']; ?>"><?php echo e($lab['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-eye"></i></button>
                </form>
            </div>
        </div>
    </div>

    <!-- Activity -->
    <?php if (can('view_logs')): ?>
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <h5><i class="bi bi-journal-text me-1 text-primary"></i>User Activity</h5>
                <p class="text-muted">Audit trail of every action: user, date, time, IP and old/new values.</p>
                <a href="<?php echo $base; ?>activity.php" class="btn btn-sm btn-primary"><i class="bi bi-eye me-1"></i>View</a>
                <a href="<?php echo $base; ?>print.php?type=activity" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-printer me-1"></i>Print</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Has Some Issues -->
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <h5><i class="bi bi-tools me-1 text-warning"></i>Has Some Issues</h5>
                <ul class="list-unstyled mb-3">
                    <li>Computers with minor problems: <strong><?php echo $counts['some_issues']; ?></strong></li>
                </ul>
                <a href="<?php echo $base; ?>inventory.php?status=Has%20Some%20Issues" class="btn btn-sm btn-outline-primary">Issue List</a>
            </div>
        </div>
    </div>

    <!-- Issues history -->
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <h5><i class="bi bi-journal-check me-1 text-success"></i>Issues History</h5>
                <p class="text-muted">Every reported issue: date &amp; time, lab, computer, reporter, current status and solved by.</p>
                <a href="<?php echo base_url('issues/index.php'); ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>View</a>
                <a href="<?php echo $base; ?>export_excel.php?type=issues" class="btn btn-sm btn-success"><i class="bi bi-file-earmark-excel me-1"></i>Export Excel</a>
                <a href="<?php echo $base; ?>export_pdf.php?type=issues" class="btn btn-sm btn-danger"><i class="bi bi-file-earmark-pdf me-1"></i>Export PDF</a>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header"><i class="bi bi-download me-1"></i>Exports</div>
    <div class="card-body">
        <p class="text-muted mb-3">Download the reports in spreadsheet format. CSV and Excel (.xlsx) files open directly in Microsoft Excel.</p>
        <div class="row g-2">
            <div class="col-md-3">
                <a href="<?php echo $base; ?>export_csv.php?type=inventory" class="btn btn-outline-success w-100"><i class="bi bi-filetype-csv me-1"></i>Inventory CSV</a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo $base; ?>export_excel.php?type=inventory" class="btn btn-outline-success w-100"><i class="bi bi-file-earmark-excel me-1"></i>Inventory Excel</a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo $base; ?>export_csv.php?type=faulty" class="btn btn-outline-success w-100"><i class="bi bi-filetype-csv me-1"></i>Not Working CSV</a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo $base; ?>export_csv.php?type=working" class="btn btn-outline-success w-100"><i class="bi bi-filetype-csv me-1"></i>Working CSV</a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo $base; ?>export_excel.php?type=working" class="btn btn-outline-success w-100"><i class="bi bi-file-earmark-excel me-1"></i>Working Excel</a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo $base; ?>export_csv.php?type=lab&lab_id=1" class="btn btn-outline-success w-100"><i class="bi bi-filetype-csv me-1"></i>Lab 1 CSV</a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo $base; ?>export_excel.php?type=lab&lab_id=1" class="btn btn-outline-success w-100"><i class="bi bi-file-earmark-excel me-1"></i>Lab 1 Excel</a>
            </div>
            <?php if (can('view_logs')): ?>
            <div class="col-md-3">
                <a href="<?php echo $base; ?>export_csv.php?type=activity" class="btn btn-outline-success w-100"><i class="bi bi-filetype-csv me-1"></i>Activity CSV</a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo $base; ?>export_excel.php?type=activity" class="btn btn-outline-success w-100"><i class="bi bi-file-earmark-excel me-1"></i>Activity Excel</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
