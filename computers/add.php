<?php
/**
 * =============================================================================
 * Computers - Add
 * =============================================================================
 * Handles both the simplified add form display (GET) and the insert (POST)
 * with validation, photo upload and activity logging.
 * =============================================================================
 */

$page_title = 'Add Computer';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/computer_form.php';

require_can('add_computer');

// Load labs for the room dropdown.
$labs = db()->query("SELECT id, name FROM labs ORDER BY name")->fetchAll();

// Default (empty) form values.
$v      = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $data   = computer_post_data();
    $errors = validate_computer_post($data);

    // A description is required whenever the computer is not fully working.
    if ($data['status'] !== 'Working' && trim((string)$data['remarks']) === '') {
        $errors[] = 'Please describe the issue when the status is not "Working".';
    }

    if (count($errors) > 0) {
        // Redisplay the form with the submitted values so nothing is lost.
        $v = $data;
        foreach ($errors as $err) {
            set_flash('danger', $err);
        }
    } else {
        // Build the INSERT with an explicit column list.
        $columns = array_keys($data);
        $marks   = implode(',', array_fill(0, count($columns), '?'));
        $sql = "INSERT INTO computers (" . implode(', ', $columns) . ", created_by, updated_by)
                VALUES ($marks, ?, ?)";
        $stmt = db()->prepare($sql);

        $params = array_values($data);
        $params[] = (int)current_user()['id'];
        $params[] = (int)current_user()['id'];

        try {
            $stmt->execute($params);
            $computerId   = (int)db()->lastInsertId();
            $photosSaved  = save_computer_photos($computerId, $data['computer_id']);

            log_activity('Added computer ' . $data['computer_id'] . ($photosSaved > 0 ? " (+$photosSaved photo(s))" : ''),
                'computers', $computerId, null, $data);

            set_flash('success', 'Computer "' . $data['computer_id'] . '" was added successfully' . ($photosSaved > 0 ? " with $photosSaved photo(s)" : '') . '.');
            redirect('computers/view.php?id=' . $computerId);
        } catch (PDOException $ex) {
            error_log('Add computer failed: ' . $ex->getMessage());
            set_flash('danger', 'Could not save the computer. Please try again.');
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add Computer</h4>
    <a href="<?php echo base_url('computers/index.php'); ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to list
    </a>
</div>

<?php $isEdit = false; ?>
<?php include __DIR__ . '/_form.php'; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
