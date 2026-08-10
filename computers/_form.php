<?php
/**
 * =============================================================================
 * Computers - Add / Edit Form (shared partial, simplified)
 * =============================================================================
 * A simple form for adding or editing a computer: choose a room, enter the
 * Computer ID, pick the condition of the main parts (monitor / keyboard /
 * mouse), optionally add CPU details and set a working status with a
 * description of any issue.
 *
 * Requires:
 *   $v      - array of current values (or empty defaults)
 *   $labs   - list of labs for the dropdown
 *   $isEdit - boolean (optional, defaults to false)
 * Posts back to the including page with multipart/form-data.
 * =============================================================================
 */

$isEdit = $isEdit ?? false;
$reportOnly = $reportOnly ?? false;

// Helper to read a value with a default.
$fv = function ($key) use ($v) {
    return e($v[$key] ?? '');
};

// Helper to render a field value as read-only text (staff report mode).
$fvText = function ($key) use ($v) {
    $val = (string)($v[$key] ?? '');
    return $val === '' ? '<span class="text-muted">&mdash;</span>' : e($val);
};

// Current lab name for the read-only display (staff report mode).
$labName = '';
foreach ($labs as $lab) {
    if ((int)$lab['id'] === (int)($v['lab_id'] ?? 0)) {
        $labName = $lab['name'];
        break;
    }
}

// Datalist suggestions pulled from existing computer records.
$datalists = [
    'dl_cpu'        => distinct_values('cpu'),
    'dl_ram'        => distinct_values('ram'),
    'dl_storagecap' => distinct_values('storage_capacity'),
];

// Helper that renders the options of a condition select, keeping any legacy
// stored value that is not part of the standard status list.
$statusOptions = computer_statuses();
$statusSelect  = function ($field, $default) use ($v, $statusOptions) {
    $cur     = (string)($v[$field] ?? $default);
    $options = '';
    if (!in_array($cur, $statusOptions, true)) {
        $options .= '<option selected>' . e($cur) . '</option>';
    }
    foreach ($statusOptions as $opt) {
        $options .= '<option' . ($cur === $opt ? ' selected' : '') . '>' . e($opt) . '</option>';
    }
    return $options;
};
?>
<?php foreach ($datalists as $id => $values): ?>
    <?php if (count($values) > 0): ?>
    <datalist id="<?php echo $id; ?>">
        <?php foreach ($values as $val): ?>
            <option value="<?php echo e($val); ?>"></option>
        <?php endforeach; ?>
    </datalist>
    <?php endif; ?>
<?php endforeach; ?>

