<?php
/**
 * =============================================================================
 * Application Helper Functions
 * =============================================================================
 * Central collection of helpers used across every page:
 * output escaping, URLs, flash messages, settings, activity logging,
 * status badges, photo upload handling and simple SQL dump backup.
 * =============================================================================
 */

if (!defined('IN_APP')) {
    http_response_code(403);
    exit('Direct access is not allowed.');
}

// -----------------------------------------------------------------------------
// Output escaping (XSS prevention)
// -----------------------------------------------------------------------------

/**
 * Escape a value for safe HTML output.
 *
 * @param mixed $value
 * @return string
 */
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Escape a value for use inside an HTML attribute (same as e() but kept
 * separate for readability at call sites).
 *
 * @param mixed $value
 * @return string
 */
function attr($value)
{
    return e($value);
}

// -----------------------------------------------------------------------------
// URLs
// -----------------------------------------------------------------------------

/**
 * Detect the base path of the application (supports root and sub-folder installs).
 *
 * The application uses module sub-folders (computers/, users/, reports/...).
 * The URL base is therefore the directory that CONTAINS those module folders,
 * not the directory of the currently executing script.
 *
 * @return string e.g. "" or "/computer-management-system"
 */
function app_base_path()
{
    static $base = null;

    if ($base === null) {
        if (defined('BASE_URL') && BASE_URL !== '') {
            $base = rtrim(BASE_URL, '/');
        } else {
            // Start from the directory of the current script.
            $dir = dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php');
            $dir = str_replace('\\', '/', $dir);

            // Module folders live directly under the application root.
            // If we are inside one, walk one level up to the app root.
            $knownModules = ['computers', 'users', 'labs', 'reports', 'settings', 'issues',
                             'config', 'includes', 'assets', 'uploads', 'database'];
            if (in_array(basename($dir), $knownModules, true)) {
                $dir = dirname($dir);
            }

            $base = ($dir === '/' || $dir === '.' || $dir === '') ? '' : rtrim($dir, '/');
        }
    }
    return $base;
}

/**
 * Build an absolute path for the given application URL.
 *
 * @param string $path e.g. "computers/index.php"
 * @return string
 */
function base_url($path = '')
{
    return app_base_path() . '/' . ltrim($path, '/');
}

/**
 * Redirect the browser to an application URL and stop execution.
 *
 * @param string $url
 */
function redirect($url)
{
    header('Location: ' . base_url($url));
    exit;
}

/**
 * URL for an asset inside /assets.
 *
 * @param string $path
 * @return string
 */
function asset_url($path)
{
    return base_url('assets/' . ltrim($path, '/'));
}

/**
 * Human readable "time ago" string for a timestamp.
 *
 * @param string $datetime
 * @return string
 */
function time_ago($datetime)
{
    if (!$datetime) {
        return 'N/A';
    }
    $ts = strtotime($datetime);
    if ($ts === false) {
        return e($datetime);
    }
    $diff = time() - $ts;
    if ($diff < 60)      return 'just now';
    if ($diff < 3600)    return round($diff / 60) . ' min ago';
    if ($diff < 86400)   return round($diff / 3600) . ' hr ago';
    if ($diff < 2592000) return round($diff / 86400) . ' days ago';
    return date('M j, Y', $ts);
}

/**
 * Return true when the current user is allowed to create/edit a user of the
 * given role. Admins can only manage Staff accounts; Super Admins manage all.
 *
 * @param string $role
 * @return bool
 */
function can_manage_role($role)
{
    $user = current_user();
    if (!$user) {
        return false;
    }
    if ($user['role'] === 'super_admin') {
        return true;
    }
    // Admin may only handle staff accounts.
    return $role === 'staff';
}

/**
 * Return the list of user roles the current user may assign.
 *
 * @return array role_key => label
 */
function assignable_roles()
{
    $user = current_user();
    $all  = user_roles();
    if ($user && $user['role'] === 'super_admin') {
        return $all;
    }
    // Admin can only create staff accounts.
    return ['staff' => $all['staff']];
}

/**
 * URL for a file inside /uploads.
 *
 * @param string $path
 * @return string
 */
function uploads_url($path)
{
    return base_url('uploads/' . ltrim($path, '/'));
}

/**
 * URL of a user's profile photo ('' when none is set).
 *
 * @param string|null $filename
 * @return string
 */
function profile_photo_url($filename)
{
    if (!$filename) {
        return '';
    }
    return uploads_url('profiles/' . rawurlencode($filename));
}

/**
 * Validate and save a single uploaded profile photo.
 *
 * @param array $file    One entry from $_FILES
 * @param int   $userId  Owner of the photo (used for a unique prefix)
 * @return string|null   Stored filename or null when the upload is not usable
 */
