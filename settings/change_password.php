<?php
/**
 * =============================================================================
 * Settings - Change Password
 * =============================================================================
 * Lets any logged-in user change their own password. The current password is
 * verified before the new one is stored (hashed with password_hash()).
 * =============================================================================
 */

$page_title = 'Change Password';

require_once __DIR__ . '/../includes/auth.php';

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $current    = $_POST['current_password'] ?? '';
    $password   = $_POST['new_password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';

    $errors = [];

    // Verify the current password.
    $stmt = db()->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([(int)$user['id']]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($current, $hash)) {
        $errors[] = 'Your current password is incorrect.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'The new passwords do not match.';
    }

    if (count($errors) > 0) {
        foreach ($errors as $err) {
            set_flash('danger', $err);
        }
    } else {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = db()->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$newHash, (int)$user['id']]);

        log_activity('Changed own password', 'users', (int)$user['id']);
        set_flash('success', 'Your password was changed successfully.');
        redirect('settings/change_password.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-key me-2"></i>Change Password</h4>
    <a href="<?php echo base_url('dashboard.php'); ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to dashboard</a>
</div>

<div class="card" style="max-width:560px;">
    <div class="card-body">
        <form method="post" action="">
            <?php echo csrf_field(); ?>

            <div class="mb-3">
                <label class="form-label">Current Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" name="current_password" required autocomplete="current-password">
            </div>

            <div class="mb-3">
                <label class="form-label">New Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" name="new_password" required minlength="6" autocomplete="new-password">
                <div class="form-text">At least 6 characters.</div>
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
