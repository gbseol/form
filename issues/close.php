<?php
/**
 * =============================================================================
 * Issues - Close (POST)
 * =============================================================================
 * A staff member can close (mark resolved) an issue they reported; admins /
 * super admins can close any issue. Closing records who closed it and when.
 * =============================================================================
 */

require_once __DIR__ . '/../includes/auth.php';

require_login();
require_can('view_issues');

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

if (!can_close_issue($issue)) {
    set_flash('danger', 'You do not have permission to close that issue.');
    redirect('issues/index.php');
}

$userId   = (int)current_user()['id'];
$fixNotes = trim((string)($_POST['fix_notes'] ?? ''));
if ($fixNotes === '') {
    $fixNotes = $issue['fix_notes'];
}

// Closing an issue reverts the condition changes the reporter made when they
// created it: the linked computer goes back to a fully Working state.
if (!empty($issue['computer_id'])) {
    $computerId = (int)$issue['computer_id'];
    $stmt = db()->prepare(
        "UPDATE computers
            SET monitor_condition = 'Working', keyboard_condition = 'Working',
                mouse_condition = 'Working', cpu_condition = 'Working',
                status = 'Working', updated_by = ?
          WHERE id = ?"
    );
    $stmt->execute([$userId, $computerId]);
    log_activity('Reverted computer condition to Working (issue #' . $id . ' closed)',
        'computers', $computerId, null, ['status' => 'Working']);
}

$stmt = db()->prepare(
    "UPDATE issues
        SET status = 'resolved', fix_notes = ?, fixed_by = ?, fixed_at = NOW()
      WHERE id = ?"
);
$stmt->execute([$fixNotes, $userId, $id]);

$old = ['status' => $issue['status']];
$new = ['status' => 'resolved'];
log_activity('Closed issue #' . $id, 'issues', $id, $old, $new);

$msg = 'Issue #' . $id . ' was closed.';
if (!empty($issue['computer_id'])) {
    $msg .= ' The computer\'s condition was reverted to Working.';
}
set_flash('success', $msg);
redirect('issues/view.php?id=' . $id);
