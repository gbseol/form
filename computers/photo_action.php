<?php
/**
 * =============================================================================
 * Computers - Photo Actions (delete / set primary)
 * =============================================================================
 * POST only. Handles the small per-photo forms shown on the edit page.
 * =============================================================================
 */

require_once __DIR__ . '/../includes/auth.php';

require_login();
require_can('edit_computer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('computers/index.php');
}
verify_csrf();

$action    = $_POST['action'] ?? '';
$photoId   = (int)($_POST['photo_id'] ?? 0);
$computerId = (int)($_POST['computer_id'] ?? 0);

// Ensure the photo belongs to the given computer.
$stmt = db()->prepare("SELECT * FROM computer_photos WHERE id = ? AND computer_id = ?");
$stmt->execute([$photoId, $computerId]);
$photo = $stmt->fetch();

if (!$photo) {
    set_flash('danger', 'Photo not found.');
    redirect('computers/edit.php?id=' . $computerId);
}

if ($action === 'delete') {
    // Remove the file from disk and the row (prevent primary hole).
    $path = __DIR__ . '/../uploads/computers/' . $photo['filename'];
    if (is_file($path)) {
        @unlink($path);
    }
    db()->prepare("DELETE FROM computer_photos WHERE id = ?")->execute([$photoId]);

    // If the deleted photo was the primary one, promote another photo.
    if ($photo['is_primary']) {
        $next = db()->prepare("SELECT id FROM computer_photos WHERE computer_id = ? ORDER BY id ASC LIMIT 1");
        $next->execute([$computerId]);
        $nextId = $next->fetchColumn();
        if ($nextId) {
            db()->prepare("UPDATE computer_photos SET is_primary = 1 WHERE id = ?")->execute([$nextId]);
        }
    }

    log_activity('Deleted photo of computer #' . $computerId, 'computer_photos', $photoId);
    set_flash('success', 'Photo deleted.');
} elseif ($action === 'primary') {
    // Demote all other photos, promote this one.
    db()->prepare("UPDATE computer_photos SET is_primary = 0 WHERE computer_id = ?")->execute([$computerId]);
    db()->prepare("UPDATE computer_photos SET is_primary = 1 WHERE id = ?")->execute([$photoId]);

    log_activity('Changed primary photo of computer #' . $computerId, 'computer_photos', $photoId);
    set_flash('success', 'Primary photo updated.');
} else {
    set_flash('danger', 'Unknown photo action.');
}

redirect('computers/edit.php?id=' . $computerId);
