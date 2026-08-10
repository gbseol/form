<?php
/**
 * =============================================================================
 * Labs - Add
 * =============================================================================
 * Creates a new lab. Super Admin and Admin only.
 * =============================================================================
 */

$page_title = 'Add Lab';

require_once __DIR__ . '/../includes/auth.php';

require_can('manage_labs');

$v = ['name' => '', 'location' => '', 'description' => '', 'status' => 'active'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $v = [
        'name'        => trim($_POST['name'] ?? ''),
        'location'    => trim($_POST['location'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'status'      => in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active',
    ];

    $errors = [];
    if ($v['name'] === '') {
        $errors[] = 'Lab name is required.';
    } else {
        $check = db()->prepare("SELECT id FROM labs WHERE name = ?");
        $check->execute([$v['name']]);
        if ($check->fetch()) {
            $errors[] = 'A lab with this name already exists.';
        }
    }

    if (count($errors) > 0) {
        foreach ($errors as $err) {
            set_flash('danger', $err);
        }
    } else {
        $stmt = db()->prepare("INSERT INTO labs (name, location, description, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$v['name'], $v['location'] ?: null, $v['description'] ?: null, $v['status']]);

        $newId = (int)db()->lastInsertId();
        log_activity('Added lab "' . $v['name'] . '"', 'labs', $newId, null, $v);

        set_flash('success', 'Lab "' . $v['name'] . '" was created.');
        redirect('labs/index.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add Lab</h4>
    <a href="<?php echo base_url('labs/index.php'); ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to labs</a>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-body">
        <form method="post" action="">
            <?php echo csrf_field(); ?>

            <div class="mb-3">
                <label class="form-label">Lab Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="<?php echo e($v['name']); ?>" placeholder="e.g. Lab 1" required maxlength="100">
            </div>

            <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" class="form-control" name="location" value="<?php echo e($v['location']); ?>" placeholder="e.g. Building A, Ground Floor" maxlength="100">
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

            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create Lab</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
