<?php
/**
 * =============================================================================
 * Labs - Edit
 * =============================================================================
 * Updates a lab's name, location, description and status.
 * =============================================================================
 */

$page_title = 'Edit Lab';

require_once __DIR__ . '/../includes/auth.php';

require_can('manage_labs');

$id = (int)($_GET['id'] ?? 0);

$stmt = db()->prepare("SELECT * FROM labs WHERE id = ?");
$stmt->execute([$id]);
$lab = $stmt->fetch();

if (!$lab) {
    set_flash('danger', 'Lab not found.');
    redirect('labs/index.php');
}

$v = [
    'name'        => $lab['name'],
    'location'    => $lab['location'] ?? '',
    'description' => $lab['description'] ?? '',
    'status'      => $lab['status'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $v = [
        'name'        => trim($_POST['name'] ?? ''),
        'location'    => trim($_POST['location'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'status'      => in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : $lab['status'],
    ];

    $errors = [];
    if ($v['name'] === '') {
        $errors[] = 'Lab name is required.';
    } else {
        $check = db()->prepare("SELECT id FROM labs WHERE name = ? AND id <> ?");
        $check->execute([$v['name'], $id]);
        if ($check->fetch()) {
            $errors[] = 'A lab with this name already exists.';
        }
    }

    if (count($errors) > 0) {
        foreach ($errors as $err) {
            set_flash('danger', $err);
        }
    } else {
        $stmt = db()->prepare("UPDATE labs SET name = ?, location = ?, description = ?, status = ? WHERE id = ?");
        $stmt->execute([$v['name'], $v['location'] ?: null, $v['description'] ?: null, $v['status'], $id]);

        log_activity('Updated lab "' . $v['name'] . '"', 'labs', $id, $lab, $v);

        set_flash('success', 'Lab "' . $v['name'] . '" was updated.');
        redirect('labs/index.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Lab</h4>
    <a href="<?php echo base_url('labs/index.php'); ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to labs</a>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-body">
        <form method="post" action="">
            <?php echo csrf_field(); ?>

            <div class="mb-3">
                <label class="form-label">Lab Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="<?php echo e($v['name']); ?>" required maxlength="100">
            </div>

            <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" class="form-control" name="location" value="<?php echo e($v['location']); ?>" maxlength="100">
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3" maxlength="2000"><?php echo e($v['description']); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select" name="status">
                    <option value="active" <?php echo $v['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $v['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
