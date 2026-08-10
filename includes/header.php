<?php
/**
 * =============================================================================
 * Application Header / Layout
 * =============================================================================
 * Outputs the HTML <head>, the sidebar navigation, the top bar and the opening
 * of the content area. Every normal (non-print, non-export) page includes this.
 *
 * Required variables before including this file:
 *   $page_title - string, shown in the browser tab and the top bar.
 * =============================================================================
 */

require_once __DIR__ . '/auth.php';
require_login();

$current_user = current_user();
$site_name    = get_setting('site_name', 'Computer Lab Management System');
$logo_file    = get_setting('logo', '');
$theme_color  = get_setting('theme_color', '#0d6efd');

// Resolve the current script name for the active navigation highlight.
$current_script = basename($_SERVER['SCRIPT_NAME'] ?? '');
$current_dir    = basename(dirname($_SERVER['SCRIPT_NAME'] ?? ''));

/**
 * Mark a navigation item as active.
 */
function nav_active($dir, $script = '')
{
    global $current_dir, $current_script;
    if ($script !== '' && $current_script === $script) {
        return 'active';
    }
    if ($script === '' && $current_dir === $dir) {
        return 'active';
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title ?? 'Dashboard'); ?> | <?php echo e($site_name); ?></title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" type="image/png" href="<?php echo asset_url('images/favicon.png'); ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Chart.js for dashboard charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <!-- Custom styles -->
    <link href="<?php echo asset_url('css/style.css'); ?>?v=<?php echo APP_VERSION; ?>" rel="stylesheet">

    <style>
        /* Theme colour injected from the Settings page */
        :root {
            --primary: <?php echo e($theme_color); ?>;
        }
        .btn-primary, .btn-outline-primary { --bs-btn-border-color: var(--primary); }
        .btn-primary { --bs-btn-bg: var(--primary); --bs-btn-hover-bg: color-mix(in srgb, var(--primary) 85%, #000); --bs-btn-active-bg: var(--primary); --bs-btn-disabled-bg: var(--primary); }
        .btn-outline-primary { --bs-btn-color: var(--primary); --bs-btn-hover-bg: var(--primary); --bs-btn-hover-border-color: var(--primary); }
        .text-primary, .link-primary { color: var(--primary) !important; }
        .bg-primary { background-color: var(--primary) !important; }
        .border-primary { border-color: var(--primary) !important; }
        .page-item.active .page-link { background-color: var(--primary); border-color: var(--primary); }
        .form-check-input:checked, .form-select:focus, .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 .2rem color-mix(in srgb, var(--primary) 25%, transparent); }
        a { color: var(--primary); }
    </style>

    <script>
        // Apply the user's saved theme as early as possible to avoid a light-mode flash.
        (function () {
            var theme = '<?php echo e($current_user['theme'] ?? 'light'); ?>';
            try {
                var saved = localStorage.getItem('clms_theme');
                if (saved === 'dark' || saved === 'light') { theme = saved; }
            } catch (err) {}
            if (theme === 'dark') {
                document.documentElement.classList.add('dark-mode');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            }
        })();
    </script>
</head>
<body class="app-body">

<div class="app-wrapper">
    <!-- ============================ SIDEBAR ============================ -->
    <aside class="app-sidebar" id="appSidebar">
        <div class="sidebar-brand">
            <?php if ($logo_file && file_exists(__DIR__ . '/../uploads/logos/' . $logo_file)): ?>
                <img src="<?php echo uploads_url('logos/' . rawurlencode($logo_file)); ?>" alt="Logo" class="sidebar-logo">
            <?php else: ?>
                <i class="bi bi-pc-display"></i>
            <?php endif; ?>
            <span class="sidebar-brand-text"><?php echo e($site_name); ?></span>
        </div>

        <nav class="sidebar-nav">
            <a href="<?php echo base_url('dashboard.php'); ?>" class="nav-link <?php echo ($current_script === 'dashboard.php') ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i><span>Dashboard</span>
            </a>

            <?php if (!has_role('staff')): ?>
            <a href="<?php echo base_url('computers/index.php'); ?>" class="nav-link <?php echo nav_active('computers'); ?>">
                <i class="bi bi-pc-display"></i><span>Computers</span>
            </a>
            <?php endif; ?>

            <?php if (can('add_computer')): ?>
            <a href="<?php echo base_url('computers/add.php'); ?>" class="nav-link <?php echo ($current_script === 'add.php' && $current_dir === 'computers') ? 'active' : ''; ?>">
                <i class="bi bi-plus-circle"></i><span>Add Computer</span>
            </a>
            <?php endif; ?>

            <?php if (can('manage_labs')): ?>
            <a href="<?php echo base_url('labs/index.php'); ?>" class="nav-link <?php echo nav_active('labs'); ?>">
                <i class="bi bi-building"></i><span>Labs</span>
            </a>
            <?php endif; ?>

            <?php if (can('manage_users')): ?>
            <a href="<?php echo base_url('users/index.php'); ?>" class="nav-link <?php echo nav_active('users'); ?>">
                <i class="bi bi-people"></i><span>Users</span>
            </a>
            <?php endif; ?>

            <?php if (can('view_logs')): ?>
            <a href="<?php echo base_url('reports/activity.php'); ?>" class="nav-link <?php echo ($current_script === 'activity.php') ? 'active' : ''; ?>">
                <i class="bi bi-journal-text"></i><span>Activity Logs</span>
            </a>
            <?php endif; ?>

            <?php if (can('view_reports')): ?>
            <a href="<?php echo base_url('reports/index.php'); ?>" class="nav-link <?php echo nav_active('reports'); ?>">
                <i class="bi bi-bar-chart"></i><span>Reports</span>
            </a>
            <?php endif; ?>

            <?php if (can('view_issues')): ?>
            <a href="<?php echo base_url('issues/index.php'); ?>" class="nav-link <?php echo nav_active('issues'); ?>">
                <i class="bi bi-bug"></i><span>Issues</span>
                <?php if (open_issue_count() > 0): ?>
                    <span class="badge text-bg-danger ms-auto"><?php echo open_issue_count(); ?></span>
                <?php endif; ?>
            </a>
            <?php endif; ?>

            <div class="sidebar-section-title">Account</div>

            <a href="<?php echo base_url('settings/profile.php'); ?>" class="nav-link <?php echo ($current_script === 'profile.php') ? 'active' : ''; ?>">
                <i class="bi bi-person-circle"></i><span>My Profile</span>
            </a>

            <a href="<?php echo base_url('settings/change_password.php'); ?>" class="nav-link <?php echo ($current_script === 'change_password.php') ? 'active' : ''; ?>">
                <i class="bi bi-key"></i><span>Change Password</span>
            </a>

            <?php if (can('manage_settings')): ?>
            <a href="<?php echo base_url('settings/index.php'); ?>" class="nav-link <?php echo ($current_script === 'index.php' && $current_dir === 'settings') ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i><span>Settings</span>
            </a>
            <?php endif; ?>
        </nav>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- ============================ MAIN AREA ============================ -->
    <div class="app-main">
        <!-- Top bar -->
        <header class="app-topbar">
            <button class="btn btn-link text-dark sidebar-toggle" id="sidebarToggle" type="button" aria-label="Toggle navigation">
                <i class="bi bi-list fs-3"></i>
            </button>

            <h1 class="topbar-title"><?php echo e($page_title ?? 'Dashboard'); ?></h1>

            <div class="topbar-right">
                <button type="button" class="btn btn-light theme-toggle me-2" id="themeToggle"
                        data-bs-toggle="tooltip" data-bs-placement="bottom"
                        title="Toggle light / dark theme" aria-label="Toggle light / dark theme">
                    <i class="bi bi-moon-stars d-none"></i>
                    <i class="bi bi-sun d-none"></i>
                </button>

                <div class="dropdown">
                <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                    <?php $currentPhotoUrl = profile_photo_url($current_user['profile_photo'] ?? null); ?>
                    <?php if ($currentPhotoUrl): ?>
                        <img src="<?php echo e($currentPhotoUrl); ?>" alt="Profile photo" class="avatar-sm rounded-circle object-fit-cover">
                    <?php else: ?>
                        <i class="bi bi-person-circle fs-5"></i>
                    <?php endif; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <div class="dropdown-item-text d-flex flex-column align-items-start">
                            <strong class="text-truncate" style="max-width: 220px;"><?php echo e($current_user['full_name']); ?></strong>
                            <span class="badge text-bg-<?php echo ($current_user['role'] === 'super_admin') ? 'danger' : (($current_user['role'] === 'admin') ? 'primary' : 'secondary'); ?>">
                                <?php echo e(role_label($current_user['role'])); ?>
                            </span>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?php echo base_url('settings/profile.php'); ?>"><i class="bi bi-person me-2"></i>My Profile</a></li>
                    <li><a class="dropdown-item" href="<?php echo base_url('settings/change_password.php'); ?>"><i class="bi bi-key me-2"></i>Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?php echo base_url('logout.php'); ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="app-content">
            <div class="container-fluid">
                <?php render_flash(); ?>
