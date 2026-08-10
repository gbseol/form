<?php
/**
 * =============================================================================
 * Reports - User Activity
 * =============================================================================
 * Full audit trail with date range and user filters. Super Admin only.
 * =============================================================================
 */

$page_title = 'Activity Logs';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/_data.php';

require_can('view_logs');

$rows  = activity_report();
$users = db()->query("SELECT id, username, full_name FROM users ORDER BY username")->fetchAll();

$from = trim($_GET['from'] ?? '');
$to   = trim($_GET['to'] ?? '');
$userId = (int)($_GET['user_id'] ?? 0);
$extraQuery = '';
if ($from)      $extraQuery .= '&from=' . rawurlencode($from);
if ($to)        $extraQuery .= '&to=' . rawurlencode($to);
if ($userId > 0) $extraQuery .= '&user_id=' . $userId;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0"><i class="bi bi-journal-text me-2"></i>Activity Logs</h4>
    <a href="<?php echo base_url('reports/index.php'); ?>" class="btn btn-outline-secondary btn-sm">Back</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control form-control-sm" name="from" value="<?php echo e($from); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control form-control-sm" name="to" value="<?php echo e($to); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">User</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">All Users</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?php echo (int)$u['id']; ?>" <?php echo $userId === (int)$u['id'] ? 'selected' : ''; ?>><?php echo e($u['username']); ?> (<?php echo e($u['full_name']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="<?php echo base_url('reports/activity.php'); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
                <a href="<?php echo base_url('reports/export_csv.php?type=activity' . $extraQuery); ?>" class="btn btn-sm btn-outline-success"><i class="bi bi-filetype-csv"></i></a>
                <a href="<?php echo base_url('reports/print.php?type=activity' . $extraQuery); ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-printer"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Log Entries <span class="text-muted">(<?php echo count($rows); ?> shown, max 5000)</span></div>
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>IP Address</th>
                    <th>Action</th>
                    <th>Old Value</th>
                    <th>New Value</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($rows) === 0): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No activity recorded.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td class="fw-semibold"><?php echo e($r['User']); ?></td>
                    <td class="small"><?php echo e($r['Date']); ?></td>
                    <td class="small"><?php echo e($r['Time']); ?></td>
                    <td class="small mono"><?php echo e($r['IP Address']); ?></td>
                    <td class="small"><?php echo e($r['Action']); ?></td>
                    <td class="small">
                        <?php if ($r['Old Value']): ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0" data-bs-toggle="modal" data-bs-target="#logModal" data-log="<?php echo e($r['Old Value']); ?>">View</button>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="small">
                        <?php if ($r['New Value']): ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0" data-bs-toggle="modal" data-bs-target="#logModal" data-log="<?php echo e($r['New Value']); ?>">View</button>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for inspecting old/new JSON values -->
<div class="modal fade" id="logModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Value Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <pre class="log-pre mb-0" style="white-space:pre-wrap;font-size:.85rem;"></pre>
            </div>
        </div>
    </div>
</div>

<script>
    // Populate the value-detail modal with the clicked row's JSON.
    document.querySelectorAll('[data-bs-toggle="modal"][data-target="#logModal"]').forEach(function () {});
    document.querySelectorAll('[data-bs-target="#logModal"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const raw = this.getAttribute('data-log');
            const modalBody = document.querySelector('#logModal .log-pre');
            try {
                const obj = JSON.parse(raw);
                modalBody.textContent = JSON.stringify(obj, null, 2);
            } catch (e) {
                modalBody.textContent = raw;
            }
        });
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
