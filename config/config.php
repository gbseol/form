<?php
/**
 * =============================================================================
 * Configuration File
 * =============================================================================
 * IMPORTANT: Edit this file with YOUR InfinityFree MySQL database details.
 *
 * 1. Log in to your InfinityFree control panel.
 * 2. Open the "MySQL Databases" page.
 * 3. Create a database (or use the one that is auto created) and note the:
 *      - Database Host      (e.g. sql123.infinityfree.com)
 *      - Database Name      (e.g. if0_12345678_computer)
 *      - Database Username  (e.g. if0_12345678)
 *      - Database Password
 * 4. Import database/database.sql through phpMyAdmin.
 * 5. Put the values below.
 *
 * The database username on InfinityFree is usually the same as the database
 * name prefix (if0_XXXXXXXX). InfinityFree does NOT accept the host
 * "localhost" - you must use the value shown in your control panel.
 * =============================================================================
 */

// -----------------------------------------------------------------------------
// Database connection settings (EDIT THESE)
// -----------------------------------------------------------------------------
define('DB_HOST', '127.0.0.1');              // Database host from control panel
define('DB_NAME', 'computer_management');    // Database name
define('DB_USER', 'cms_user');               // Database username
define('DB_PASS', 'cms_pass');               // Database password
define('DB_CHARSET', 'utf8mb4');

// -----------------------------------------------------------------------------
// Application settings
// -----------------------------------------------------------------------------
// Leave BASE_URL empty for automatic detection (works when installed in a
// sub-folder such as /computer-management-system/ as well as in the root).
// You can force it if needed, e.g. define('BASE_URL', '/computer-management-system');
define('BASE_URL', '');

// Default timezone used by the application (change to your local zone).
// Full list: https://www.php.net/manual/en/timezones.php
date_default_timezone_set('UTC');

// Maximum upload size for photos (bytes). Default 5 MB.
define('MAX_UPLOAD_SIZE', 5242880);

// Version string appended to assets so browsers refresh them after updates.
define('APP_VERSION', '1.6.0');
