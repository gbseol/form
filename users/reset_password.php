<?php
/**
 * =============================================================================
 * Users - Reset Password
 * =============================================================================
 * Lets an authorised user set a brand new password for another account.
 * Admins may only reset passwords of Staff accounts.
 * =============================================================================
 */

$page_title = 'Reset Password';

require_once __DIR__ . '/../includes/auth.php';

require_can('reset_passwords');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('danger', 'User not found.');
    redirect('users/index.php');
}

// Role guard: Admins cannot reset passwords of Admin or Super Admin accounts.
if (!can_manage_role($user['role'])) {
    set_flash('danger', 'You are not allowed to reset the password of this account.');
    redirect('users/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $password    = $_POST['password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    $errors = [];
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

        $stmt = db()->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$hash, $id]);

        log_activity('Reset password for user "' . $user['username'] . '"', 'users', $id, null, null);

        set_flash('success', 'Password for "' . $user['username'] . '" was reset successfully.');
        redirect('users/index.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-key me-2"></i>Reset Password: <?php echo e($user['username']); ?></h4>
    <a href="<?php echo base_url('users/index.php'); ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to users</a>
</div>

<div class="card" style="max-width:560px;">
    <div class="card-body">
        <form method="post" action="">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">

            <div class="mb-3">
                <label class="form-label">New Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" name="password" required minlength="6" autocomplete="new-password">
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" name="confirm_password" required minlength="6" autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update Password</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
