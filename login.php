<?php
/**
 * =============================================================================
 * Login Page
 * =============================================================================
 * Authenticates a user with username + password.
 *
 * Security features:
 *   - Prepared statements (SQL injection safe)
 *   - password_verify() against bcrypt hashes
 *   - CSRF token on the form
 *   - Basic brute-force protection (locks the account after 5 failed tries)
 *   - Regenerates the session id on successful login (session fixation)
 * =============================================================================
 */

require_once __DIR__ . '/includes/auth.php';

// Already logged in? Go to the dashboard.
if (is_logged_in()) {
    redirect('dashboard.php');
}

$site_name   = get_setting('site_name', 'Computer Lab Management System');
$logo_file   = get_setting('logo', '');
$error       = '';
$lockMessage = '';
$username    = trim($_POST['username'] ?? '');

// Simple brute-force lockout: 5 attempts -> locked for 15 minutes.
$attempts   = $_SESSION['login_attempts'] ?? 0;
$lockedUntil = $_SESSION['login_locked_until'] ?? 0;

if (time() < $lockedUntil) {
    $minutes = (int)ceil(($lockedUntil - time()) / 60);
    $lockMessage = 'Too many failed login attempts. Try again in ' . $minutes . ' minute(s).';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if ($lockMessage !== '') {
        $error = $lockMessage;
    } else {
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Please enter both username and password.';
        } else {
            // Fetch the user by username using a prepared statement.
            $stmt = db()->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Reset the failed-attempt counter on success.
                unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);

                if ($user['status'] === 'inactive') {
                    $error = 'This account has been deactivated. Please contact the administrator.';
                } else {
                    // Prevent session fixation by issuing a new session id.
                    session_regenerate_id(true);

                    // Store only the needed fields in the session.
                    $_SESSION['user'] = [
                        'id'            => (int)$user['id'],
                        'username'      => $user['username'],
                        'full_name'     => $user['full_name'],
                        'email'         => $user['email'],
                        'role'          => $user['role'],
                        'status'        => $user['status'],
                        'profile_photo' => $user['profile_photo'] ?? null,
                        'theme'         => $user['theme'] ?? 'light',
                    ];

                    // Update the last login timestamp.
                    $upd = db()->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $upd->execute([$user['id']]);

                    log_activity('Logged in', 'users', (int)$user['id']);
                    redirect('dashboard.php');
                }
            } else {
                // Failed attempt bookkeeping.
                $attempts++;
                $_SESSION['login_attempts'] = $attempts;

                if ($attempts >= 5) {
                    $_SESSION['login_locked_until'] = time() + 900; // 15 minutes
                    $error = 'Too many failed login attempts. Your login is locked for 15 minutes.';
                } else {
                    $error = 'Invalid username or password. Attempts left: ' . (5 - $attempts) . '.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?php echo e($site_name); ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="<?php echo asset_url('images/favicon.png'); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?php echo asset_url('css/style.css'); ?>?v=<?php echo APP_VERSION; ?>" rel="stylesheet">
    <style>
        .login-brand {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .login-brand .logo {
            height: 64px;
            width: 64px;
            object-fit: contain;
            border-radius: 12px;
            margin-bottom: .5rem;
        }
        .login-brand .logo-placeholder {
            font-size: 3rem;
            color: var(--primary, #0d6efd);
        }
    </style>
    <script>
        // Respect the visitor's saved theme on the login page too.
        (function () {
            try {
                if (localStorage.getItem('clms_theme') === 'dark') {
                    document.documentElement.classList.add('dark-mode');
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                }
            } catch (err) {}
        })();
    </script>
</head>
<body class="app-body">
<div class="login-page">
    <div class="login-blob login-blob-1"></div>
    <div class="login-blob login-blob-2"></div>
    <div class="login-grid"></div>
    <div class="card login-card">
        <div class="card-body p-4 p-md-5">
            <div class="login-brand">
                <?php if ($logo_file && file_exists(__DIR__ . '/uploads/logos/' . $logo_file)): ?>
                    <img src="<?php echo uploads_url('logos/' . rawurlencode($logo_file)); ?>" alt="Logo" class="logo">
                <?php else: ?>
                    <div class="logo-placeholder"><i class="bi bi-pc-display"></i></div>
                <?php endif; ?>
                <h4 class="mb-1"><?php echo e($site_name); ?></h4>
                <p class="text-muted mb-0">Sign in to your account</p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i><?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo base_url('login.php'); ?>" autocomplete="off">
                <?php echo csrf_field(); ?>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?php echo e($username); ?>" required autofocus maxlength="50">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                    </button>
                </div>
            </form>

            <hr class="my-4">
            <p class="text-center text-muted small mb-0">
                Default login &mdash; Username: <code>admin</code> &nbsp; Password: <code>Admin@123</code><br>
                <span class="text-danger">Please change the password after your first login.</span>
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon = this.querySelector('i');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
</script>
</body>
</html>
