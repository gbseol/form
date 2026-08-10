<?php
/**
 * =============================================================================
 * Dashboard Chart Data (AJAX endpoint)
 * =============================================================================
 * Returns JSON used by assets/js/main.js to render the dashboard charts.
 *
 *   GET dashboard_ajax.php -> JSON
 *
 * Requires an authenticated session. Intended to be called from the dashboard
 * via fetch(). Output is JSON only.
 * =============================================================================
 */

require_once __DIR__ . '/includes/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

// Disable error output so it cannot corrupt the JSON response.
$response = [];

// -----------------------------------------------------------------------------
// 1. Computers per Lab
// -----------------------------------------------------------------------------
$labs = [];
$stmt = db()->query(
    "SELECT l.name, COUNT(c.id) AS total
       FROM labs l
       LEFT JOIN computers c ON c.lab_id = l.id
      WHERE l.status = 'active'
      GROUP BY l.id
      ORDER BY l.name"
);
while ($row = $stmt->fetch()) {
    $labs[] = ['name' => $row['name'], 'total' => (int)$row['total']];
}
$response['labs'] = $labs;

// -----------------------------------------------------------------------------
// 2. Computers by status (Working / Not Working / Has Some Issues)
// -----------------------------------------------------------------------------
$status = [];
$stmt = db()->query("SELECT status, COUNT(*) AS total FROM computers GROUP BY status");
while ($row = $stmt->fetch()) {
    $status[] = ['status' => $row['status'], 'total' => (int)$row['total']];
}
$response['status'] = $status;

// -----------------------------------------------------------------------------
// 3. Issues for staff (Open vs Solved) - issues reported by the current user
// -----------------------------------------------------------------------------
$issues = [];
if (has_role('staff')) {
    $uid = (int)current_user()['id'];
    $stmt = db()->prepare(
        "SELECT
            SUM(status IN ('open','in_progress')) AS open_total,
            SUM(status = 'resolved')              AS solved_total
           FROM issues
          WHERE reported_by = ?"
    );
    $stmt->execute([$uid]);
    $row = $stmt->fetch();
    $issues = [
        ['label' => 'Open Issues',  'total' => (int)($row['open_total'] ?? 0)],
        ['label' => 'Solved Issues', 'total' => (int)($row['solved_total'] ?? 0)],
    ];
}
$response['issues'] = $issues;

// -----------------------------------------------------------------------------
// 3. Monthly updates - computers added per month for the last 12 months
// -----------------------------------------------------------------------------
$monthly = [];
$stmt = db()->query(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS total
       FROM computers
      WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
      GROUP BY ym
      ORDER BY ym"
);
$map = [];
while ($row = $stmt->fetch()) {
    $map[$row['ym']] = (int)$row['total'];
}

// Fill missing months with zero so the line chart is continuous.
for ($i = 11; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("-$i months"));
    $monthly[] = [
        'label' => date('M y', strtotime($ym . '-01')),
        'total' => $map[$ym] ?? 0,
    ];
}
$response['monthly'] = $monthly;

echo json_encode($response);
