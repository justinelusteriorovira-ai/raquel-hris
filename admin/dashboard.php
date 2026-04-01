<?php
$page_title = 'Admin Dashboard';
require_once '../includes/session-check.php';
checkRole(['Admin']);
require_once '../includes/header.php';

// Fetch stats
$total_users = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$active_users = $conn->query("SELECT COUNT(*) as c FROM users WHERE is_active = 1")->fetch_assoc()['c'];
$total_employees = $conn->query("SELECT COUNT(*) as c FROM employees WHERE is_active = 1")->fetch_assoc()['c'];
$pending_evals = $conn->query("SELECT COUNT(*) as c FROM evaluations WHERE status IN ('Pending Supervisor', 'Pending Manager')")->fetch_assoc()['c'];

// --- ANALYTICS DATA FETCHING ---

// 1. Employee Status Distribution
$status_res = $conn->query("SELECT employment_status, COUNT(*) as count FROM employees WHERE is_active = 1 AND deleted_at IS NULL GROUP BY employment_status");
$status_labels = [];
$status_counts = [];
while ($row = $status_res->fetch_assoc()) {
    $status_labels[] = $row['employment_status'];
    $status_counts[] = (int) $row['count'];
}

// 2. Department Distribution
$dept_res = $conn->query("SELECT department, COUNT(*) as count FROM employees WHERE is_active = 1 AND deleted_at IS NULL GROUP BY department ORDER BY count DESC");
$dept_labels = [];
$dept_counts = [];
while ($row = $dept_res->fetch_assoc()) {
    $dept_labels[] = $row['department'];
    $dept_counts[] = (int) $row['count'];
}

// 3. Gender Distribution
$gender_res = $conn->query("SELECT gender, COUNT(*) as count FROM employees WHERE is_active = 1 AND deleted_at IS NULL GROUP BY gender");
$gender_labels = [];
$gender_counts = [];
while ($row = $gender_res->fetch_assoc()) {
    $gender_labels[] = $row['gender'] ?? 'Unknown';
    $gender_counts[] = (int) $row['count'];
}

// 4. Hiring Trends (Last 12 Months)
$hiring_res = $conn->query("SELECT DATE_FORMAT(hire_date, '%b %Y') as month_label, COUNT(*) as count 
                            FROM employees 
                            WHERE hire_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH) 
                            GROUP BY DATE_FORMAT(hire_date, '%Y-%m') 
                            ORDER BY DATE_FORMAT(hire_date, '%Y-%m') ASC");
$hiring_labels = [];
$hiring_counts = [];
while ($row = $hiring_res->fetch_assoc()) {
    $hiring_labels[] = $row['month_label'];
    $hiring_counts[] = (int) $row['count'];
}

