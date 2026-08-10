<?php
/**
 * =============================================================================
 * Issues - Edit
 * =============================================================================
 * Staff members can edit the issues they reported (category + description);
 * admins / super admins can edit any issue. The computer, reporter and status
 * can never be changed from here.
 * =============================================================================
 */

$page_title = 'Edit Issue';

require_once __DIR__ . '/../includes/auth.php';

require_can('view_issues');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = db()->prepare("SELECT * FROM issues WHERE id = ?");
$stmt->execute([$id]);
$issue = $stmt->fetch();

if (!$issue) {
    set_flash('danger', 'Issue not found.');
    redirect('issues/index.php');
}

if (!can_edit_issue($issue)) {
    set_flash('danger', 'You do not have permission to edit that issue.');
    redirect('issues/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $category = in_array($_POST['issue_category'] ?? '', issue_categories(), true)
        ? $_POST['issue_category']
        : $issue['issue_category'];
    $description = trim((string)($_POST['description'] ?? ''));

    if ($description === '') {
        set_flash('danger', 'The description cannot be empty.');
    } else {
        $stmt = db()->prepare(
            "UPDATE issues SET issue_category = ?, description = ? WHERE id = ?"
        );
        $stmt->execute([$category, $description, $id]);

        $old = ['category' => $issue['issue_category'], 'description' => $issue['description']];
        $new = ['category' => $category, 'description' => $description];
        log_activity('Edited issue #' . $id, 'issues', $id, $old, $new);

        set_flash('success', 'Issue #' . $id . ' was updated.');
        redirect('issues/view.php?id=' . $id);
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Issue #<?php echo (int)$issue['id']; ?></h4>
    <a href="<?php echo base_url('issues/view.php?id=' . (int)$issue['id']); ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to issue
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-bug me-1"></i>Issue Details</div>
            <div class="card-body">
                <form method="post" action="">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" value="<?php echo (int)$issue['id']; ?>">

                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="issue_category">
                            <?php foreach (issue_categories() as $cat): ?>
                                <option value="<?php echo e($cat); ?>" <?php echo $issue['issue_category'] === $cat ? 'selected' : ''; ?>>
                                    <?php echo e($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="6" maxlength="5000" required><?php echo e($issue['description']); ?></textarea>
                        <div class="form-text">Explain the problem so the administrators can fix it.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i>Save Changes
                        </button>
                        <a href="<?php echo base_url('issues/view.php?id=' . (int)$issue['id']); ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
