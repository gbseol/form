<?php
/**
 * =============================================================================
 * Users - Delete
 * =============================================================================
 * POST only. Only a Super Admin can delete users. A user cannot delete their
 * own account. Computers created by the user keep their audit reference
 * (ON DELETE SET NULL).
 * =============================================================================
 */

require_once __DIR__ . '/../includes/auth.php';

require_login();
require_can('delete_users');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('users/index.php');
}
verify_csrf();

$id = (int)($_POST['id'] ?? 0);
$me = (int)current_user()['id'];

// Never allow deleting yourself.
if ($id === $me) {
    set_flash('danger', 'You cannot delete your own account.');
    redirect('users/index.php');
}

$stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('danger', 'User not found.');
    redirect('users/index.php');
}

// Extra safety: only Super Admin can delete another Super Admin.
if ($user['role'] === 'super_admin' && current_user()['role'] !== 'super_admin') {
    set_flash('danger', 'You are not allowed to delete this account.');
    redirect('users/index.php');
}

$stmt = db()->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

log_activity('Deleted user "' . $user['username'] . '"', 'users', $id, $user, null);

set_flash('success', 'User "' . $user['username'] . '" was deleted.');
redirect('users/index.php');
