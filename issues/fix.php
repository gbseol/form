<?php
/**
 * =============================================================================
 * Issues - Update Status (POST)
 * =============================================================================
 * Administrators change an issue's status (open / in progress / resolved) and
 * record fix notes. Resolved issues store who fixed it and when.
 * =============================================================================
 */

require_once __DIR__ . '/../includes/auth.php';

require_login();
require_can('manage_issues');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('issues/index.php');
}
verify_csrf();

$id = (int)($_POST['id'] ?? 0);

$stmt = db()->prepare("SELECT * FROM issues WHERE id = ?");
$stmt->execute([$id]);
$issue = $stmt->fetch();

if (!$issue) {
    set_flash('danger', 'Issue not found.');
    redirect('issues/index.php');
}

$status    = in_array($_POST['status'] ?? '', issue_statuses(), true) ? $_POST['status'] : $issue['status'];
$fixNotes  = trim((string)($_POST['fix_notes'] ?? ''));
$userId    = (int)current_user()['id'];

$old = $issue;
$new = $issue;
$new['status']    = $status;
$new['fix_notes'] = $fixNotes;

if ($status === 'resolved') {
    $stmt = db()->prepare(
        "UPDATE issues
            SET status = ?, fix_notes = ?, fixed_by = ?, fixed_at = NOW()
          WHERE id = ?"
    );
    $stmt->execute([$status, $fixNotes, $userId, $id]);
} else {
    $stmt = db()->prepare(
        "UPDATE issues
            SET status = ?, fix_notes = ?, fixed_by = NULL, fixed_at = NULL
          WHERE id = ?"
    );
    $stmt->execute([$status, $fixNotes, $id]);
}

log_activity('Updated issue #' . $id . ' to "' . $status . '"', 'issues', $id, $old, $new);

set_flash('success', 'Issue #' . $id . ' was updated.');
redirect('issues/view.php?id=' . $id);
