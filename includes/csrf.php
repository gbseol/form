<?php
/**
 * =============================================================================
 * CSRF Protection Helpers
 * =============================================================================
 * Every form that changes data (POST) must include the token from csrf_field().
 * Every POST request must call verify_csrf() first.
 *
 * Usage:
 *   // In a form:
 *   <form method="post"> <?php echo csrf_field(); ?> ... </form>
 *
 *   // At the top of a POST handler:
 *   verify_csrf();
 * =============================================================================
 */

if (!defined('IN_APP')) {
    http_response_code(403);
    exit('Direct access is not allowed.');
}

/**
 * Return (and lazily create) the CSRF token for the current session.
 *
 * @return string
 */
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden CSRF input field for use inside forms.
 *
 * @return string
 */
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Verify the CSRF token submitted with a POST request.
 * Exits with a 419 status if the token is missing or invalid.
 */
function verify_csrf()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    $token = $_POST['csrf_token'] ?? '';
    if ($token === '' || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Security token validation failed. Please go back, reload the page and try again.');
    }
}
