<?php
/**
 * =============================================================================
 * Issues - List
 * =============================================================================
 * Lists reported computer issues. Staff see only the issues they reported;
 * admins / super admins see every issue and can manage them.
 * =============================================================================
 */

$page_title = 'Issues';

require_once __DIR__ . '/../includes/auth.php';

require_can('view_issues');

$user      = current_user();
$isManager = can('manage_issues');

// Filters.
$fStatus = trim($_GET['status'] ?? '');

// Staff only ever see their own reports.
$where  = [];
$params = [];
if (!$isManager) {
    $where[]  = 'i.reported_by = ?';
    $params[] = (int)$user['id'];
}
if ($fStatus !== '' && in_array($fStatus, issue_statuses(), true)) {
    $where[]  = 'i.status = ?';
    $params[] = $fStatus;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$fromSql = "FROM issues i
            LEFT JOIN computers c ON c.id = i.computer_id
            LEFT JOIN labs l ON l.id = c.lab_id
            LEFT JOIN users u ON u.id = i.reported_by
            $whereSql";

$stmt = db()->prepare("SELECT COUNT(*) $fromSql");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();

$stmt = db()->prepare(
    "SELECT i.*, c.computer_id, l.name AS lab_name,
            u.username AS reporter_username
     $fromSql
     ORDER BY FIELD(i.status, 'open', 'in_progress', 'resolved'), i.created_at DESC
     LIMIT 100"
);
$stmt->execute($params);
$issues = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="bi bi-bug me-1"></i> Reported Issues
            <span class="text-muted">(<?php echo $total; ?> total)</span>
        </span>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <?php if ($isManager): ?>
                <a href="<?php echo base_url('reports/export_excel.php?type=issues' . ($fStatus !== '' ? '&status=' . urlencode($fStatus) : '')); ?>" class="btn btn-sm btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                </a>
                <a href="<?php echo base_url('reports/export_pdf.php?type=issues' . ($fStatus !== '' ? '&status=' . urlencode($fStatus) : '')); ?>" class="btn btn-sm btn-danger">
                    <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
                </a>
            <?php endif; ?>
            <form method="get" action="" class="d-flex gap-2 align-items-center">
                <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <?php foreach (issue_statuses() as $s): ?>
                        <option value="<?php echo e($s); ?>" <?php echo $fStatus === $s ? 'selected' : ''; ?>>
                            <?php echo e(ucfirst(str_replace('_', ' ', $s))); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($fStatus !== ''): ?>
                    <a href="<?php echo base_url('issues/index.php'); ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Computer</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Reported By</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($issues) === 0): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <?php echo $isManager ? 'No issues have been reported yet.' : 'You have not reported any issues yet.'; ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($issues as $issue): ?>
                <tr>
                    <td data-label="#" class="fw-semibold">#<?php echo (int)$issue['id']; ?></td>
                    <td data-label="Computer">
                        <?php if ($issue['computer_id']): ?>
                            <a href="<?php echo base_url('computers/view.php?id=' . (int)$issue['computer_id']); ?>" class="fw-semibold"><?php echo e($issue['computer_id']); ?></a>
                            <div class="small text-muted"><?php echo e($issue['lab_name'] ?: '—'); ?></div>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Category"><?php echo e($issue['issue_category']); ?></td>
                    <td data-label="Description" class="small issue-desc">
                        <?php echo e(mb_strimwidth($issue['description'], 0, 70, '…')); ?>
                    </td>
                    <td data-label="Status"><?php echo issue_status_badge($issue['status']); ?></td>
                    <td data-label="Reported By" class="small"><?php echo e($issue['reporter_username'] ?: '—'); ?></td>
                    <td data-label="Date" class="small"><?php echo e(date('d M Y', strtotime($issue['created_at']))); ?></td>
                    <td class="text-end text-nowrap stack-actions">
                        <a href="<?php echo base_url('issues/view.php?id=' . (int)$issue['id']); ?>" class="btn btn-sm btn-outline-secondary" title="View issue">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
