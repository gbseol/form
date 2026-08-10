<?php
/**
 * =============================================================================
 * Logout
 * =============================================================================
 * Logs the user's activity, destroys the session and returns to the login page.
 * =============================================================================
 */

require_once __DIR__ . '/includes/auth.php';

// Record the logout before destroying the session.
if (is_logged_in()) {
    $user = current_user();
    log_activity('Logged out', 'users', (int)$user['id']);
}

// Clear all session data and expire the cookie.
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']);
}

session_destroy();

redirect('login.php');
