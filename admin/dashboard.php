<?php
$page_title = 'Admin Dashboard';
require_once '../includes/session-check.php';
checkRole(['Admin']);
require_once '../includes/header.php';

// Fetch stats
$total_users = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$active_users = $conn->query("SELECT COUNT(*) as c FROM users WHERE is_active = 1")->fetch_assoc()['c'];
$failed_logins = 0; // Mock for now
$last_backup = 'Not configured'; // Mock for now

// Fetch branches for the branch selector
$branches_res = $conn->query("SELECT * FROM branches ORDER BY branch_name");
$branches = [];
while ($row = $branches_res->fetch_assoc()) {
    $branches[] = $row;
}

// Handle branch selection for active users list
$selected_branch_id = isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : (count($branches) > 0 ? $branches[0]['branch_id'] : null);

$branch_active_users = [];
if ($selected_branch_id) {
    $stmt = $conn->prepare("SELECT u.*, b.branch_name FROM users u LEFT JOIN branches b ON u.branch_id = b.branch_id WHERE u.branch_id = ? AND u.is_active = 1 ORDER BY u.full_name ASC");
    $stmt->bind_param("i", $selected_branch_id);
    $stmt->execute();
    $branch_active_users = $stmt->get_result();
    $stmt->close();
}

// Fetch recent audit logs
$audit_logs = $conn->query("SELECT al.*, u.full_name FROM audit_logs al LEFT JOIN users u ON al.user_id = u.user_id ORDER BY al.timestamp DESC LIMIT 10");
?>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <h3><?php echo $total_users; ?></h3>
                <p>Total Users</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
            <div class="stat-info">
                <h3><?php echo $active_users; ?></h3>
                <p>Active Users</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-info">
                <h3><?php echo $failed_logins; ?></h3>
                <p>Failed Login Attempts</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-database"></i></div>
            <div class="stat-info">
                <h3 style="font-size:1rem;"><?php echo e($last_backup); ?></h3>
                <p>Last Backup</p>
            </div>
        </div>
    </div>
</div>

<!-- Active Users by Branch -->
<div class="content-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-building me-2"></i>Active Users by Branch</h5>
        <form method="GET" class="d-flex align-items-center" style="max-width: 300px;">
            <select name="branch_id" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                <?php foreach ($branches as $b): ?>
                    <option value="<?php echo $b['branch_id']; ?>" <?php echo $selected_branch_id == $b['branch_id'] ? 'selected' : ''; ?>>
                        <?php echo e($b['branch_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Branch</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$branch_active_users || $branch_active_users->num_rows === 0): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No active users found for this branch.</td></tr>
                    <?php else: ?>
                        <?php while ($u = $branch_active_users->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo e($u['full_name']); ?></strong></td>
                                <td><?php echo e($u['username']); ?></td>
                                        <td><span class="badge bg-primary"><?php echo e($u['role']); ?></span></td>
                                <td><?php echo e($u['branch_name'] ?? 'N/A'); ?></td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-history me-2"></i>Recent Activity</h5>
        <a href="<?php echo BASE_URL; ?>/admin/audit-trail.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($audit_logs->num_rows === 0): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No activity recorded yet.</td></tr>
                    <?php else: ?>
                        <?php while ($log = $audit_logs->fetch_assoc()): ?>
                            <tr>
                                <td><small><?php echo formatDateTime($log['timestamp']); ?></small></td>
                                <td><?php echo e($log['full_name'] ?? 'System'); ?></td>
                                <td><span class="badge bg-secondary"><?php echo e($log['action_type']); ?></span></td>
                                <td><?php echo e($log['entity_type']); ?></td>
                                <td><small><?php echo e($log['details']); ?></small></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
