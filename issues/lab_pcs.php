<?php
/**
 * =============================================================================
 * Issues - Lab / Computer data (AJAX)
 * =============================================================================
 * Returns all active labs with their computers so the dashboard "Report an
 * Issue" picker can show labs and then the computers inside the selected lab.
 *
 *   GET issues/lab_pcs.php -> JSON
 *
 * Requires an authenticated session.
 * =============================================================================
 */

require_once __DIR__ . '/../includes/auth.php';
require_login();
require_can('report_issue');

header('Content-Type: application/json; charset=utf-8');

$labs = [];
$stmt = db()->query(
    "SELECT id, name FROM labs WHERE status = 'active' ORDER BY name"
);
while ($lab = $stmt->fetch()) {
    $pcs = [];
    $pcStmt = db()->prepare(
        "SELECT id, computer_id, status FROM computers WHERE lab_id = ? ORDER BY computer_id"
    );
    $pcStmt->execute([(int)$lab['id']]);
    while ($pc = $pcStmt->fetch()) {
        $pcs[] = [
            'id'          => (int)$pc['id'],
            'computer_id' => $pc['computer_id'],
            'status'      => $pc['status'],
        ];
    }
    $labs[] = [
        'id'        => (int)$lab['id'],
        'name'      => $lab['name'],
        'computers' => $pcs,
    ];
}

echo json_encode(['ok' => true, 'labs' => $labs]);
