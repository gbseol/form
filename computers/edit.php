<?php
/**
 * =============================================================================
 * Computers - Edit
 * =============================================================================
 * Handles the form display (GET) and the update (POST). Photos can also be
 * managed here: upload new ones, delete or change the primary photo.
 * =============================================================================
 */

$page_title = 'Edit Computer';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/computer_form.php';

// Admins / Super Admins edit the full inventory. Staff may only open this
// page in report mode where they can update the computer's condition (parts,
// CPU, status) and optionally file an issue - never the room or computer ID.
if (!can('edit_computer') && !can('report_issue')) {
    set_flash('danger', 'You do not have permission to perform that action.');
    redirect('dashboard.php');
}
$reportOnly = !can('edit_computer');

if ($reportOnly) {
    $page_title = 'Report an Issue';
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

// Load the existing record.
$stmt = db()->prepare("SELECT * FROM computers WHERE id = ?");
$stmt->execute([$id]);
$existing = $stmt->fetch();

if (!$existing) {
    set_flash('danger', 'Computer not found.');
    redirect('computers/index.php');
}

$labs = db()->query("SELECT id, name FROM labs ORDER BY name")->fetchAll();

$v = $existing;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if ($reportOnly) {
        // Staff may update the computer's condition fields (parts, CPU and
        // working status) and optionally file an issue. The room and the
        // Computer ID can never be changed from this page.
        $statusOptions = computer_statuses();
        $parts = ['monitor_condition', 'keyboard_condition', 'mouse_condition', 'cpu_condition'];

        $update = [];
        foreach ($parts as $field) {
            $val = (string)($_POST[$field] ?? '');
            $update[$field] = in_array($val, $statusOptions, true)
                ? $val
                : ($existing[$field] ?: 'Working');
        }
        $status = (string)($_POST['status'] ?? '');
        $update['status'] = in_array($status, $statusOptions, true)
            ? $status
            : ($existing['status'] ?: 'Working');

        try {
            $stmt = db()->prepare(
                "UPDATE computers
                    SET monitor_condition = ?, keyboard_condition = ?, mouse_condition = ?,
                        cpu_condition = ?, status = ?, updated_by = ?
                  WHERE id = ?"
            );
            $stmt->execute([
                $update['monitor_condition'],
                $update['keyboard_condition'],
                $update['mouse_condition'],
                $update['cpu_condition'],
                $update['status'],
                (int)current_user()['id'],
                $id,
            ]);

            log_activity('Updated computer ' . $existing['computer_id'] . ' (condition/status)',
                'computers', $id, $existing, $update);

            // The problem description is optional - an issue is created when
            // the staff member wrote something, or automatically when the
            // computer's status is not "Working" (so reporting a problem always
            // creates an issue the admins can see and fix).
            $issueDescription = trim((string)($_POST['issue_description'] ?? ''));
            $autoIssue        = $reportOnly && $update['status'] !== 'Working';
            if ($issueDescription !== '' || $autoIssue) {
                $category = in_array($_POST['issue_category'] ?? '', issue_categories(), true)
                    ? $_POST['issue_category']
                    : 'Other';
                if ($issueDescription === '') {
                    $issueDescription = 'Computer status set to "' . $update['status'] . '".';
                    $category         = 'Other';
                }
                $issueId = create_issue($id, $category, $issueDescription, (int)current_user()['id']);
                log_activity('Reported issue #' . $issueId . ' for computer ' . $existing['computer_id'],
                    'issues', $issueId, null, ['category' => $category, 'description' => $issueDescription]);
                set_flash('success', 'Issue #' . $issueId . ' has been reported for this computer. An administrator will fix it.');
            } else {
                set_flash('success', 'Computer "' . $existing['computer_id'] . '" condition was updated.');
            }

            redirect('computers/view.php?id=' . $id);
        } catch (PDOException $ex) {
            error_log('Update computer (report mode) failed: ' . $ex->getMessage());
            set_flash('danger', 'Could not save the changes. Please try again.');
        }
    } else {
        $data   = computer_post_data();
        $errors = validate_computer_post($data, $id);

        if (count($errors) > 0) {
            $v = $data;
            foreach ($errors as $err) {
                set_flash('danger', $err);
            }
        } else {
            // Build a dynamic UPDATE using the safe whitelist from computer_post_data().
            $columns = array_keys($data);
            $sets    = implode(', ', array_map(fn($c) => "$c = ?", $columns));
            $sql = "UPDATE computers SET $sets, updated_by = ? WHERE id = ?";

            $params = array_values($data);
            $params[] = (int)current_user()['id'];
            $params[] = $id;

            try {
                db()->prepare($sql)->execute($params);

                $photosSaved = save_computer_photos($id, $data['computer_id']);

                log_activity('Updated computer ' . $data['computer_id'] . ($photosSaved > 0 ? " (+$photosSaved photo(s))" : ''),
                    'computers', $id, $existing, $data);

                // Create an issue when the user described a problem.
                $issueDescription = trim((string)($_POST['issue_description'] ?? ''));
                if ($issueDescription !== '') {
                    $category = in_array($_POST['issue_category'] ?? '', issue_categories(), true)
                        ? $_POST['issue_category']
                        : 'Other';
                    $issueId = create_issue($id, $category, $issueDescription, (int)current_user()['id']);
                    log_activity('Reported issue #' . $issueId . ' for computer ' . $data['computer_id'],
                        'issues', $issueId, null, ['category' => $category, 'description' => $issueDescription]);
                    set_flash('success', 'Issue #' . $issueId . ' has been reported for this computer. An administrator will fix it.');
                } else {
                    set_flash('success', 'Computer "' . $data['computer_id'] . '" was updated successfully' . ($photosSaved > 0 ? " with $photosSaved new photo(s)" : '') . '.');
                }

                redirect('computers/view.php?id=' . $id);
            } catch (PDOException $ex) {
                error_log('Update computer failed: ' . $ex->getMessage());
                set_flash('danger', 'Could not save the changes. Please try again.');
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i><?php echo $reportOnly ? 'Report an Issue' : 'Edit Computer'; ?>: <?php echo e($existing['computer_id']); ?></h4>
    <a href="<?php echo base_url('computers/view.php?id=' . $id); ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to details
    </a>
</div>

<?php $isEdit = !$reportOnly; ?>
<?php include __DIR__ . '/_form.php'; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
