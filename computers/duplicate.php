<?php
/**
 * =============================================================================
 * Computers - Duplicate
 * =============================================================================
 * POST only. Copies an existing computer (and its photos) under a new
 * unique Computer ID. The copied photos are physically duplicated on disk.
 * =============================================================================
 */

require_once __DIR__ . '/../includes/auth.php';

require_login();
require_can('add_computer');

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

// Generate the next sequential Computer ID in the same lab (e.g. PC002).
$newCode = next_computer_id($computer['lab_id']);

// Build the insert data: all columns except id, computer_id and audit columns.
$fields = [
    'lab_id', 'cpu', 'ram', 'storage_capacity',
    'monitor_condition', 'keyboard_condition', 'mouse_condition', 'cpu_condition',
    'status', 'remarks',
];

$cols = implode(', ', $fields);
$marks = implode(', ', array_fill(0, count($fields), '?'));

$params = [];
foreach ($fields as $f) {
    $params[] = $computer[$f];
}
$params[] = $newCode; // computer_id (unique code generated above)
$params[] = (int)current_user()['id']; // created_by
$params[] = (int)current_user()['id']; // updated_by

$insert = db()->prepare(
    "INSERT INTO computers ($cols, computer_id, created_by, updated_by)
     VALUES ($marks, ?, ?, ?)"
);
$insert->execute($params);
$newId = (int)db()->lastInsertId();

// Duplicate photos (physical copy + new DB rows).
$photoStmt = db()->prepare("SELECT filename FROM computer_photos WHERE computer_id = ?");
$photoStmt->execute([$id]);
$copyCount = 0;
foreach ($photoStmt->fetchAll() as $photo) {
    $src = __DIR__ . '/../uploads/computers/' . $photo['filename'];
    if (!is_file($src)) {
        continue;
    }
    $ext  = pathinfo($photo['filename'], PATHINFO_EXTENSION);
    $dest = 'pc' . $newId . '_copy_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (@copy($src, __DIR__ . '/../uploads/computers/' . $dest)) {
        $pstmt = db()->prepare("INSERT INTO computer_photos (computer_id, filename, is_primary) VALUES (?, ?, ?)");
        $pstmt->execute([$newId, $dest, ($copyCount === 0) ? 1 : 0]);
        $copyCount++;
    }
}

log_activity('Duplicated computer ' . $computer['computer_id'] . " as $newCode",
    'computers', $newId, $computer['computer_id'], $newCode);

set_flash('success', 'Computer duplicated as "' . $newCode . '" (' . $copyCount . ' photo(s) copied).');
redirect('computers/view.php?id=' . $newId);
