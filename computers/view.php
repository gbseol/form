<?php
/**
 * =============================================================================
 * Computers - View Details
 * =============================================================================
 * Full detail page for one computer with a photo gallery and action buttons.
 * =============================================================================
 */

$page_title = 'Computer Details';

require_once __DIR__ . '/../includes/auth.php';

require_can('view_computer');

$id = (int)($_GET['id'] ?? 0);

$stmt = db()->prepare(
    "SELECT c.*, l.name AS lab_name,
            creator.full_name AS created_by_name, updater.full_name AS updated_by_name
       FROM computers c
       LEFT JOIN labs l ON l.id = c.lab_id
       LEFT JOIN users creator ON creator.id = c.created_by
       LEFT JOIN users updater ON updater.id = c.updated_by
      WHERE c.id = ?"
);
$stmt->execute([$id]);
$computer = $stmt->fetch();

if (!$computer) {
    set_flash('danger', 'Computer not found.');
    redirect('computers/index.php');
}

// Photos for this computer.
$photoStmt = db()->prepare("SELECT * FROM computer_photos WHERE computer_id = ? ORDER BY is_primary DESC, id ASC");
$photoStmt->execute([$id]);
$photos = $photoStmt->fetchAll();

$primaryPhoto = null;
foreach ($photos as $p) {
    if ($p['is_primary']) { $primaryPhoto = $p; break; }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">
        <i class="bi bi-pc-display me-2"></i><?php echo e($computer['computer_id']); ?>
        <?php echo status_badge($computer['status']); ?>
    </h4>
    <div class="btn-group">
        <?php if (can('edit_computer')): ?>
            <a href="<?php echo base_url('computers/edit.php?id=' . $id); ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
        <?php endif; ?>
        <a href="<?php echo base_url('computers/print.php?id=' . $id); ?>" class="btn btn-outline-info btn-sm" target="_blank"><i class="bi bi-printer me-1"></i>Print</a>
        <?php if (can('add_computer')): ?>
            <form method="post" action="<?php echo base_url('computers/duplicate.php'); ?>" class="d-inline">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <button class="btn btn-outline-secondary btn-sm" type="submit"><i class="bi bi-files me-1"></i>Duplicate</button>
            </form>
        <?php endif; ?>
        <?php if (can('delete_computer')): ?>
            <form method="post" action="<?php echo base_url('computers/delete.php'); ?>" class="d-inline" data-confirm="Delete computer <?php echo e($computer['computer_id']); ?> and all its photos? This cannot be undone.">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <button class="btn btn-outline-danger btn-sm" type="submit"><i class="bi bi-trash me-1"></i>Delete</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($primaryPhoto): ?>
    <div class="text-center mb-3">
        <img src="<?php echo uploads_url('computers/' . rawurlencode($primaryPhoto['filename'])); ?>" class="gallery-img img-fluid border rounded" alt="Primary photo">
    </div>
<?php endif; ?>

<?php if (count($photos) > 1): ?>
    <div class="photo-grid mb-3">
        <?php foreach ($photos as $p): ?>
            <?php if (!$p['is_primary']): ?>
                <img src="<?php echo uploads_url('computers/' . rawurlencode($p['filename'])); ?>" class="photo-thumb" alt="Computer photo">
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
// Helper to render a detail row.
function detail_row($label, $value) {
    $value = ($value === null || $value === '' || $value === 'No') ? '—' : e($value);
    echo '<tr><th>' . e($label) . '</th><td>' . $value . '</td></tr>';
}
?>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-info-circle me-1"></i>Basic Information</div>
            <table class="table table-sm mb-0">
                <tbody>
                    <?php
                    detail_row('Computer ID', $computer['computer_id']);
                    detail_row('Lab', $computer['lab_name']);
                    ?>
                    <tr>
                        <th>Status</th>
                        <td><?php echo status_badge($computer['status']); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-cpu me-1"></i>Hardware</div>
            <table class="table table-sm mb-0">
                <tbody>
                    <?php
                    detail_row('CPU', $computer['cpu']);
                    detail_row('RAM', $computer['ram']);
                    detail_row('Storage Capacity', $computer['storage_capacity']);
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pc-display me-1"></i>Parts</div>
            <table class="table table-sm mb-0">
                <tbody>
                    <?php
                    detail_row('CPU Condition', $computer['cpu_condition']);
                    detail_row('Monitor Condition', $computer['monitor_condition']);
                    detail_row('Keyboard Condition', $computer['keyboard_condition']);
                    detail_row('Mouse Condition', $computer['mouse_condition']);
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-chat-left-text me-1"></i>Remarks</div>
            <div class="card-body">
                <?php echo $computer['remarks'] !== '' ? nl2br(e($computer['remarks'])) : '<span class="text-muted">No remarks.</span>'; ?>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-clock-history me-1"></i>Audit Information</div>
            <table class="table table-sm mb-0">
                <tbody>
                    <tr><th>Created By</th><td><?php echo e($computer['created_by_name'] ?: '—'); ?></td></tr>
                    <tr><th>Created At</th><td><?php echo e($computer['created_at']); ?></td></tr>
                    <tr><th>Last Updated By</th><td><?php echo e($computer['updated_by_name'] ?: '—'); ?></td></tr>
                    <tr><th>Last Updated At</th><td><?php echo e($computer['updated_at']); ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
