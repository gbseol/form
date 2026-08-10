<?php
/**
 * =============================================================================
 * Computers - Delete
 * =============================================================================
 * POST only. Deletes a computer, its photos (files + rows) and logs the action.
 * =============================================================================
 */

require_once __DIR__ . '/../includes/auth.php';

require_login();
require_can('delete_computer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('computers/index.php');
}
verify_csrf();

$id = (int)($_POST['id'] ?? 0);

$stmt = db()->prepare("SELECT * FROM computers WHERE id = ?");
$stmt->execute([$id]);
$computer = $stmt->fetch();

if (!$computer) {
    set_flash('danger', 'Computer not found.');
    redirect('computers/index.php');
}

// Remove photo files from disk (best effort) and then their DB rows.
$photoStmt = db()->prepare("SELECT filename FROM computer_photos WHERE computer_id = ?");
$photoStmt->execute([$id]);
foreach ($photoStmt->fetchAll() as $photo) {
    $path = __DIR__ . '/../uploads/computers/' . $photo['filename'];
    if (is_file($path)) {
        @unlink($path);
    }
}

// Delete the computer - photos cascade automatically (ON DELETE CASCADE).
$del = db()->prepare("DELETE FROM computers WHERE id = ?");
$del->execute([$id]);

log_activity('Deleted computer ' . $computer['computer_id'], 'computers', $id, $computer, null);

set_flash('success', 'Computer "' . $computer['computer_id'] . '" was deleted.');
redirect('computers/index.php');
