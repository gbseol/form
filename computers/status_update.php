<?php
/**
 * =============================================================================
 * Computers - Quick Status Update (AJAX endpoint)
 * =============================================================================
 * POST only, returns JSON. Called from the inventory list quick-select.
 *
 * Request body (form-encoded):
 *   computer_id, status, csrf_token
 * Response:
 *   {"ok": true}  or  {"ok": false, "error": "..."}
 * =============================================================================
 */

require_once __DIR__ . '/../includes/auth.php';

require_login();
require_can('update_status');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Verify the CSRF token supplied by the AJAX request.
verify_csrf();

$id     = (int)($_POST['computer_id'] ?? 0);
$status = $_POST['status'] ?? '';

// Only allow the known status values.
if (!in_array($status, computer_statuses(), true)) {
    echo json_encode(['ok' => false, 'error' => 'Invalid status.']);
    exit;
}

$stmt = db()->prepare("SELECT * FROM computers WHERE id = ?");
$stmt->execute([$id]);
$computer = $stmt->fetch();

if (!$computer) {
    echo json_encode(['ok' => false, 'error' => 'Computer not found.']);
    exit;
}

// Save the new status and log old -> new.
$upd = db()->prepare("UPDATE computers SET status = ?, updated_by = ? WHERE id = ?");
$upd->execute([$status, (int)current_user()['id'], $id]);

log_activity(
    'Changed status of ' . $computer['computer_id'] . ' from "' . $computer['status'] . '" to "' . $status . '"',
    'computers',
    $id,
    ['status' => $computer['status']],
    ['status' => $status]
);

echo json_encode(['ok' => true, 'status' => $status]);
