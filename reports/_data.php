<?php
/**
 * =============================================================================
 * Reports - Shared Data Builder
 * =============================================================================
 * Builds the row sets used by the report pages, the printable pages and the
 * CSV / Excel exports so that all of them always agree.
 *
 * Included by report pages. Reads filters from $_GET.
 * =============================================================================
 */

if (!defined('IN_APP')) {
    http_response_code(403);
    exit('Direct access is not allowed.');
}

/**
 * Columns for computer inventory style reports.
 *
 * @return array
 */
function computer_report_columns()
{
    return [
        'Computer ID', 'Lab', 'CPU', 'RAM', 'Storage Capacity',
        'Monitor Condition', 'Keyboard Condition', 'Mouse Condition',
        'Status', 'Remarks',
    ];
}

/**
 * Build one flat row (assoc label => value) from a computers record.
 *
 * @param array $c
 * @return array
 */
function computer_report_row(array $c)
{
    return [
        'Computer ID'        => $c['computer_id'],
        'Lab'                => $c['lab_name'] ?? '',
        'CPU'                => $c['cpu'],
        'RAM'                => $c['ram'],
        'Storage Capacity'   => $c['storage_capacity'],
        'Monitor Condition'  => $c['monitor_condition'],
        'Keyboard Condition' => $c['keyboard_condition'],
        'Mouse Condition'    => $c['mouse_condition'],
        'CPU Condition'      => $c['cpu_condition'],
        'Status'             => $c['status'],
        'Remarks'            => $c['remarks'],
    ];
}

/**
 * Fetch computer rows for a report type.
 *
 * Report types: 'inventory', 'faulty', 'working', 'maintenance', 'lab'
 * Extra filters read from $_GET: lab_id
 *
 * @param string $type
 * @return array
 */
function computer_report($type)
{
    $where  = [];
    $params = [];

    if ($type === 'faulty') {
        $where[] = "c.status = 'Not Working'";
    } elseif ($type === 'working') {
        $where[] = "c.status = 'Working'";
    } elseif ($type === 'maintenance') {
        $where[] = "c.status = 'Has Some Issues'";
    } elseif ($type === 'lab') {
        $labId = (int)($_GET['lab_id'] ?? 0);
        if ($labId > 0) {
            $where[] = "c.lab_id = ?";
            $params[] = $labId;
        }
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $stmt = db()->prepare(
        "SELECT c.*, l.name AS lab_name
           FROM computers c
           LEFT JOIN labs l ON l.id = c.lab_id
           $whereSql
          ORDER BY (l.name IS NULL), l.name ASC, c.computer_id"
    );
    $stmt->execute($params);

    $rows = [];
    while ($row = $stmt->fetch()) {
        $rows[] = computer_report_row($row);
    }
    return $rows;
}

/**
 * Fetch activity log rows for the activity report / export.
 *
 * Filters read from $_GET: from (date), to (date), user_id
 *
 * @return array
 */
function activity_report()
{
    $where  = [];
    $params = [];

    if (!empty($_GET['from'])) {
        $where[] = 'DATE(a.created_at) >= ?';
        $params[] = date('Y-m-d', strtotime($_GET['from']));
    }
    if (!empty($_GET['to'])) {
        $where[] = 'DATE(a.created_at) <= ?';
        $params[] = date('Y-m-d', strtotime($_GET['to']));
    }
    if (!empty($_GET['user_id'])) {
        $where[] = 'a.user_id = ?';
        $params[] = (int)$_GET['user_id'];
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $stmt = db()->prepare(
        "SELECT a.*, u.full_name
           FROM activity_logs a
           LEFT JOIN users u ON u.id = a.user_id
           $whereSql
          ORDER BY a.created_at DESC
          LIMIT 5000"
    );
    $stmt->execute($params);

    $rows = [];
    while ($row = $stmt->fetch()) {
        $rows[] = [
            'User'     => $row['username'],
            'Date'     => date('Y-m-d', strtotime($row['created_at'])),
            'Time'     => date('H:i:s', strtotime($row['created_at'])),
            'IP Address' => $row['ip_address'],
            'Action'   => $row['action'],
            'Old Value' => $row['old_value'],
            'New Value' => $row['new_value'],
        ];
    }
    return $rows;
}

/**
 * Return the column names for the activity report.
 *
 * @return array
 */
function activity_report_columns()
{
    return ['User', 'Date', 'Time', 'IP Address', 'Action', 'Old Value', 'New Value'];
}

/**
 * Columns for the issues history report / export.
 *
 * @return array
 */
function issues_report_columns()
{
    return [
        'S No', 'Ticket ID', 'Created By', 'Issue Date', 'Issue Time', 'Lab', 'PC Number',
        'Issue', 'Fixed By', 'Fix Date', 'Fix Time', 'Solution', 'Status',
    ];
}

/**
 * Fetch all reported issues with the details needed for the history export.
 *
 * Filter read from $_GET: status (open / in_progress / resolved).
 *
 * @return array
 */
function issues_report()
{
    $where  = [];
    $params = [];

    $fStatus = trim($_GET['status'] ?? '');
    if ($fStatus !== '' && in_array($fStatus, issue_statuses(), true)) {
        $where[]  = 'i.status = ?';
        $params[] = $fStatus;
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $stmt = db()->prepare(
        "SELECT i.id, i.issue_category, i.description, i.status,
                i.fixed_at, i.fix_notes, i.created_at,
                c.computer_id,
                l.name AS lab_name,
                u.username AS reporter_username,
                f.username AS fixer_username
           FROM issues i
           LEFT JOIN computers c ON c.id = i.computer_id
           LEFT JOIN labs l ON l.id = c.lab_id
           LEFT JOIN users u ON u.id = i.reported_by
           LEFT JOIN users f ON f.id = i.fixed_by
            $whereSql
           ORDER BY i.created_at ASC, i.id ASC
           LIMIT 10000"
    );
    $stmt->execute($params);

    $rows = [];
    $serial = 1;
    while ($row = $stmt->fetch()) {
        $rows[] = [
            'S No'        => $serial++,
            'Ticket ID'   => $row['id'],
            'Created By'  => $row['reporter_username'] ?? '',
            'Issue Date'  => $row['created_at'] ? date('Y-m-d', strtotime($row['created_at'])) : '',
            'Issue Time'  => $row['created_at'] ? date('H:i:s', strtotime($row['created_at'])) : '',
            'Lab'         => $row['lab_name'] ?? '',
            'PC Number'   => $row['computer_id'] ?? '',
            'Issue'       => $row['description'],
            'Fixed By'    => $row['fixer_username'] ?? '',
            'Fix Date'    => $row['fixed_at'] ? date('Y-m-d', strtotime($row['fixed_at'])) : '',
            'Fix Time'    => $row['fixed_at'] ? date('H:i:s', strtotime($row['fixed_at'])) : '',
            'Solution'    => $row['fix_notes'] ?? '',
            'Status'      => ucfirst(str_replace('_', ' ', $row['status'])),
        ];
    }
    return $rows;
}
