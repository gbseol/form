<?php
/**
 * =============================================================================
 * Settings - Site Configuration
 * =============================================================================
 * Lets the Super Admin change the site name, upload a logo and pick the theme
 * colour (which drives the accent colour of the whole UI). Super Admin only.
 * =============================================================================
 */

$page_title = 'Settings';

require_once __DIR__ . '/../includes/auth.php';

require_can('manage_settings');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $siteName   = trim($_POST['site_name'] ?? '');
    $themeColor = trim($_POST['theme_color'] ?? '#0d6efd');
    $removeLogo = isset($_POST['remove_logo']);

    if ($siteName === '') {
        $errors[] = 'Site name is required.';
    }
    // Validate the colour is a proper 3/6-digit hex colour.
    if (preg_match('/^#[0-9a-fA-F]{6}$/', $themeColor) !== 1 && preg_match('/^#[0-9a-fA-F]{3}$/', $themeColor) !== 1) {
        $errors[] = 'Please pick a valid theme colour.';
    }

    if (count($errors) === 0) {
        set_setting('site_name', $siteName);
        set_setting('theme_color', strtoupper($themeColor));

        // Handle the logo upload (optional).
        if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logo = save_uploaded_photo($_FILES['logo'], __DIR__ . '/../uploads/logos', (int)current_user()['id']);
            if ($logo === null) {
                $errors[] = 'The logo could not be uploaded. Use a JPG, PNG, GIF, WEBP or BMP file smaller than ' . round(MAX_UPLOAD_SIZE / 1048576) . ' MB.';
            } else {
                // Delete the previous logo file (keep disk clean).
                $oldLogo = get_setting('logo', '');
                if ($oldLogo && $oldLogo !== $logo && is_file(__DIR__ . '/../uploads/logos/' . $oldLogo)) {
                    @unlink(__DIR__ . '/../uploads/logos/' . $oldLogo);
                }
                set_setting('logo', $logo);
            }
        }

        if ($removeLogo) {
            $oldLogo = get_setting('logo', '');
            if ($oldLogo && is_file(__DIR__ . '/../uploads/logos/' . $oldLogo)) {
                @unlink(__DIR__ . '/../uploads/logos/' . $oldLogo);
            }
            set_setting('logo', '');
        }

        if (count($errors) === 0) {
            log_activity('Updated site settings', 'settings', null, null, [
                'site_name' => $siteName,
                'theme_color' => $themeColor,
            ]);
            set_flash('success', 'Settings were saved successfully.');
            redirect('settings/index.php');
        }
    }

    foreach ($errors as $err) {
        set_flash('danger', $err);
    }
}

$currentLogo = get_setting('logo', '');

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-gear me-2"></i>Settings</h4>
</div>

<div class="row g-3">
    <!-- Site settings -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-sliders me-1"></i>Site Configuration</div>
            <div class="card-body">
                <form method="post" action="" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>

                    <div class="mb-3">
                        <label class="form-label">Company / School Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="site_name" value="<?php echo e(get_setting('site_name', 'Computer Lab Management System')); ?>" required maxlength="150">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Logo</label>
                        <?php if ($currentLogo && is_file(__DIR__ . '/../uploads/logos/' . $currentLogo)): ?>
                            <div class="mb-2">
                                <img src="<?php echo uploads_url('logos/' . rawurlencode($currentLogo)); ?>" alt="Current logo" style="height:48px;border-radius:6px;border:1px solid #dee2e6;">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="remove_logo" id="removeLogo">
                                    <label class="form-check-label" for="removeLogo">Remove current logo</label>
                                </div>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="logo" accept="image/*">
                        <div class="form-text">JPG, PNG, GIF, WEBP or BMP, max <?php echo round(MAX_UPLOAD_SIZE / 1048576); ?> MB.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Theme Color <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" class="form-control form-control-color" name="theme_color" value="<?php echo e(get_setting('theme_color', '#0d6efd')); ?>" style="width:60px;height:38px;">
                            <input type="text" class="form-control mono" id="themeHex" value="<?php echo e(get_setting('theme_color', '#0d6efd')); ?>" maxlength="7" style="max-width:140px;">
                            <span class="text-muted small">Accent colour used across the interface.</span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Settings</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick links -->
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-person me-1"></i>Account</div>
            <div class="list-group list-group-flush">
                <a class="list-group-item list-group-item-action" href="<?php echo base_url('settings/profile.php'); ?>">
                    <i class="bi bi-person-circle me-2"></i>My Profile
                </a>
                <a class="list-group-item list-group-item-action" href="<?php echo base_url('settings/change_password.php'); ?>">
                    <i class="bi bi-key me-2"></i>Change Password
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-database me-1"></i>Database</div>
            <div class="card-body">
                <p class="text-muted small">Download a full backup of the database or restore a previous backup.</p>
                <div class="d-grid gap-2">
                    <a href="<?php echo base_url('settings/backup.php'); ?>" class="btn btn-outline-primary"><i class="bi bi-download me-1"></i>Download Database Backup</a>
                    <a href="<?php echo base_url('settings/restore.php'); ?>" class="btn btn-outline-warning"><i class="bi bi-upload me-1"></i>Restore Database</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Keep the colour input and hex text input in sync.
    const colorPicker = document.querySelector('input[name="theme_color"]');
    const hexInput = document.getElementById('themeHex');
    colorPicker.addEventListener('input', function () { hexInput.value = this.value; });
    hexInput.addEventListener('input', function () {
        if (/^#[0-9a-fA-F]{6}$/.test(this.value) || /^#[0-9a-fA-F]{3}$/.test(this.value)) {
            colorPicker.value = this.value;
        }
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