// 5. Departmental Performance (Avg Score)
$perf_res = $conn->query("SELECT e.department, AVG(ev.total_score) as avg_score 
                          FROM evaluations ev 
                          JOIN employees e ON ev.employee_id = e.employee_id 
                          WHERE ev.status = 'Approved' 
                          GROUP BY e.department 
                          ORDER BY avg_score DESC");
$perf_labels = [];
$perf_scores = [];
while ($row = $perf_res->fetch_assoc()) {
    $perf_labels[] = $row['department'];
    $perf_scores[] = round((float) $row['avg_score'], 1);
}

// Existing logic for branch users and audit logs
$branches_res = $conn->query("SELECT * FROM branches ORDER BY branch_name");
$branches = [];
while ($row = $branches_res->fetch_assoc()) {
    $branches[] = $row;
}
$selected_branch_id = isset($_GET['branch_id']) ? (int) $_GET['branch_id'] : (count($branches) > 0 ? $branches[0]['branch_id'] : null);
$branch_active_users = [];
if ($selected_branch_id) {
    $stmt = $conn->prepare("SELECT u.*, b.branch_name FROM users u LEFT JOIN branches b ON u.branch_id = b.branch_id WHERE u.branch_id = ? AND u.is_active = 1 ORDER BY u.full_name ASC");
    $stmt->bind_param("i", $selected_branch_id);
    $stmt->execute();
    $branch_active_users = $stmt->get_result();
    $stmt->close();
}
$audit_logs = $conn->query("SELECT al.*, u.full_name FROM audit_logs al LEFT JOIN users u ON al.user_id = u.user_id ORDER BY al.timestamp DESC LIMIT 10");
?>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
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
            <div class="stat-icon green"><i class="fas fa-user-tie"></i></div>
            <div class="stat-info">
                <h3><?php echo $total_employees; ?></h3>
                <p>Total Employees</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-file-signature"></i></div>
            <div class="stat-info">
                <h3><?php echo $pending_evals; ?></h3>
                <p>Pending Evaluations</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-user-check"></i></div>
            <div class="stat-info">
                <h3><?php echo $active_users; ?></h3>
                <p>Active System Users</p>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Row 1 -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="content-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie me-2"></i>Employment Status</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="content-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar me-2"></i>Department Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="deptChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Row 2 -->
<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="content-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-chart-line me-2"></i>Hiring Trends (12 Months)</h5>
            </div>
            <div class="card-body">
                <canvas id="hiringChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="content-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-venus-mars me-2"></i>Gender Diversity</h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="genderChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Performance Analytics -->
<div class="row mb-4">
    <div class="col-12">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="fas fa-star me-2"></i>Departmental Performance (Avg Score)</h5>
            </div>
            <div class="card-body">
                <canvas id="perfChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Active Users by Branch -->
    <div class="col-lg-6">
        <div class="content-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-building me-2"></i>Active Users by Branch</h5>
                <form method="GET" class="d-flex align-items-center" style="max-width: 200px;">
                    <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
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
                                <th>Role</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$branch_active_users || $branch_active_users->num_rows === 0): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No active users.</td>
                                </tr>
                            <?php else: ?>
                                <?php while ($u = $branch_active_users->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo e($u['full_name']); ?></strong></td>
                                        <td><span class="badge bg-primary"><?php echo e($u['role']); ?></span></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-lg-6">
        <div class="content-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-history me-2"></i>Recent Activity</h5>
                <a href="<?php echo BASE_URL; ?>/admin/audit-trail.php" class="btn btn-sm btn-outline-primary">View
                    All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Action</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($audit_logs->num_rows === 0): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No activity.</td>
                                </tr>
                            <?php else: ?>
                                <?php while ($log = $audit_logs->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo e($log['full_name'] ?? 'System'); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo e($log['action_type']); ?></span></td>
                                        <td><small><?php echo formatDateTime($log['timestamp']); ?></small></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15 } }
            }
        };

        // 1. Employment Status (Doughnut)
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($status_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($status_counts); ?>,
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                    hoverOffset: 4
                }]
            },
            options: commonOptions
        });

        // 2. Department Distribution (Horizontal Bar)
        new Chart(document.getElementById('deptChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($dept_labels); ?>,
                datasets: [{
                    label: 'Employees',
                    data: <?php echo json_encode($dept_counts); ?>,
                    backgroundColor: '#4e73df'
                }]
            },
            options: {
                ...commonOptions,
                indexAxis: 'y',
                plugins: { legend: { display: false } }
            }
        });

        // 3. Hiring Trends (Line)
        new Chart(document.getElementById('hiringChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($hiring_labels); ?>,
                datasets: [{
                    label: 'New Hires',
                    data: <?php echo json_encode($hiring_counts); ?>,
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.05)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                ...commonOptions,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });

        // 4. Gender Diversity (Pie)
        new Chart(document.getElementById('genderChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($gender_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($gender_counts); ?>,
                    backgroundColor: ['#4e73df', '#f66d9b', '#6c757d']
                }]
            },
            options: commonOptions
        });

        // 5. Performance (Bar)
        new Chart(document.getElementById('perfChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($perf_labels); ?>,
                datasets: [{
                    label: 'Avg Score (%)',
                    data: <?php echo json_encode($perf_scores); ?>,
                    backgroundColor: '#f6c23e'
                }]
            },
            options: {
                ...commonOptions,
                scales: { y: { beginAtZero: true, max: 100 } }
            }
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>