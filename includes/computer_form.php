<?php
/**
 * =============================================================================
 * Computer Form Helpers
 * =============================================================================
 * Shared logic for the add/edit computer pages:
 *   - Sanitise and validate the posted computer fields
 *   - Save multiple uploaded photos
 * =============================================================================
 */

if (!defined('IN_APP')) {
    http_response_code(403);
    exit('Direct access is not allowed.');
}

/**
 * Build a safe database-ready array from the POST payload.
 * Every value is trimmed; unknown fields are simply ignored (whitelist style).
 *
 * @return array column => value
 */
function computer_post_data()
{
    $data = [];

    // Text / nullable string fields (the minimal set the app now collects).
    foreach (['computer_id', 'cpu', 'ram', 'storage_capacity', 'remarks'] as $f) {
        $data[$f] = trim((string)($_POST[$f] ?? ''));
    }

    // Part condition fields - only allow the standard status values.
    foreach (['monitor_condition', 'keyboard_condition', 'mouse_condition', 'cpu_condition'] as $f) {
        $data[$f] = in_array($_POST[$f] ?? '', computer_statuses(), true) ? $_POST[$f] : 'Working';
    }

    // Overall status.
    $data['status'] = in_array($_POST['status'] ?? '', computer_statuses(), true) ? $_POST['status'] : 'Working';

    // Lab (room).
    $data['lab_id'] = (isset($_POST['lab_id']) && $_POST['lab_id'] !== '')
        ? (int)$_POST['lab_id']
        : null;

    return $data;
}

/**
 * Validate the posted computer data. Returns a list of error messages.
 *
 * @param array $data  Result of computer_post_data()
 * @param int|null $ignoreId When editing, the id to ignore for the unique check.
 * @return array
 */
function validate_computer_post(array $data, $ignoreId = null)
{
    $errors = [];

    // Computer ID is mandatory.
    if ($data['computer_id'] === '') {
        $errors[] = 'Computer ID is required.';
    } elseif (strlen($data['computer_id']) > 50) {
        $errors[] = 'Computer ID must be 50 characters or fewer.';
    } else {
        // Uniqueness is enforced per lab: the same ID may be reused in a
        // different room (e.g. PC001 in Lab 1 and PC001 in the Library).
        $stmt = db()->prepare(
            "SELECT id FROM computers
              WHERE computer_id = ?
                AND id <> ?
                AND (lab_id = ? OR (lab_id IS NULL AND ? IS NULL))"
        );
        $stmt->execute([$data['computer_id'], (int)$ignoreId, $data['lab_id'], $data['lab_id']]);
        if ($stmt->fetch()) {
            $errors[] = 'This Computer ID already exists in the selected room. Please use a unique value.';
        }
    }

    // Lab must exist when provided.
    if ($data['lab_id'] !== null) {
        $stmt = db()->prepare("SELECT id FROM labs WHERE id = ?");
        $stmt->execute([$data['lab_id']]);
        if (!$stmt->fetch()) {
            $errors[] = 'The selected lab does not exist.';
        }
    }

    return $errors;
}

/**
 * Save the uploaded photos ($_FILES['photos']) for a computer.
 *
 * @param int $computerId
 * @return int number of photos stored
 */
function save_computer_photos($computerId, $computerCode)
{
    if (empty($_FILES['photos']) || !is_array($_FILES['photos']['name'])) {
        return 0;
    }

    $targetDir = __DIR__ . '/../uploads/computers';
    $saved     = 0;
    $total     = count($_FILES['photos']['name']);

    // Rebuild the files array into a convenient list.
    for ($i = 0; $i < $total; $i++) {
        $file = [
            'name'     => $_FILES['photos']['name'][$i],
            'type'     => $_FILES['photos']['type'][$i],
            'tmp_name' => $_FILES['photos']['tmp_name'][$i],
            'error'    => $_FILES['photos']['error'][$i],
            'size'     => $_FILES['photos']['size'][$i],
        ];

        $filename = save_uploaded_photo($file, $targetDir, $computerId);
        if ($filename === null) {
            continue;
        }

        // The first saved photo becomes the primary photo.
        $isPrimary = 0;
        if ($i === 0) {
            $check = db()->prepare("SELECT id FROM computer_photos WHERE computer_id = ?");
            $check->execute([$computerId]);
            if (!$check->fetch()) {
                $isPrimary = 1;
            }
        }

        $stmt = db()->prepare(
            "INSERT INTO computer_photos (computer_id, filename, is_primary) VALUES (?, ?, ?)"
        );
        $stmt->execute([$computerId, $filename, $isPrimary]);
        $saved++;
    }

    return $saved;
}
