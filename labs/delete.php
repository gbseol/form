<?php
/**
 * =============================================================================
 * Labs - Delete
 * =============================================================================
 * POST only. Deletes a lab. Computers in the lab are NOT deleted; their lab_id
 * is set to NULL through the foreign key (ON DELETE SET NULL).
 * =============================================================================
 */

require_once __DIR__ . '/../includes/auth.php';

require_login();
require_can('delete_labs');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('labs/index.php');
}
verify_csrf();

$id = (int)($_POST['id'] ?? 0);

$stmt = db()->prepare("SELECT * FROM labs WHERE id = ?");
$stmt->execute([$id]);
$lab = $stmt->fetch();

if (!$lab) {
    set_flash('danger', 'Lab not found.');
    redirect('labs/index.php');
}

$del = db()->prepare("DELETE FROM labs WHERE id = ?");
$del->execute([$id]);

log_activity('Deleted lab "' . $lab['name'] . '"', 'labs', $id, $lab, null);

set_flash('success', 'Lab "' . $lab['name'] . '" was deleted. Its computers are now unassigned.');
redirect('labs/index.php');
