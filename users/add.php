<?php
/**
 * =============================================================================
 * Users - Add
 * =============================================================================
 * Creates a new user account. Password is hashed with password_hash().
 * Admins can only create Staff accounts (Super Admins can create any role).
 * =============================================================================
 */

$page_title = 'Add User';

require_once __DIR__ . '/../includes/auth.php';

require_can('manage_users');

$roles = assignable_roles();

$v = [
    'username'  => '',
    'full_name' => '',
    'email'     => '',
    'role'      => 'staff',
    'status'    => 'active',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $v = [
        'username'  => trim($_POST['username'] ?? ''),
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email'     => trim($_POST['email'] ?? ''),
        'role'      => array_key_exists($_POST['role'] ?? '', $roles) ? $_POST['role'] : 'staff',
        'status'    => in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active',
    ];
    $password     = $_POST['password'] ?? '';
    $confirmPass  = $_POST['confirm_password'] ?? '';

    $errors = [];

    if ($v['username'] === '' || !preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $v['username'])) {
        $errors[] = 'Username must be 3-50 characters (letters, numbers, dot, dash or underscore).';
    } else {
        $check = db()->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$v['username']]);
        if ($check->fetch()) {
            $errors[] = 'This username is already taken.';
        }
    }
    if ($v['full_name'] === '') {
        $errors[] = 'Full name is required.';
    }
    if ($v['email'] !== '' && filter_var($v['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirmPass) {
        $errors[] = 'The passwords do not match.';
    }

    if (count($errors) > 0) {
        foreach ($errors as $err) {
            set_flash('danger', $err);
        }
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = db()->prepare(
            "INSERT INTO users (username, full_name, email, password_hash, role, status)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$v['username'], $v['full_name'], $v['email'] ?: null, $hash, $v['role'], $v['status']]);

        $newId = (int)db()->lastInsertId();
        log_activity('Added user "' . $v['username'] . '" with role ' . $v['role'], 'users', $newId, null, $v);

        set_flash('success', 'User "' . $v['username'] . '" was created successfully.');
        redirect('users/index.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-person-plus me-2"></i>Add User</h4>
    <a href="<?php echo base_url('users/index.php'); ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to users</a>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-body">
        <form method="post" action="">
            <?php echo csrf_field(); ?>

            <div class="mb-3">
                <label class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="username" value="<?php echo e($v['username']); ?>" required maxlength="50">
            </div>

            <div class="mb-3">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="full_name" value="<?php echo e($v['full_name']); ?>" required maxlength="100">
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="<?php echo e($v['email']); ?>" maxlength="100">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select class="form-select" name="role">
                        <?php foreach ($roles as $key => $label): ?>
                            <option value="<?php echo e($key); ?>" <?php echo $v['role'] === $key ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select" name="status">
                        <option value="active" <?php echo $v['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $v['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" name="password" required minlength="6" autocomplete="new-password">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" name="confirm_password" required minlength="6" autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create User</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
