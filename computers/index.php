<?php
/**
 * =============================================================================
 * Computers - List / Search / Filter
 * =============================================================================
 * Shows the computer inventory with search, filters and pagination.
 * Supports quick AJAX status updates from the table.
 * =============================================================================
 */

$page_title = 'Computers';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/computer_form.php'; // for enum helpers

require_can('view_computer');

$perPage = 15;

// -----------------------------------------------------------------------------
// Read filter parameters from the query string (GET).
// -----------------------------------------------------------------------------
$fLab           = isset($_GET['lab']) ? (int)$_GET['lab'] : 0;
$fStatus        = trim($_GET['status'] ?? '');
$fRam           = trim($_GET['ram'] ?? '');
$fCpu           = trim($_GET['cpu'] ?? '');
$fStorage       = trim($_GET['storage'] ?? '');
$page           = max(1, (int)($_GET['page'] ?? 1));

// -----------------------------------------------------------------------------
// Build the WHERE clause with prepared-statement parameters.
// -----------------------------------------------------------------------------
$where  = [];
$params = [];

// Filters.
if ($fLab > 0)            { $where[] = 'c.lab_id = ?';    $params[] = $fLab; }
if ($fStatus !== '')      { $where[] = 'c.status = ?';    $params[] = $fStatus; }
if ($fRam !== '')         { $where[] = 'c.ram LIKE ?';    $params[] = '%' . $fRam . '%'; }
if ($fCpu !== '')         { $where[] = 'c.cpu LIKE ?';    $params[] = '%' . $fCpu . '%'; }
if ($fStorage !== '')     { $where[] = 'c.storage_capacity LIKE ?'; $params[] = '%' . $fStorage . '%'; }

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$fromSql  = "FROM computers c LEFT JOIN labs l ON l.id = c.lab_id $whereSql";

// -----------------------------------------------------------------------------
// Count the total rows for pagination.
// -----------------------------------------------------------------------------
$stmt = db()->prepare("SELECT COUNT(*) $fromSql");
$stmt->execute($params);
$totalRows = (int)$stmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));
$offset = ($page - 1) * $perPage;

// -----------------------------------------------------------------------------
// Fetch the current page of computers.
// -----------------------------------------------------------------------------
$stmt = db()->prepare(
    "SELECT c.*, l.name AS lab_name
     $fromSql
     ORDER BY (l.name IS NULL), l.name ASC, c.computer_id ASC
     LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$computers = $stmt->fetchAll();

// -----------------------------------------------------------------------------
// Filter dropdown options.
// -----------------------------------------------------------------------------
$labs      = db()->query("SELECT id, name FROM labs ORDER BY name")->fetchAll();
$ramOptions   = distinct_values('ram');
$cpuOptions   = distinct_values('cpu');
$storageOpts  = distinct_values('storage_capacity');

// Preserve the query string when building pagination links.
$queryArgs = $_GET;
unset($queryArgs['page']);
$baseQuery = http_build_query($queryArgs);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Lab</label>
                <select class="form-select" name="lab">
                    <option value="">All Labs</option>
                    <?php foreach ($labs as $lab): ?>
                        <option value="<?php echo (int)$lab['id']; ?>" <?php echo $fLab === (int)$lab['id'] ? 'selected' : ''; ?>><?php echo e($lab['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <?php foreach (computer_statuses() as $s): ?>
                        <option <?php echo $fStatus === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">RAM</label>
                <select class="form-select" name="ram">
                    <option value="">All RAM</option>
                    <?php foreach ($ramOptions as $r): ?>
                        <option <?php echo $fRam === $r ? 'selected' : ''; ?>><?php echo e($r); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">CPU</label>
                <select class="form-select" name="cpu">
                    <option value="">All CPU</option>
                    <?php foreach ($cpuOptions as $c): ?>
                        <option <?php echo $fCpu === $c ? 'selected' : ''; ?>><?php echo e($c); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Storage</label>
                <select class="form-select" name="storage">
                    <option value="">All Storage</option>
                    <?php foreach ($storageOpts as $s): ?>
                        <option <?php echo $fStorage === $s ? 'selected' : ''; ?>><?php echo e($s); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Filter</button>
                <a href="<?php echo base_url('computers/index.php'); ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-pc-display me-1"></i> Computer Inventory
            <span class="text-muted">(<?php echo $totalRows; ?> records)</span>
        </span>
        <div class="btn-group">
            <?php if (can('add_computer')): ?>
                <a href="<?php echo base_url('computers/add.php'); ?>" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Computer</a>
            <?php endif; ?>
            <?php if (can('export_reports')): ?>
                <a href="<?php echo base_url('reports/inventory.php'); ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-bar-graph me-1"></i>Inventory Report</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle mb-0 table-stack">
            <thead>
                <tr>
                    <th>Computer ID</th>
                    <th>Lab</th>
                    <th>CPU</th>
                    <th>RAM</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($computers) === 0): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No computers found<?php echo ($fLab || $fStatus || $fRam !== '' || $fCpu !== '' || $fStorage !== '') ? ' matching your criteria' : ' yet'; ?>.
                            <?php if (can('add_computer')): ?><a href="<?php echo base_url('computers/add.php'); ?>">Add your first computer</a>.<?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($computers as $row): ?>
                <tr>
                    <td data-label="Computer" class="fw-semibold">
                        <a href="<?php echo base_url('computers/view.php?id=' . (int)$row['id']); ?>"><?php echo e($row['computer_id']); ?></a>
                    </td>
                    <td data-label="Lab"><?php echo e($row['lab_name'] ?: '—'); ?></td>
                    <td data-label="CPU" class="small"><?php echo e($row['cpu'] ?: '—'); ?></td>
                    <td data-label="RAM" class="small"><?php echo e($row['ram'] ?: '—'); ?></td>
                    <td data-label="Status">
                        <?php echo status_badge($row['status']); ?>
                        <?php if (can('update_status')): ?>
                            <select class="form-select form-select-sm mt-1 status-quick" data-id="<?php echo (int)$row['id']; ?>" data-url="<?php echo base_url('computers/status_update.php'); ?>">
                                <?php foreach (computer_statuses() as $s): ?>
                                    <option <?php echo $row['status'] === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </td>
                    <td class="text-end text-nowrap stack-actions">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="<?php echo base_url('computers/view.php?id=' . (int)$row['id']); ?>" class="btn btn-outline-secondary" title="View details"><i class="bi bi-eye"></i></a>
                            <?php if (can('edit_computer')): ?>
                                <a href="<?php echo base_url('computers/edit.php?id=' . (int)$row['id']); ?>" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <?php endif; ?>
                            <?php if (can('add_computer')): ?>
                            <form method="post" action="<?php echo base_url('computers/duplicate.php'); ?>" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                <button class="btn btn-outline-secondary" title="Duplicate" type="submit"><i class="bi bi-files"></i></button>
                            </form>
                            <?php endif; ?>
                            <?php if (can('delete_computer')): ?>
                            <form method="post" action="<?php echo base_url('computers/delete.php'); ?>" class="d-inline" data-confirm="Delete computer <?php echo e($row['computer_id']); ?> and all its photos? This cannot be undone.">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                <button class="btn btn-outline-danger" title="Delete" type="submit"><i class="bi bi-trash"></i></button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="card-footer">
        <nav>
            <ul class="pagination pagination-sm justify-content-center mb-0">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?<?php echo $baseQuery; ?>&page=<?php echo max(1, $page - 1); ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?php echo $baseQuery; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?<?php echo $baseQuery; ?>&page=<?php echo min($totalPages, $page + 1); ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
