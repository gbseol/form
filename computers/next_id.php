<?php
/**
 * =============================================================================
 * Computers - Next Computer ID (AJAX)
 * =============================================================================
 * Returns the suggested computer ID for a chosen lab, e.g. PC001, PC002 ...
 * Every room numbers its own computers starting from PC001, so the next ID is
 * the highest PC number already used in THAT room plus one.
 * =============================================================================
 */

require_once __DIR__ . '/../includes/auth.php';
require_login();
require_can('add_computer');

header('Content-Type: application/json; charset=utf-8');

$labId = (int)($_GET['lab_id'] ?? 0);
$response = ['ok' => false, 'computer_id' => ''];

if ($labId > 0) {
    $stmt = db()->prepare("SELECT id FROM labs WHERE id = ?");
    $stmt->execute([$labId]);
    if ($stmt->fetch()) {
        // Each room numbers its own computers (PC001, PC002, ...). The next ID
        // is the highest number already used in THIS room plus one, so an empty
        // room always starts at PC001 even if other rooms use the same codes.
        $response = ['ok' => true, 'computer_id' => next_computer_id($labId)];
    }
}

echo json_encode($response);
