<?php
/**
 * =============================================================================
 * Users - Edit
 * =============================================================================
 * Updates a user's profile fields. The password is changed separately via
 * users/reset_password.php. Admins may only edit Staff accounts and are never
 * allowed to manage Super Admin or other Admin accounts.
 * =============================================================================
 */

$page_title = 'Edit User';

require_once __DIR__ . '/../includes/auth.php';

require_can('manage_users');

$id = (int)($_GET['id'] ?? 0);

$stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('danger', 'User not found.');
    redirect('users/index.php');
}

// Role guard: the current user must be allowed to manage this user's role.
if (!can_manage_role($user['role'])) {
    set_flash('danger', 'You are not allowed to manage this account.');
    redirect('users/index.php');
}

$roles = assignable_roles();

$v = [
    'full_name' => $user['full_name'],
    'email'     => $user['email'] ?? '',
    'role'      => $user['role'],
    'status'    => $user['status'],
];

// Module permissions shown on the form: explicit assignment when present,
// otherwise the role defaults. Only the Super Admin can edit them.
$isSuperAdminAccount = ($user['role'] === 'super_admin');
$selectedModules     = $user['permissions'] === null
    ? role_default_modules($user['role'])
    : ($user['permissions'] === ''
        ? []
        : array_values(array_filter(array_map('trim', explode(',', (string)$user['permissions'])))));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // ------------------------------------------------------------------
    // Action: remove this user's profile photo
    // ------------------------------------------------------------------
    if (($_POST['action'] ?? '') === 'remove_photo') {
        delete_profile_photo_file($user['profile_photo'] ?? null);

        $stmt = db()->prepare("UPDATE users SET profile_photo = NULL WHERE id = ?");
        $stmt->execute([$id]);

        if ((int)current_user()['id'] === $id) {
            $_SESSION['user']['profile_photo'] = null;
        }

        log_activity('Removed profile photo of user "' . $user['username'] . '"', 'users', $id, $user['profile_photo'] ?? null, null);
        set_flash('success', 'The profile photo was removed.');
        redirect('users/edit.php?id=' . $id);
    }

    $v = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email'     => trim($_POST['email'] ?? ''),
        'role'      => array_key_exists($_POST['role'] ?? '', $roles) ? $_POST['role'] : $user['role'],
        'status'    => in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : $user['status'],
    ];

    // Module permissions: only a Super Admin editing a non-Super-Admin account
    // may change them. For everyone else the existing assignment is kept.
    $permissions = $user['permissions'];
    if (has_role('super_admin') && !$isSuperAdminAccount) {
        $posted = (array)($_POST['permissions'] ?? []);
        $allowed = array_keys(module_list());
        $selectedModules = array_values(array_intersect($allowed, $posted));
        $permissions = implode(',', $selectedModules);
    } else {
        $selectedModules = $user['permissions'] === null
            ? role_default_modules($v['role'])
            : ($user['permissions'] === ''
                ? []
                : array_values(array_filter(array_map('trim', explode(',', (string)$user['permissions'])))));
    }

    $errors = [];
    if ($v['full_name'] === '') {
        $errors[] = 'Full name is required.';
    }
    if ($v['email'] !== '' && filter_var($v['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors[] = 'Please enter a valid email address.';
    }

    $photoFile = null;
    if (!empty($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $photoFile = save_profile_photo($_FILES['profile_photo'], $id);
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

        $stmt = db()->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, status = ?, profile_photo = ?, permissions = ? WHERE id = ?");
        $stmt->execute([$v['full_name'], $v['email'] ?: null, $v['role'], $v['status'], $photoToStore, $permissions, $id]);

        if ((int)current_user()['id'] === $id) {
            $_SESSION['user']['profile_photo'] = $photoToStore;
        }

        log_activity('Updated user "' . $user['username'] . '"', 'users', $id, $user, $v);

        set_flash('success', 'User "' . $user['username'] . '" was updated.');
        redirect('users/index.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-person-gear me-2"></i>Edit User: <?php echo e($user['username']); ?></h4>
    <a href="<?php echo base_url('users/index.php'); ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to users</a>
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
                        <button type="submit" name="action" value="remove_photo" class="btn btn-outline-danger btn-sm mb-2" data-confirm="Remove this user's profile photo?">
                            <i class="bi bi-trash me-1"></i>Remove
                        </button>
                    <?php endif; ?>
                </div>
                <div class="form-text">JPEG, PNG, GIF, WebP or BMP. Maximum 5 MB.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" value="<?php echo e($user['username']); ?>" disabled>
                <div class="form-text">The username cannot be changed. Use "Reset password" to set a new login.</div>
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

            <?php if (has_role('super_admin')): ?>
                <?php include __DIR__ . '/_permissions.php'; ?>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
