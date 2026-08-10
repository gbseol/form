<?php
/**
 * =============================================================================
 * Database Connection (PDO)
 * =============================================================================
 * Provides a single PDO connection (singleton) reused across the application.
 * All queries in the application use prepared statements.
 *
 * Usage:
 *   $stmt = db()->prepare("SELECT * FROM labs WHERE id = ?");
 *   $stmt->execute([$id]);
 *   $row = $stmt->fetch();
 * =============================================================================
 */

// Security: prevent direct browser access to this file.
if (!defined('IN_APP')) {
    http_response_code(403);
    exit('Direct access is not allowed.');
}

/**
 * Returns the shared PDO database connection, creating it on first call.
 *
 * @return PDO
 * @throws PDOException if the connection cannot be established
 */
function db()
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        $options = [
            // Throw exceptions on errors so bugs surface instead of silently failing
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Fetch rows as associative arrays by default
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Use real prepared statements (prevents SQL injection)
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Do not leak credentials to the browser. Show a friendly message
            // and log the real error to the server error log.
            error_log('Database connection failed: ' . $e->getMessage());
            http_response_code(500);
            exit('Database connection failed. Please check your config/config.php database credentials and try again.');
        }
    }

    return $pdo;
}
