<?php
/**
 * =============================================================================
 * Settings - Backup Database
 * =============================================================================
 * Generates a full .sql dump of the database (pure PHP, no shell commands,
 * so it works on restricted hosts such as InfinityFree) and downloads it.
 * =============================================================================
 */

require_once __DIR__ . '/../includes/auth.php';

require_login();
require_can('backup_restore');

$dump   = generate_sql_dump();
$stamp  = date('Y-m-d_H-i-s');
$name   = 'computer_management_backup_' . $stamp . '.sql';

log_activity('Downloaded database backup', 'settings', null, null, null);

// Clean any buffered output so the file is not corrupted.
while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/sql; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $name . '"');
header('Content-Length: ' . strlen($dump));
header('Cache-Control: no-cache, no-store, must-revalidate');

echo $dump;
exit;
