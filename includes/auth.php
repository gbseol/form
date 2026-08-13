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
 * Permissions are checked in two steps:
 *   1. The module the permission belongs to must be accessible to the user
 *      (explicitly assigned by a Super Admin, or the role default).
 *   2. The user's role must allow the permission (the matrix below).
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

    $user = current_user();
    if (!$user) {
        return false;
    }

    // The permission belongs to a module that can be switched off per user.
    $module = permission_module($permission);
    if ($module !== null && !can_access_module($module)) {
        return false;
    }

    $roles = $map[$permission] ?? [];
    return has_role($roles);
}

// -----------------------------------------------------------------------------
// Per-user module permissions (Super Admin assigns these via the user form)
// -----------------------------------------------------------------------------

/**
 * The switchable top-level modules shown to the Super Admin on the user form.
 *
 * @return array module key => display label
 */
function module_list()
{
    return [
        'dashboard' => 'Dashboard',
        'computers' => 'Computers',
        'labs'      => 'Labs',
        'users'     => 'Users',
        'reports'   => 'Reports',
        'issues'    => 'Issues',
    ];
}

/**
 * Modules a role can access by default (used when a user has no explicit
 * permission assignment yet - i.e. permissions is NULL).
 *
 * @param string $role
 * @return array
 */
function role_default_modules($role)
{
    $map = [
        'super_admin' => ['dashboard', 'computers', 'labs', 'users', 'reports', 'issues'],
        'admin'       => ['dashboard', 'computers', 'labs', 'users', 'reports', 'issues'],
        'staff'       => ['dashboard', 'computers', 'issues'],
    ];
    return $map[$role] ?? ['dashboard'];
}

/**
 * Map a fine-grained permission to the module that controls it. Permissions
 * that are not part of a switchable module (activity logs, settings, ...)
 * return null and therefore stay role-only.
 *
 * @param string $permission
 * @return string|null
 */
function permission_module($permission)
{
    $map = [
        'manage_users'    => 'users',
        'delete_users'    => 'users',
        'reset_passwords' => 'users',

        'manage_labs'     => 'labs',
        'delete_labs'     => 'labs',

        'add_computer'    => 'computers',
        'edit_computer'   => 'computers',
        'update_status'   => 'computers',
        'delete_computer' => 'computers',
        'view_computer'   => 'computers',

        'view_reports'    => 'reports',
        'export_reports'  => 'reports',

        'report_issue'    => 'issues',
        'view_issues'     => 'issues',
        'manage_issues'   => 'issues',
    ];
    return $map[$permission] ?? null;
}

/**
 * The modules the current user can access. Uses the explicitly assigned
 * permissions from the users table when present, otherwise the role defaults.
 * Super Admin always has every module.
 *
 * @return array
 */
function user_modules()
{
    static $modules = null;

    if ($modules !== null) {
        return $modules;
    }

    $user = current_user();
    if (!$user) {
        return $modules = [];
    }

    if ($user['role'] === 'super_admin') {
        return $modules = array_keys(module_list());
    }

    $stmt = db()->prepare("SELECT permissions FROM users WHERE id = ?");
    $stmt->execute([(int)$user['id']]);
    $perms = $stmt->fetchColumn();

    if ($perms === null) {
        $modules = role_default_modules($user['role']);
    } else {
        $modules = $perms === ''
            ? []
            : array_values(array_filter(array_map('trim', explode(',', (string)$perms))));
    }

    return $modules;
}

/**
 * True when the current user can access the given module.
 *
 * @param string $module
 * @return bool
 */
function can_access_module($module)
{
    return in_array($module, user_modules(), true);
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
