<?php
/**
 * =============================================================================
 * Users - List
 * =============================================================================
 * Lists all user accounts. Only Super Admin and Admin can view this page.
 * =============================================================================
 */

$page_title = 'Users';

require_once __DIR__ . '/../includes/auth.php';

require_can('manage_users');

$roleFilter = trim($_GET['role'] ?? '');

$sql = "SELECT * FROM users";
$params = [];
if ($roleFilter !== '') {
    $sql .= " WHERE role = ?";
    $params[] = $roleFilter;
}
$sql .= " ORDER BY role ASC, username ASC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Only Super Admin can delete users.
$canDelete = can('delete_users');

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0"><i class="bi bi-people me-2"></i>User Management</h4>
    <div class="d-flex gap-2">
        <form method="get" class="d-flex gap-2">
            <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Roles</option>
                <?php foreach (user_roles() as $key => $label): ?>
                    <option value="<?php echo e($key); ?>" <?php echo $roleFilter === $key ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <a href="<?php echo base_url('users/add.php'); ?>" class="btn btn-sm btn-primary"><i class="bi bi-person-plus me-1"></i>Add User</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle mb-0">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) === 0): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
                <?php endif; ?>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?php $photoUrl = profile_photo_url($user['profile_photo'] ?? null); ?>
                            <?php if ($photoUrl): ?>
                                <img src="<?php echo e($photoUrl); ?>" alt="Profile photo" class="avatar-sm rounded-circle object-fit-cover flex-shrink-0">
                            <?php else: ?>
                                <i class="bi bi-person-circle fs-4 text-secondary flex-shrink-0"></i>
                            <?php endif; ?>
                            <span class="fw-semibold"><?php echo e($user['username']); ?></span>
                        </div>
                    </td>
                    <td><?php echo e($user['full_name']); ?></td>
                    <td><?php echo e($user['email'] ?: '—'); ?></td>
                    <td>
                        <span class="badge text-bg-<?php echo $user['role'] === 'super_admin' ? 'danger' : ($user['role'] === 'admin' ? 'primary' : 'secondary'); ?>">
                            <?php echo e(role_label($user['role'])); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                            <?php echo e(ucfirst($user['status'])); ?>
                        </span>
                    </td>
                    <td class="small"><?php echo $user['last_login'] ? e($user['last_login']) : '<span class="text-muted">Never</span>'; ?></td>
                    <td class="text-end text-nowrap">
                        <?php if ($user['id'] === (int)current_user()['id']): ?>
                            <span class="text-muted small">(you)</span>
                        <?php endif; ?>
                        <?php if (can_manage_role($user['role'])): ?>
                            <a href="<?php echo base_url('users/edit.php?id=' . (int)$user['id']); ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <a href="<?php echo base_url('users/reset_password.php?id=' . (int)$user['id']); ?>" class="btn btn-sm btn-outline-warning" title="Reset password"><i class="bi bi-key"></i></a>
                        <?php endif; ?>
                        <?php if ($canDelete && $user['id'] !== (int)current_user()['id']): ?>
                            <form method="post" action="<?php echo base_url('users/delete.php'); ?>" class="d-inline" data-confirm="Delete user <?php echo e($user['username']); ?>? This cannot be undone.">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
