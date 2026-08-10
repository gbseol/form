<?php
/**
 * =============================================================================
 * Settings - My Profile
 * =============================================================================
 * Lets any logged-in user update their own name, email address and profile
 * photo (upload or remove).
 * =============================================================================
 */

$page_title = 'My Profile';

require_once __DIR__ . '/../includes/auth.php';

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // ------------------------------------------------------------------
    // Action: remove the current profile photo
    // ------------------------------------------------------------------
    if (($_POST['action'] ?? '') === 'remove_photo') {
        delete_profile_photo_file($user['profile_photo'] ?? null);

        $stmt = db()->prepare("UPDATE users SET profile_photo = NULL WHERE id = ?");
        $stmt->execute([(int)$user['id']]);
        $_SESSION['user']['profile_photo'] = null;

        log_activity('Removed own profile photo', 'users', (int)$user['id']);
        set_flash('success', 'Your profile photo was removed.');
        redirect('settings/profile.php');
    }

    // ------------------------------------------------------------------
    // Action: update name / email (+ optionally upload a new photo)
    // ------------------------------------------------------------------
    $username = trim($_POST['username'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');

    $errors = [];
    if ($username === '' || !preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
        $errors[] = 'Username must be 3-50 characters (letters, numbers, dot, dash or underscore).';
    } else {
        $check = db()->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $check->execute([$username, (int)$user['id']]);
        if ($check->fetch()) {
            $errors[] = 'This username is already taken.';
        }
    }
    if ($fullName === '') {
        $errors[] = 'Full name is required.';
    }
    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors[] = 'Please enter a valid email address.';
    }

    $photoFile = null;
    if (!empty($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $photoFile = save_profile_photo($_FILES['profile_photo'], (int)$user['id']);
        if ($photoFile === null) {
            $errors[] = 'The photo could not be saved. Use a JPEG, PNG, GIF, WebP or BMP image under 5 MB.';
        }
    }

    if (count($errors) > 0) {
        if ($photoFile) {
            delete_profile_photo_file($photoFile);
        }
        foreach ($errors as $err) {
            set_flash('danger', $err);
        }
    } else {
        if ($photoFile) {
            delete_profile_photo_file($user['profile_photo'] ?? null);
        }
        $photoToStore = $photoFile ?: ($user['profile_photo'] ?? null);

        $stmt = db()->prepare("UPDATE users SET username = ?, full_name = ?, email = ?, profile_photo = ? WHERE id = ?");
        $stmt->execute([$username, $fullName, $email ?: null, $photoToStore, (int)$user['id']]);

        // Refresh the session copy.
        $_SESSION['user']['username']      = $username;
        $_SESSION['user']['full_name']     = $fullName;
        $_SESSION['user']['email']         = $email;
        $_SESSION['user']['profile_photo'] = $photoToStore;

        log_activity('Updated own profile', 'users', (int)$user['id']);
        set_flash('success', 'Your profile was updated.');
        redirect('settings/profile.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-person-circle me-2"></i>My Profile</h4>
    <a href="<?php echo base_url('dashboard.php'); ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to dashboard</a>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-body">
        <form method="post" action="" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>

            <div class="mb-4 text-center">
                <?php $photoUrl = profile_photo_url($user['profile_photo'] ?? null); ?>
                <img id="profilePhotoPreview" src="<?php echo e($photoUrl); ?>" alt="Profile photo preview" class="avatar-lg rounded-circle border shadow-sm object-fit-cover mb-2 <?php echo $photoUrl ? '' : 'd-none'; ?>">
                <i id="profilePhotoFallback" class="bi bi-person-circle avatar-lg d-inline-block text-secondary mb-2 <?php echo $photoUrl ? 'd-none' : ''; ?>"></i>
                <div>
                    <label for="profile_photo" class="btn btn-outline-primary btn-sm mb-2">
                        <i class="bi bi-camera me-1"></i>Upload Photo
                    </label>
                    <input type="file" class="d-none" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp" data-preview="#profilePhotoPreview" data-fallback="#profilePhotoFallback">
                    <?php if ($user['profile_photo'] ?? null): ?>
                        <button type="submit" name="action" value="remove_photo" class="btn btn-outline-danger btn-sm mb-2" data-confirm="Remove your profile photo?">
                            <i class="bi bi-trash me-1"></i>Remove
                        </button>
                    <?php endif; ?>
                </div>
                <div class="form-text">JPEG, PNG, GIF, WebP or BMP. Maximum 5 MB.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="username" value="<?php echo e($user['username']); ?>" required maxlength="50">
                <div class="form-text">Letters, numbers, dot, dash or underscore. 3-50 characters.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Role</label>
                <input type="text" class="form-control" value="<?php echo e(role_label($user['role'])); ?>" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="full_name" value="<?php echo e($user['full_name']); ?>" required maxlength="100">
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="<?php echo e($user['email']); ?>" maxlength="100">
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Profile</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
