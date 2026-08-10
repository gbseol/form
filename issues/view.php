<?php
/**
 * =============================================================================
 * Issues - View
 * =============================================================================
 * Shows one issue in detail. Administrators can update its status
 * (open / in progress / resolved) and record fix notes here.
 * =============================================================================
 */

$page_title = 'Issue Details';

require_once __DIR__ . '/../includes/auth.php';

require_can('view_issues');

$id = (int)($_GET['id'] ?? 0);

$stmt = db()->prepare(
    "SELECT i.*, c.computer_id, c.status AS computer_status, l.name AS lab_name,
            u.username AS reporter_username,
            f.username AS fixer_username
       FROM issues i
       LEFT JOIN computers c ON c.id = i.computer_id
       LEFT JOIN labs l ON l.id = c.lab_id
       LEFT JOIN users u ON u.id = i.reported_by
       LEFT JOIN users f ON f.id = i.fixed_by
      WHERE i.id = ?"
);
$stmt->execute([$id]);
$issue = $stmt->fetch();

if (!$issue) {
    set_flash('danger', 'Issue not found.');
    redirect('issues/index.php');
}

// Staff can only view their own issues.
if (!can('manage_issues') && (int)$issue['reported_by'] !== (int)current_user()['id']) {
    set_flash('danger', 'You do not have permission to view that issue.');
    redirect('issues/index.php');
}

$isManager = can('manage_issues');

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0"><i class="bi bi-bug me-2"></i>Issue #<?php echo (int)$issue['id']; ?> <?php echo issue_status_badge($issue['status']); ?></h4>
    <a href="<?php echo base_url('issues/index.php'); ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to issues
    </a>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-info-circle me-1"></i>Issue Details</div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <th class="text-muted" style="width: 35%;">Computer</th>
                            <td>
                                <?php if ($issue['computer_id']): ?>
                                    <a href="<?php echo base_url('computers/view.php?id=' . (int)$issue['computer_id']); ?>">
                                        <?php echo e($issue['computer_id']); ?>
                                    </a>
                                    <span class="text-muted">— <?php echo e($issue['lab_name'] ?: 'No lab'); ?></span>
                                    <span class="ms-1"><?php echo status_badge($issue['computer_status'] ?? ''); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">Computer no longer exists</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Category</th>
                            <td><?php echo e($issue['issue_category']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Reported By</th>
                            <td><?php echo e($issue['reporter_username'] ?: '—'); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Reported On</th>
                            <td><?php echo e(date('d M Y, H:i', strtotime($issue['created_at']))); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Description</th>
                            <td style="word-break: break-word;"><?php echo nl2br(e($issue['description'])); ?></td>
                        </tr>
                        <?php if ($issue['status'] === 'resolved'): ?>
                        <tr>
                            <th class="text-muted">Resolved By</th>
                            <td><?php echo e($issue['fixer_username'] ?: '—'); ?> on <?php echo e(date('d M Y, H:i', strtotime($issue['fixed_at']))); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Fix Notes</th>
                            <td style="word-break: break-word;"><?php echo $issue['fix_notes'] !== null && $issue['fix_notes'] !== '' ? nl2br(e($issue['fix_notes'])) : '—'; ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <?php if ($isManager): ?>
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-tools me-1"></i>Manage Issue</div>
            <div class="card-body">
                <form method="post" action="<?php echo base_url('issues/fix.php'); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" value="<?php echo (int)$issue['id']; ?>">

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <?php foreach (issue_statuses() as $s): ?>
                                <option value="<?php echo e($s); ?>" <?php echo $issue['status'] === $s ? 'selected' : ''; ?>>
                                    <?php echo e(ucfirst(str_replace('_', ' ', $s))); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fix Notes</label>
                        <textarea class="form-control" name="fix_notes" rows="4" placeholder="Describe what was done to fix the problem."><?php echo e($issue['fix_notes'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-check-lg me-1"></i>Save Status / Mark Fixed
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