function save_profile_photo($file, $userId)
{
    if (!isset($file['error']) || is_array($file['error'])) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
    if (!in_array($mime, $allowed, true)) {
        return null;
    }

    $ext = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
        'image/bmp'  => 'bmp',
    ][$mime];

    $dir = __DIR__ . '/../uploads/profiles';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $filename = 'u' . (int)$userId . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

    if (move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
        return $filename;
    }
    return null;
}

/**
 * Delete a stored profile photo file from disk (no-op when unknown).
 *
 * @param string|null $filename
 */
function delete_profile_photo_file($filename)
{
    if (!$filename) {
        return;
    }
    $path = __DIR__ . '/../uploads/profiles/' . basename($filename);
    if (is_file($path)) {
        @unlink($path);
    }
}

// -----------------------------------------------------------------------------
// Flash messages
// -----------------------------------------------------------------------------

/**
 * Queue a one-time flash message for the next page load.
 *
 * @param string $type    success|danger|warning|info
 * @param string $message
 */
function set_flash($type, $message)
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Fetch and clear all queued flash messages.
 *
 * @return array
 */
function get_flash()
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/**
 * Render queued flash messages as Bootstrap alerts.
 */
function render_flash()
{
    foreach (get_flash() as $f) {
        echo '<div class="alert alert-' . e($f['type']) . ' alert-dismissible fade show" role="alert">'
           . e($f['message'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
           . '</div>';
    }
}

// -----------------------------------------------------------------------------
// Settings (key/value store)
// -----------------------------------------------------------------------------

/**
 * Read a setting from the settings table (cached for the request lifetime).
 *
 * @param string $key
 * @param string $default
 * @return string
 */
function get_setting($key, $default = '')
{
    static $cache = null;

    if ($cache === null) {
        $cache = [];
        try {
            $stmt = db()->query("SELECT setting_key, setting_value FROM settings");
            while ($row = $stmt->fetch()) {
                $cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $ex) {
            // Table may not exist yet - return defaults silently.
        }
    }

    return (isset($cache[$key]) && $cache[$key] !== '') ? $cache[$key] : $default;
}

/**
 * Save a single setting to the database (upsert).
 *
 * @param string $key
 * @param string $value
 */
function set_setting($key, $value)
{
    $stmt = db()->prepare(
        "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
    );
    $stmt->execute([$key, $value]);
}

// -----------------------------------------------------------------------------
// Activity logging
// -----------------------------------------------------------------------------

/**
 * Write a new row into the activity_logs table.
 *
 * @param string $action     Human readable action, e.g. "Added computer LAB1-PC001"
 * @param string|null $tableName
 * @param int|null $recordId
 * @param mixed $oldValue    Old state (array/string) - stored as JSON
 * @param mixed $newValue    New state (array/string) - stored as JSON
 */
function log_activity($action, $tableName = null, $recordId = null, $oldValue = null, $newValue = null)
{
    $user     = current_user();
    $userId   = $user['id'] ?? null;
    $username = $user['username'] ?? 'guest';
    $ip       = $_SERVER['REMOTE_ADDR'] ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');

    $oldJson = (is_array($oldValue)) ? json_encode($oldValue) : $oldValue;
    $newJson = (is_array($newValue)) ? json_encode($newValue) : $newValue;

    try {
        $stmt = db()->prepare(
            "INSERT INTO activity_logs (user_id, username, action, table_name, record_id, old_value, new_value, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $username, $action, $tableName, $recordId, $oldJson, $newJson, $ip]);
    } catch (Exception $ex) {
        // Logging must never break the main action.
        error_log('log_activity failed: ' . $ex->getMessage());
    }
}

// -----------------------------------------------------------------------------
// Status helpers
// -----------------------------------------------------------------------------

/**
 * Return a Bootstrap badge HTML string for a computer status.
 *
 * @param string $status
 * @return string
 */
function status_badge($status)
{
    $colors = [
        'Working'           => 'success',
        'Not Working'       => 'danger',
        'Has Some Issues'   => 'warning',
    ];
    $color = $colors[$status] ?? 'secondary';
    return '<span class="badge bg-' . $color . ' status-badge">' . e($status) . '</span>';
}

/**
 * Allowed computer status values.
 *
 * @return array
 */
function computer_statuses()
{
    return ['Working', 'Not Working', 'Has Some Issues'];
}

/**
 * Allowed user roles.
 *
 * @return array
 */
function user_roles()
{
    return [
        'super_admin' => 'Super Admin',
        'admin'       => 'Admin',
        'staff'       => 'Staff',
    ];
}

/**
 * Return the display label for a role key.
 *
 * @param string $role
 * @return string
 */
function role_label($role)
{
    $roles = user_roles();
    return $roles[$role] ?? ucfirst($role);
}

// -----------------------------------------------------------------------------
// Computer ID helpers
// -----------------------------------------------------------------------------

/**
 * Compute the next sequential Computer ID for a lab (e.g. PC001, PC002, ...).
 * Each lab numbers its own computers starting from PC001, so the next ID is the
 * highest PC number already used in that lab plus one. The returned ID is
 * guaranteed to be unique within the same lab.
 *
 * @param int|null $labId
 * @return string
 */
function next_computer_id($labId)
{
    $max = 0;
    $stmt = db()->prepare(
        "SELECT computer_id FROM computers
          WHERE (lab_id = ? OR (lab_id IS NULL AND ? IS NULL))"
    );
    $stmt->execute([$labId, $labId]);
    while ($row = $stmt->fetch()) {
        if (preg_match('/^PC(\d+)$/i', $row['computer_id'], $m)) {
            $max = max($max, (int)$m[1]);
        }
    }

    // Skip any number that is already taken by a non-standard computer ID.
    $candidate = '';
    do {
        $candidate = 'PC' . str_pad($max + 1, 3, '0', STR_PAD_LEFT);
        $max++;
        $check = db()->prepare(
            "SELECT id FROM computers
              WHERE computer_id = ?
                AND (lab_id = ? OR (lab_id IS NULL AND ? IS NULL))"
        );
        $check->execute([$candidate, $labId, $labId]);
    } while ($check->fetch());

    return $candidate;
}

// -----------------------------------------------------------------------------
// Issue helpers
// -----------------------------------------------------------------------------

/**
 * Allowed issue categories.
 *
 * @return array
 */
function issue_categories()
{
    return [
        'Monitor', 'Keyboard', 'Mouse', 'CPU', 'RAM / Memory', 'Storage',
        'Power / UPS', 'Network', 'Software', 'Printer / Scanner', 'Other',
    ];
}

/**
 * Allowed issue statuses.
 *
 * @return array
 */
function issue_statuses()
{
    return ['open', 'in_progress', 'resolved'];
}

/**
 * Return a Bootstrap badge HTML string for an issue status.
 *
 * @param string $status
 * @return string
 */
function issue_status_badge($status)
{
    $colors = [
        'open'        => 'danger',
        'in_progress' => 'warning',
        'resolved'    => 'success',
    ];
    $color = $colors[$status] ?? 'secondary';
    $label = ucfirst(str_replace('_', ' ', $status));
    return '<span class="badge bg-' . $color . '">' . e($label) . '</span>';
}

/**
 * Create a new issue record.
 *
 * @param int|null $computerId
 * @param string   $category
 * @param string   $description
 * @param int|null $reporterId
 * @return int New issue id
 */
function create_issue($computerId, $category, $description, $reporterId)
{
    $stmt = db()->prepare(
        "INSERT INTO issues (computer_id, reported_by, issue_category, description) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([
        $computerId !== null ? (int)$computerId : null,
        $reporterId !== null ? (int)$reporterId : null,
        $category,
        $description,
    ]);
    return (int)db()->lastInsertId();
}

/**
 * Whether the current user may edit the given issue. Admins / super admins
 * can edit any issue; staff members can only edit the issues they reported.
 *
 * @param array $issue
 * @return bool
 */
function can_edit_issue($issue)
{
    if (can('manage_issues')) {
        return true;
    }
    $user = current_user();
    return $user !== null && (int)($issue['reported_by'] ?? 0) === (int)$user['id'];
}

/**
 * Whether the current user may close (mark resolved) the given issue.
 * Only issues that are not already resolved can be closed.
 *
 * @param array $issue
 * @return bool
 */
function can_close_issue($issue)
{
    return can_edit_issue($issue) && ($issue['status'] ?? '') !== 'resolved';
}

/**
 * URL used to edit an issue. When the issue is linked to a computer the staff
 * member is taken back to the same report page where issues are created, so
 * they can restore the computer's condition and the issue is resolved on save.
 * Issues without a computer fall back to the standalone edit page.
 *
 * @param array $issue
 * @return string
 */
function issue_edit_url($issue)
{
    $id = (int)$issue['id'];
    if (!empty($issue['computer_row_id'])) {
        return base_url('computers/edit.php?id=' . (int)$issue['computer_row_id'] . '&report=1&issue=' . $id);
    }
    return base_url('issues/edit.php?id=' . $id);
}

/**
 * Number of open (not resolved) issues. Used for the sidebar badge.
 * Staff members only see the count of issues they reported themselves;
 * admins / super admins see the overall count.
 *
 * @return int
 */
function open_issue_count()
{
    try {
        $user = current_user();
        if ($user && !can('manage_issues')) {
            $stmt = db()->prepare(
                "SELECT COUNT(*) FROM issues WHERE status <> 'resolved' AND reported_by = ?"
            );
            $stmt->execute([(int)$user['id']]);
            return (int)$stmt->fetchColumn();
        }
        return (int)db()->query("SELECT COUNT(*) FROM issues WHERE status <> 'resolved'")->fetchColumn();
    } catch (Exception $ex) {
        return 0;
    }
}

// -----------------------------------------------------------------------------
// Misc helpers
// -----------------------------------------------------------------------------

/**
 * Fetch distinct existing values for a column, used to build datalists on forms.
 *
 * @param string $column
 * @param string $table
 * @return array
 */
function distinct_values($column, $table = 'computers')
{
    $column = preg_replace('/[^A-Za-z0-9_]/', '', $column);
    $table  = preg_replace('/[^A-Za-z0-9_]/', '', $table);
    $rows = [];
    try {
        $stmt = db()->query("SELECT DISTINCT `$column` AS v FROM `$table` WHERE `$column` IS NOT NULL AND `$column` <> '' ORDER BY `$column`");
        while ($row = $stmt->fetch()) {
            $rows[] = $row['v'];
        }
    } catch (Exception $ex) {
        // Fall through and return an empty list.
    }
    return $rows;
}

/**
 * Human readable "time ago" string for a timestamp.
 *
 * @param string $datetime
 * @return string
 */

/**
 * Format a MySQL DATE column for display (empty -> "—").
 *
 * @param string|null $date
 * @return string
 */
function format_date($date)
{
    if (!$date || $date === '0000-00-00') {
        return '<span class="text-muted">—</span>';
    }
    $ts = strtotime($date);
    return $ts ? date('d M Y', $ts) : e($date);
}

/**
 * Clean a single uploaded photo and return the stored filename or null.
 *
 * @param array  $file    One entry from $_FILES['photos']
 * @param string $targetDir
 * @param int    $computerId Used to build a meaningful file prefix
 * @return string|null
 */
function save_uploaded_photo($file, $targetDir, $computerId)
{
    // Guard against missing / failed uploads.
    if (!isset($file['error']) || is_array($file['error'])) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return null;
    }

    // Verify the file really is an image using the MIME type.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
    if (!in_array($mime, $allowed, true)) {
        return null;
    }

    // Build a unique, safe filename.
    $ext = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
        'image/bmp'  => 'bmp',
    ][$mime];

    $filename = 'pc' . (int)$computerId . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $targetDir . '/' . $filename)) {
        return $filename;
    }
    return null;
}