<form method="post" action="" enctype="multipart/form-data" autocomplete="off" id="computerForm">
    <?php echo csrf_field(); ?>

    <!-- ==================== ROOM & COMPUTER ID ==================== -->
    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-building me-1"></i> Room &amp; Computer ID</div>
        <div class="card-body">
            <?php if ($reportOnly): ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Room / Lab</label>
                        <input type="text" class="form-control" value="<?php echo e($labName ?: '—'); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Computer ID</label>
                        <input type="text" class="form-control" value="<?php echo $fv('computer_id'); ?>" readonly>
                    </div>
                </div>
                <div class="form-text">Only administrators can change the room or the computer ID.</div>
            <?php else: ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Room / Lab <span class="text-danger">*</span></label>
                    <?php if ($isEdit): ?>
                        <select class="form-select" name="lab_id" required>
                    <?php else: ?>
                        <select class="form-select" id="labSelect" name="lab_id" required>
                    <?php endif; ?>
                        <option value="">-- Select Room --</option>
                        <?php foreach ($labs as $lab): ?>
                            <option value="<?php echo (int)$lab['id']; ?>"
                                <?php echo ((int)($v['lab_id'] ?? 0) === (int)$lab['id']) ? 'selected' : ''; ?>>
                                <?php echo e($lab['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Select the room this computer is in.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Computer ID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="computerIdField" name="computer_id"
                           value="<?php echo $fv('computer_id'); ?>" required maxlength="50"
                           <?php echo $isEdit ? '' : 'readonly'; ?>>
                    <?php if ($isEdit): ?>
                        <div class="form-text">A unique code for this computer (e.g. PC001).</div>
                    <?php else: ?>
                        <div class="form-text">Assigned automatically when you pick a room (e.g. PC001, PC002 ...).</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ==================== COMPUTER PARTS ==================== -->
    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-pc-display me-1"></i> Computer Parts</div>
        <div class="card-body">
            <?php if ($reportOnly): ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Monitor <span class="text-danger">*</span></label>
                        <select class="form-select" name="monitor_condition" required><?php echo $statusSelect('monitor_condition', 'Working'); ?></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Keyboard <span class="text-danger">*</span></label>
                        <select class="form-select" name="keyboard_condition" required><?php echo $statusSelect('keyboard_condition', 'Working'); ?></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mouse <span class="text-danger">*</span></label>
                        <select class="form-select" name="mouse_condition" required><?php echo $statusSelect('mouse_condition', 'Working'); ?></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">CPU <span class="text-danger">*</span></label>
                        <select class="form-select" name="cpu_condition" required><?php echo $statusSelect('cpu_condition', 'Working'); ?></select>
                    </div>
                </div>
                <div class="form-text">Update the condition of each part (Working / Not Working / Has Some Issues) so problems are easy to spot.</div>
            <?php else: ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Monitor <span class="text-danger">*</span></label>
                    <select class="form-select" name="monitor_condition" required><?php echo $statusSelect('monitor_condition', 'Working'); ?></select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Keyboard <span class="text-danger">*</span></label>
                    <select class="form-select" name="keyboard_condition" required><?php echo $statusSelect('keyboard_condition', 'Working'); ?></select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mouse <span class="text-danger">*</span></label>
                    <select class="form-select" name="mouse_condition" required><?php echo $statusSelect('mouse_condition', 'Working'); ?></select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">CPU Condition <span class="text-danger">*</span></label>
                    <select class="form-select" name="cpu_condition" required><?php echo $statusSelect('cpu_condition', 'Working'); ?></select>
                </div>
            </div>
            <div class="form-text">Pick the condition of each part (Working / Not Working / Has Some Issues) so problems are easy to spot.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ==================== CPU DETAILS (optional) ==================== -->
    <?php if (!$reportOnly): ?>
    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-cpu me-1"></i> CPU Details <small class="text-muted">(optional)</small></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Processor</label>
                    <input type="text" class="form-control" name="cpu" list="dl_cpu" value="<?php echo $fv('cpu'); ?>" placeholder="e.g. Intel Core i5" maxlength="150">
                </div>
                <div class="col-md-4">
                    <label class="form-label">RAM</label>
                    <input type="text" class="form-control" name="ram" list="dl_ram" value="<?php echo $fv('ram'); ?>" placeholder="e.g. 8 GB" maxlength="50">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Storage</label>
                    <input type="text" class="form-control" name="storage_capacity" list="dl_storagecap" value="<?php echo $fv('storage_capacity'); ?>" placeholder="e.g. 512 GB SSD" maxlength="50">
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ==================== WORKING STATUS ==================== -->
    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-tag me-1"></i> Working Status</div>
        <div class="card-body">
            <?php if ($reportOnly): ?>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="computerStatus" name="status" required><?php echo $statusSelect('status', 'Working'); ?></select>
                        <div class="form-text">Choose the current condition of this computer.</div>
                    </div>
                </div>
            <?php else: ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="computerStatus" name="status" required><?php echo $statusSelect('status', 'Working'); ?></select>
                    <div class="form-text">Choose the current condition of this computer.</div>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Describe the issue</label>
                    <textarea class="form-control" id="computerRemarks" name="remarks" rows="3" maxlength="5000"><?php echo $fv('remarks'); ?></textarea>
                    <div class="form-text">Required when the status is not "Working" (e.g. mouse not working, screen flickers).</div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ==================== REPORT AN ISSUE (edit mode only) ==================== -->
    <?php if ($isEdit || $reportOnly): ?>
    <div class="card mb-3" id="issueReportCard">
        <div class="card-header"><i class="bi bi-bug me-1"></i> Report an Issue <small class="text-muted">(optional)</small></div>
        <div class="card-body">
            <?php if ($reportOnly): ?>
                <p class="text-muted small mb-3">
                    Found a problem with this computer? Update its condition above and
                    optionally describe the problem below. You can also submit without
                    a description - just saving the condition is enough.
                </p>
            <?php else: ?>
                <p class="text-muted small mb-3">
                    Describe any problem with this computer and update its condition above.
                    When you save, an issue is created so the administrators can see and fix it.
                </p>
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Issue Category</label>
                    <select class="form-select" name="issue_category">
                        <option value="">-- Select Category --</option>
                        <?php foreach (issue_categories() as $cat): ?>
                            <option <?php echo (($_POST['issue_category'] ?? '') === $cat) ? 'selected' : ''; ?>><?php echo e($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label">What is the problem? <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control" name="issue_description" rows="3" maxlength="5000"
                              placeholder="e.g. Monitor is not working, the screen stays black after power on."><?php echo e($_POST['issue_description'] ?? ''); ?></textarea>
                    <div class="form-text"><?php echo $reportOnly ? 'Optional - you can submit without writing anything here.' : 'Leave this empty if you are only making a normal update.'; ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ==================== PHOTOS (optional) ==================== -->
    <?php if (!$reportOnly): ?>
    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-images me-1"></i> Photos <small class="text-muted">(optional)</small></div>
        <div class="card-body">
            <input type="file" class="form-control" name="photos[]" accept="image/*" multiple>
            <div class="form-text">JPG, PNG, GIF, WEBP or BMP. Maximum <?php echo round(MAX_UPLOAD_SIZE / 1048576); ?> MB per file. The first uploaded photo becomes the primary photo.</div>
            <?php if ($isEdit): ?>
                <div class="alert alert-info mt-2 mb-0 py-2">
                    <i class="bi bi-info-circle me-1"></i>Use the "Manage Current Photos" section below the form to delete photos or change the primary photo.
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-1"></i><?php echo $reportOnly ? 'Report Issue' : ($isEdit ? 'Save Changes' : 'Add Computer'); ?>
        </button>
        <a href="<?php echo base_url('computers/index.php'); ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<?php if ($isEdit): ?>
<!-- ==================== PHOTO MANAGEMENT (edit mode) ====================
     Placed AFTER the main form because nested <form> elements are invalid
     HTML and would break the browser's form parsing. -->
<?php
$photoStmt = db()->prepare("SELECT * FROM computer_photos WHERE computer_id = ? ORDER BY is_primary DESC, id ASC");
$photoStmt->execute([(int)$v['id']]);
$photos = $photoStmt->fetchAll();
?>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-images me-1"></i> Manage Current Photos</div>
    <div class="card-body">
        <?php if (count($photos) === 0): ?>
            <p class="text-muted mb-0">No photos uploaded yet.</p>
        <?php else: ?>
            <div class="photo-grid">
                <?php foreach ($photos as $photo): ?>
                    <div class="photo-item">
                        <img src="<?php echo uploads_url('computers/' . rawurlencode($photo['filename'])); ?>" class="photo-thumb" alt="Computer photo">
                        <?php if ($photo['is_primary']): ?>
                            <span class="badge text-bg-primary position-absolute bottom-0 start-0 m-1">Primary</span>
                        <?php endif; ?>
                        <div class="photo-actions">
                            <form method="post" action="<?php echo base_url('computers/photo_action.php'); ?>" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="primary">
                                <input type="hidden" name="photo_id" value="<?php echo (int)$photo['id']; ?>">
                                <input type="hidden" name="computer_id" value="<?php echo (int)$v['id']; ?>">
                                <button class="btn btn-sm btn-outline-primary" type="submit" title="Set as primary"><i class="bi bi-star"></i></button>
                            </form>
                            <form method="post" action="<?php echo base_url('computers/photo_action.php'); ?>" class="d-inline" data-confirm="Delete this photo?">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="photo_id" value="<?php echo (int)$photo['id']; ?>">
                                <input type="hidden" name="computer_id" value="<?php echo (int)$v['id']; ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit" title="Delete photo"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
