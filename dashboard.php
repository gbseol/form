<?php
/**
 * =============================================================================
 * Dashboard
 * =============================================================================
 * Overview page with summary cards, charts (loaded via AJAX from
 * dashboard_ajax.php) and a recent activity list.
 * =============================================================================
 */

$page_title = 'Dashboard';

require_once __DIR__ . '/includes/header.php';

// -----------------------------------------------------------------------------
// Summary counters
// -----------------------------------------------------------------------------
$isStaff = has_role('staff');

$counts = [
    'total'        => (int)db()->query("SELECT COUNT(*) FROM computers")->fetchColumn(),
    'working'      => (int)db()->query("SELECT COUNT(*) FROM computers WHERE status = 'Working'")->fetchColumn(),
    'not_working'  => (int)db()->query("SELECT COUNT(*) FROM computers WHERE status = 'Not Working'")->fetchColumn(),
    'some_issues'  => (int)db()->query("SELECT COUNT(*) FROM computers WHERE status = 'Has Some Issues'")->fetchColumn(),
    'labs'         => (int)db()->query("SELECT COUNT(*) FROM labs WHERE status = 'active'")->fetchColumn(),
    'users'        => (int)db()->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn(),
];

// Staff-specific issue counters (issues the staff member reported).
$counts['my_open_issues']   = 0;
$counts['my_solved_issues'] = 0;
if ($isStaff) {
    $uid = (int)$current_user['id'];
    $stmt = db()->prepare("SELECT COUNT(*) FROM issues WHERE reported_by = ? AND status IN ('open','in_progress')");
    $stmt->execute([$uid]);
    $counts['my_open_issues'] = (int)$stmt->fetchColumn();

    $stmt = db()->prepare("SELECT COUNT(*) FROM issues WHERE reported_by = ? AND status = 'resolved'");
    $stmt->execute([$uid]);
    $counts['my_solved_issues'] = (int)$stmt->fetchColumn();
}

// Overall issue counters (shown to admins / super admins).
$counts['open_issues']   = (int)db()->query("SELECT COUNT(*) FROM issues WHERE status IN ('open','in_progress')")->fetchColumn();
$counts['solved_issues'] = (int)db()->query("SELECT COUNT(*) FROM issues WHERE status = 'resolved'")->fetchColumn();

// Recent activities (last 8 entries). Only shown to admins / super admins;
// staff members do not see the activity feed.
$showRecentActivities = !has_role('staff');
$recentActivities     = [];
if ($showRecentActivities) {
    $recentStmt = db()->query(
        "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 8"
    );
    $recentActivities = $recentStmt->fetchAll();
}
?>

<div class="row g-3 mb-4">
    <?php if (!$isStaff): ?>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card bg-card-blue">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-pc-display stat-icon"></i>
                <div>
                    <div class="stat-value"><?php echo $counts['total']; ?></div>
                    <div class="stat-label">Total Computers</div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php if (!$isStaff): ?>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card bg-card-green">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-check-circle stat-icon"></i>
                <div>
                    <div class="stat-value"><?php echo $counts['working']; ?></div>
                    <div class="stat-label">Working</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card bg-card-red">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-x-circle stat-icon"></i>
                <div>
                    <div class="stat-value"><?php echo $counts['not_working']; ?></div>
                    <div class="stat-label">Not Working</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card bg-card-amber">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-tools stat-icon"></i>
                <div>
                    <div class="stat-value"><?php echo $counts['some_issues']; ?></div>
                    <div class="stat-label">Has Some Issues</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card bg-card-indigo">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-building stat-icon"></i>
                <div>
                    <div class="stat-value"><?php echo $counts['labs']; ?></div>
                    <div class="stat-label">Labs</div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php if ($isStaff): ?>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card bg-card-purple h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-exclamation-circle stat-icon"></i>
                <div>
                    <div class="stat-value"><?php echo $counts['my_open_issues']; ?></div>
                    <div class="stat-label text-nowrap">Open Issues</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card bg-card-green h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-check2-circle stat-icon"></i>
                <div>
                    <div class="stat-value"><?php echo $counts['my_solved_issues']; ?></div>
                    <div class="stat-label text-nowrap">Solved Issues</div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card bg-card-purple h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-exclamation-circle stat-icon"></i>
                <div>
                    <div class="stat-value"><?php echo $counts['open_issues']; ?></div>
                    <div class="stat-label text-nowrap">Open Issues</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card bg-card-green h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-check2-circle stat-icon"></i>
                <div>
                    <div class="stat-value"><?php echo $counts['solved_issues']; ?></div>
                    <div class="stat-label text-nowrap">Solved Issues</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card bg-card-purple">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-people stat-icon"></i>
                <div>
                    <div class="stat-value"><?php echo $counts['users']; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row g-3" id="dashboardCharts">
    <?php if (!$isStaff): ?>
    <!-- Computers per Lab -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">Computers per Lab</div>
            <div class="card-body">
                <canvas id="chartLabs" height="110"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!$isStaff): ?>
    <!-- Computers by Status -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">Computers by Status</div>
            <div class="card-body">
                <canvas id="chartStatus" height="110"></canvas>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Issues (Open vs Solved) - staff only -->
    <div class="col-lg-6 mx-auto">
        <div class="card h-100">
            <div class="card-header">Issues</div>
            <div class="card-body">
                <canvas id="chartIssues" height="110"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!$isStaff): ?>
    <!-- Monthly Updates -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">Computers Added (Last 12 Months)</div>
            <div class="card-body">
                <canvas id="chartMonthly" height="100"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($showRecentActivities): ?>
    <!-- Recent Activities -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                Recent Activities
                <?php if (can('view_logs')): ?>
                    <a href="<?php echo base_url('reports/activity.php'); ?>" class="btn btn-sm btn-outline-primary float-end">View All</a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (count($recentActivities) === 0): ?>
                        <li class="list-group-item text-muted">No activity recorded yet.</li>
                    <?php else: ?>
                        <?php foreach ($recentActivities as $act): ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <span><i class="bi bi-dot"></i><?php echo e($act['action']); ?></span>
                                </div>
                                <small class="text-muted">
                                    <?php echo e($act['username']); ?> &middot; <?php echo e(time_ago($act['created_at'])); ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if (can('report_issue')): ?>
<!-- ==================== REPORT AN ISSUE (mobile friendly) ==================== -->
<div class="card mt-4" id="issueReportWidget">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="bi bi-bug me-1"></i> Report an Issue
            <span class="text-muted small">Found a problem with a computer? Let the admins know.</span>
        </span>
        <button class="btn btn-sm btn-outline-primary" id="issuePickerToggle" type="button">
            <i class="bi bi-plus-lg me-1"></i><span id="issuePickerToggleText">Select Computer</span>
        </button>
    </div>
    <div class="card-body d-none" id="issuePicker">
        <p class="text-muted small mb-3">
            Choose a lab, then a computer. You will be taken to that computer's edit page
            where you can change its condition and describe the issue. Saving it creates a
            report that the admins can see and fix.
        </p>
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label" for="issueLabSelect">Lab</label>
                <select class="form-select" id="issueLabSelect">
                    <option value="">-- Select Lab --</option>
                </select>
            </div>
            <div class="col-12 col-md-5">
                <label class="form-label" for="issuePcSelect">Computer</label>
                <select class="form-select" id="issuePcSelect" disabled>
                    <option value="">Select a lab first</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button type="button" class="btn btn-primary w-100" id="issueGoBtn" disabled>
                    <i class="bi bi-arrow-right me-1"></i>Continue
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