// -----------------------------------------------------------------------------
// Simple SQL backup (no shell access required - works on InfinityFree)
// -----------------------------------------------------------------------------

/**
 * Generate a complete .sql dump of the configured database using PDO.
 * Used by the "Backup Database" feature.
 *
 * @return string
 */
function generate_sql_dump()
{
    $pdo   = db();
    $lines = [];

    $lines[] = "-- Lab Management System - Database Backup";
    $lines[] = "-- Generated: " . date('Y-m-d H:i:s');
    $lines[] = "-- Host: " . DB_HOST;
    $lines[] = "-- Database: " . DB_NAME;
    $lines[] = '';
    $lines[] = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";';
    $lines[] = 'SET time_zone = "+00:00";';
    $lines[] = '';

    // Retrieve all table names.
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        // 1. DROP TABLE IF EXISTS so the dump can be re-imported cleanly.
        $lines[] = 'DROP TABLE IF EXISTS `' . $table . '`;';
        $lines[] = '';

        // 2. CREATE TABLE statement.
        $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        $lines[] = $create['Create Table'] . ';';
        $lines[] = '';

        // 3. INSERT data.
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll();
        if (count($rows) > 0) {
            $lines[] = "INSERT INTO `$table` VALUES";
            $cols = array_keys($rows[0]);
            $colList = '`' . implode('`,`', $cols) . '`';
            $lines[] = "INSERT INTO `$table` ($colList) VALUES";
            $valueLines = [];
            foreach ($rows as $row) {
                $vals = [];
                foreach ($row as $val) {
                    if ($val === null) {
                        $vals[] = 'NULL';
                    } else {
                        $vals[] = $pdo->quote((string)$val);
                    }
                }
                $valueLines[] = '(' . implode(', ', $vals) . ')';
            }
            $lines[] = implode(",\n", $valueLines) . ';';
            $lines[] = '';
        }
        $lines[] = '';
    }

    return implode("\n", $lines);
}
