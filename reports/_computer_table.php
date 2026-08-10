<?php
/**
 * =============================================================================
 * Reports - Shared Computer Report Table
 * =============================================================================
 * Renders a readable summary table for computer reports plus the print and
 * export action buttons. Requires $rows (from computer_report()).
 * =============================================================================
 */

$reportTitle = $reportTitle ?? 'Report';
$type        = $type ?? 'inventory';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="bi bi-table me-1"></i><?php echo e($reportTitle); ?>
            <span class="text-muted">(<?php echo count($rows); ?> records)</span></span>
        <div class="btn-group btn-group-sm">
            <a href="<?php echo base_url('reports/export_csv.php?type=' . $type . (isset($extraQuery) ? $extraQuery : '')); ?>" class="btn btn-outline-success"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
            <a href="<?php echo base_url('reports/export_excel.php?type=' . $type . (isset($extraQuery) ? $extraQuery : '')); ?>" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
            <a href="<?php echo base_url('reports/print.php?type=' . $type . (isset($extraQuery) ? $extraQuery : '')); ?>" target="_blank" class="btn btn-outline-info"><i class="bi bi-printer me-1"></i>Print / PDF</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>Computer ID</th>
                    <th>Lab</th>
                    <th>CPU</th>
                    <th>RAM</th>
                    <th>Storage Capacity</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($rows) === 0): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No records found.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td class="fw-semibold"><?php echo e($r['Computer ID']); ?></td>
                    <td><?php echo e($r['Lab'] ?: '—'); ?></td>
                    <td class="small"><?php echo e($r['CPU'] ?: '—'); ?></td>
                    <td class="small"><?php echo e($r['RAM'] ?: '—'); ?></td>
                    <td class="small"><?php echo e($r['Storage Capacity'] ?: '—'); ?></td>
                    <td><?php echo status_badge($r['Status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
