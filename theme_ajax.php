<?php
/**
 * =============================================================================
 * Theme Preference (AJAX endpoint)
 * =============================================================================
 * Saves the current user's interface theme (light / dark) so it follows them
 * across devices and browsers.
 *
 *   POST theme_ajax.php (theme=dark&csrf_token=...) -> JSON {"ok":true}
 *
 * Requires an authenticated session and a valid CSRF token.
 * =============================================================================
 */

require_once __DIR__ . '/includes/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);
    exit;
}

verify_csrf();

$theme = $_POST['theme'] ?? '';
if (!in_array($theme, ['light', 'dark'], true)) {
    echo json_encode(['ok' => false, 'error' => 'Invalid theme value.']);
    exit;
}

$user = current_user();
$stmt = db()->prepare("UPDATE users SET theme = ? WHERE id = ?");
$stmt->execute([$theme, (int)$user['id']]);

$_SESSION['user']['theme'] = $theme;

echo json_encode(['ok' => true]);
