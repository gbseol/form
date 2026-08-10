<?php
/**
 * =============================================================================
 * Settings - Restore Database
 * =============================================================================
 * Lets the Super Admin restore a previously downloaded backup (.sql file).
 *
 * The file is executed statement by statement. Only restore backups generated
 * by this application (or standard mysqldump style CREATE/INSERT dumps) -
 * stored procedures and triggers are not supported.
 *
 * WARNING: restoring overwrites the current database content.
 * =============================================================================
 */

$page_title = 'Restore Database';

require_once __DIR__ . '/../includes/auth.php';

require_can('backup_restore');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $confirmed = isset($_POST['confirm_restore']);

    if (!$confirmed) {
        set_flash('danger', 'You must tick the confirmation checkbox to restore the database.');
        redirect('settings/restore.php');
    }

    // Validate the uploaded file.
    if (empty($_FILES['sql_file']['name']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
        set_flash('danger', 'Please choose a valid .sql backup file.');
        redirect('settings/restore.php');
    }

    if ($_FILES['sql_file']['size'] > MAX_UPLOAD_SIZE * 4) {
        set_flash('danger', 'The backup file is too large.');
        redirect('settings/restore.php');
    }

    $sql = file_get_contents($_FILES['sql_file']['tmp_name']);
    if ($sql === false || trim($sql) === '') {
        set_flash('danger', 'The uploaded file is empty or could not be read.');
        redirect('settings/restore.php');
    }

    // Split the dump into individual statements on semicolons at end of line.
    // NOTE: no transaction is used because MySQL implicitly commits before and
    // after every DDL (CREATE/DROP) statement anyway.
    $statements = preg_split('/;\s*[\r\n]+/', $sql);

    $pdo   = db();
    $count = 0;
    $error = null;

    try {
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }
            $pdo->exec($statement);
            $count++;
        }
    } catch (PDOException $ex) {
        $error = $ex->getMessage();
        error_log('Restore failed: ' . $error);
    }

    if ($error !== null) {
        set_flash('danger', 'The restore stopped on statement ' . ($count + 1) . ': ' . $error . '. The database may be partially restored; re-run the restore or import database.sql to start fresh.');
    } else {
        log_activity('Restored database from backup (' . $count . ' statements)', 'settings', null, null, null);
        set_flash('success', 'Database restored successfully (' . $count . ' statements executed). Please log in again.');
    }
    redirect('settings/restore.php');
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-upload me-2"></i>Restore Database</h4>
    <a href="<?php echo base_url('settings/index.php'); ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to settings</a>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-body">
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <strong>Warning:</strong> Restoring will overwrite the current database content with the content of the backup file.
            Make sure you have a fresh backup before you continue.
        </div>

        <form method="post" action="" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>

            <div class="mb-3">
                <label class="form-label">Backup File (.sql) <span class="text-danger">*</span></label>
                <input type="file" class="form-control" name="sql_file" accept=".sql,text/plain" required>
                <div class="form-text">Upload a backup created with the "Download Database Backup" button.</div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="confirm_restore" id="confirmRestore" required>
                <label class="form-check-label" for="confirmRestore">
                    I understand this will replace the current database.
                </label>
            </div>

            <button type="submit" class="btn btn-warning"><i class="bi bi-upload me-1"></i>Restore Database</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
