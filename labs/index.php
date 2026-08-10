<?php
/**
 * =============================================================================
 * Labs - List
 * =============================================================================
 * Shows all labs with their computer counts and links to view lab reports.
 * =============================================================================
 */

$page_title = 'Labs';

require_once __DIR__ . '/../includes/auth.php';

require_can('manage_labs');

$stmt = db()->query(
    "SELECT l.*,
            (SELECT COUNT(*) FROM computers c WHERE c.lab_id = l.id) AS total_computers,
            (SELECT COUNT(*) FROM computers c WHERE c.lab_id = l.id AND c.status = 'Working') AS working_computers
       FROM labs l
      ORDER BY l.name"
);
$labs = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0"><i class="bi bi-building me-2"></i>Lab Management</h4>
    <a href="<?php echo base_url('labs/add.php'); ?>" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Lab</a>
</div>

<div class="row g-3">
    <?php if (count($labs) === 0): ?>
        <div class="col-12">
            <div class="card"><div class="card-body text-center text-muted py-4">No labs yet. <a href="<?php echo base_url('labs/add.php'); ?>">Add your first lab</a>.</div></div>
        </div>
    <?php endif; ?>

    <?php foreach ($labs as $lab): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="mb-0"><i class="bi bi-diagram-3 me-1 text-primary"></i><?php echo e($lab['name']); ?></h5>
                        <span class="badge bg-<?php echo $lab['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo e(ucfirst($lab['status'])); ?></span>
                    </div>
                    <?php if ($lab['location']): ?>
                        <p class="mb-1"><i class="bi bi-geo-alt me-1 text-muted"></i><?php echo e($lab['location']); ?></p>
                    <?php endif; ?>
                    <?php if ($lab['description']): ?>
                        <p class="text-muted text-truncate-2 mb-2"><?php echo e($lab['description']); ?></p>
                    <?php endif; ?>

                    <div class="d-flex gap-3 mb-3 small">
                        <span><strong><?php echo (int)$lab['total_computers']; ?></strong> total</span>
                        <span><strong class="text-success"><?php echo (int)$lab['working_computers']; ?></strong> working</span>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?php echo base_url('reports/lab.php?lab_id=' . (int)$lab['id']); ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-bar-chart me-1"></i>Report</a>
                        <a href="<?php echo base_url('labs/edit.php?id=' . (int)$lab['id']); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Edit</a>
                        <?php if (can('delete_labs')): ?>
                        <form method="post" action="<?php echo base_url('labs/delete.php'); ?>" class="d-inline" data-confirm="Delete lab <?php echo e($lab['name']); ?>? Computers in this lab will not be deleted but will become unassigned.">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo (int)$lab['id']; ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash me-1"></i>Delete</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
