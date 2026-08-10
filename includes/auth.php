<?php
/**
 * =============================================================================
 * Session & Authentication
 * =============================================================================
 * - Starts a hardened PHP session.
 * - Loads the configuration, database and helper libraries.
 * - Provides require_login() and permission helpers for the role system.
 *
 * Roles:
 *   super_admin : full control, manages users, sees logs, exports, settings
 *   admin       : manages computers, labs, reports, exports, staff users
 *   staff       : edits computers, reports issues, cannot add or delete
 * =============================================================================
 */

// -----------------------------------------------------------------------------
// Start a hardened session (must run before any output)
// -----------------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,                 // Session cookie (expires when browser closes)
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,              // JavaScript cannot read the cookie
        'samesite' => 'Lax',             // Basic CSRF hardening for cookies
    ]);
    session_start();
}

// Define a constant so include-only files refuse direct access
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Load core libraries
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';

/**
 * Return the currently logged-in user array (from the session) or null.
 *
 * @return array|null
 */
function current_user()
{
    return $_SESSION['user'] ?? null;
}

/**
 * Return true when a user is logged in.
 *
 * @return bool
 */
function is_logged_in()
{
    return !empty($_SESSION['user']);
}

/**
 * Redirect to the login page when the visitor is not authenticated.
 */
function require_login()
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

/**
 * Return true when the current user holds one of the given roles.
 *
 * @param string|array $roles
 * @return bool
 */
function has_role($roles)
{
    $user = current_user();
    if (!$user) {
        return false;
    }
    $roles = (array)$roles;
    return in_array($user['role'], $roles, true);
}

/**
 * Role/permission matrix. Every privileged action in the application is
 * guarded through this single table, keeping permissions easy to maintain.
 *
 * @param string $permission
 * @return bool
 */
function can($permission)
{
    static $map = null;

    if ($map === null) {
        $map = [
            // --- Users -------------------------------------------------------
            'manage_users'     => ['super_admin', 'admin'],      // create / edit users
            'delete_users'     => ['super_admin'],               // delete any user
            'reset_passwords'  => ['super_admin', 'admin'],      // reset a user's password

            // --- Labs --------------------------------------------------------
            'manage_labs'      => ['super_admin', 'admin'],
            'delete_labs'      => ['super_admin', 'admin'],

            // --- Computers ---------------------------------------------------
            'add_computer'     => ['super_admin', 'admin'],
            'edit_computer'    => ['super_admin', 'admin'],
            'update_status'    => ['super_admin', 'admin'],
            'delete_computer'  => ['super_admin', 'admin'],
            'view_computer'    => ['super_admin', 'admin', 'staff'],

            // --- Reports -----------------------------------------------------
            'view_reports'     => ['super_admin', 'admin'],
            'export_reports'   => ['super_admin', 'admin'],

            // --- Issues ------------------------------------------------------
            'report_issue'     => ['super_admin', 'admin', 'staff'],
            'view_issues'      => ['super_admin', 'admin', 'staff'],
            'manage_issues'    => ['super_admin', 'admin'],

            // --- Activity logs -----------------------------------------------
            'view_logs'        => ['super_admin'],

            // --- Settings ----------------------------------------------------
            'manage_settings'  => ['super_admin'],
            'backup_restore'   => ['super_admin'],
        ];
    }

    $roles = $map[$permission] ?? [];
    return has_role($roles);
}

/**
 * Require a permission; otherwise flash an error and bounce to the dashboard.
 *
 * @param string $permission
 */
function require_can($permission)
{
    if (!can($permission)) {
        set_flash('danger', 'You do not have permission to perform that action.');
        redirect('dashboard.php');
    }
}
