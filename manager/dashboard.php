<?php
$page_title = 'Manager Dashboard';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/header.php';

// Fetch stats
$branch_id = $_SESSION['branch_id'];

$total_employees = $conn->query("SELECT COUNT(*) as c FROM employees WHERE is_active = 1")->fetch_assoc()['c'];
$pending_evals = $conn->query("SELECT COUNT(*) as c FROM evaluations WHERE status = 'Pending Manager'")->fetch_assoc()['c'];
$pending_movements = $conn->query("SELECT COUNT(*) as c FROM career_movements WHERE approval_status = 'Pending'")->fetch_assoc()['c'];
$avg_score_result = $conn->query("SELECT AVG(total_score) as avg FROM evaluations WHERE status = 'Approved'");
$avg_score = round($avg_score_result->fetch_assoc()['avg'] ?? 0, 1);
$new_evals_month = $conn->query("SELECT COUNT(*) as c FROM evaluations WHERE MONTH(submitted_date) = MONTH(CURRENT_DATE()) AND YEAR(submitted_date) = YEAR(CURRENT_DATE())")->fetch_assoc()['c'];
$total_branches = $conn->query("SELECT COUNT(*) as c FROM branches")->fetch_assoc()['c'];

// Gender Counts
$male_count = $conn->query("SELECT COUNT(*) as c FROM employees WHERE gender = 'Male' AND is_active = 1")->fetch_assoc()['c'];
$female_count = $conn->query("SELECT COUNT(*) as c FROM employees WHERE gender = 'Female' AND is_active = 1")->fetch_assoc()['c'];

// Fetch pending evaluations (5 most recent)
$pending_evals_list = $conn->query("SELECT ev.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name, u.full_name as submitted_by_name
    FROM evaluations ev
    LEFT JOIN employees e ON ev.employee_id = e.employee_id
    LEFT JOIN users u ON ev.submitted_by = u.user_id
    WHERE ev.status = 'Pending Manager'
    ORDER BY ev.submitted_date DESC LIMIT 5");

// Fetch pending career movements (5 most recent)
$pending_cm_list = $conn->query("SELECT cm.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name, u.full_name as logged_by_name
    FROM career_movements cm
    LEFT JOIN employees e ON cm.employee_id = e.employee_id
    LEFT JOIN users u ON cm.logged_by = u.user_id
    WHERE cm.approval_status = 'Pending'
    ORDER BY cm.created_at DESC LIMIT 5");

// 1. Performance Distribution Data
$perf_dist = $conn->query("SELECT performance_level, COUNT(*) as count FROM evaluations WHERE status = 'Approved' AND performance_level IS NOT NULL GROUP BY performance_level");
$perf_data = ['Outstanding' => 0, 'Exceeds Expectations' => 0, 'Meets Expectations' => 0, 'Needs Improvement' => 0];
while ($row = $perf_dist->fetch_assoc()) {
    if (isset($perf_data[$row['performance_level']])) {
        $perf_data[$row['performance_level']] = (int) $row['count'];
    }
}

// 2. Evaluation Status Data
$status_dist = $conn->query("SELECT status, COUNT(*) as count FROM evaluations GROUP BY status");
$status_labels = [];
$status_counts = [];
while ($row = $status_dist->fetch_assoc()) {
    $status_labels[] = $row['status'];
    $status_counts[] = (int) $row['count'];
}
?>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <h3>
                    <?php echo $total_employees; ?>
                </h3>
                <p>Total Employees</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="stat-card">
            <div class="stat-icon info"><i class="fas fa-mars"></i></div>
            <div class="stat-info">
                <h3><?php echo $male_count; ?></h3>
                <p>Male Employees</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="stat-card">
            <div class="stat-icon purple" style="background: rgba(232, 62, 140, 0.1); color: #e83e8c;"><i
                    class="fas fa-venus"></i></div>
            <div class="stat-info">
                <h3><?php echo $female_count; ?></h3>
                <p>Female Employees</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h3><?php echo $pending_evals; ?></h3>
                <p>Pending Evaluations</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-exchange-alt"></i></div>
            <div class="stat-info">
                <h3><?php echo $pending_movements; ?></h3>
                <p>Pending Movements</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-star"></i></div>
            <div class="stat-info">
                <h3><?php echo $avg_score; ?>%</h3>
                <p>Average Score</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="stat-card">
            <div class="stat-icon info"><i class="fas fa-file-alt"></i></div>
            <div class="stat-info">
                <h3><?php echo $new_evals_month; ?></h3>
                <p>New Evals This Month</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <a href="branches.php" class="text-decoration-none border-0 p-0 m-0 w-100">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-building"></i></div>
                <div class="stat-info">
                    <h3><?php echo $total_branches; ?></h3>
                    <p>Total Branches</p>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Performance Distribution -->
    <div class="col-lg-6">
        <div class="content-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie me-2"></i>Performance Distribution</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:300px;">
                    <canvas id="perfPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Evaluation Status -->
    <div class="col-lg-6">
        <div class="content-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-tasks me-2"></i>Evaluation Overview (By Status)</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:300px;">
                    <canvas id="statusDonutChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Pending Approvals -->
    <div class="col-12">
        <div class="content-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <ul class="nav nav-tabs card-header-tabs" id="pendingTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="eval-tab" data-bs-toggle="tab" data-bs-target="#evals"
                            type="button" role="tab">
                            <i class="fas fa-file-alt me-2"></i>Evaluations
                            <?php if ($pending_evals > 0): ?><span
                                    class="badge bg-warning text-dark ms-1"><?php echo $pending_evals; ?></span><?php endif; ?>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="cm-tab" data-bs-toggle="tab" data-bs-target="#movements"
                            type="button" role="tab">
                            <i class="fas fa-exchange-alt me-2"></i>Career Movements
                            <?php if ($pending_movements > 0): ?><span
                                    class="badge bg-warning text-dark ms-1"><?php echo $pending_movements; ?></span><?php endif; ?>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-0">
                <div class="tab-content" id="pendingTabsContent">
                    <!-- Evaluations Tab -->
                    <div class="tab-pane fade show active" id="evals" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Submitted By</th>
                                        <th>Date</th>
                                        <th>Score</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($pending_evals_list->num_rows === 0): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No pending evaluations.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php while ($row = $pending_evals_list->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?php echo e($row['employee_name']); ?></strong></td>
                                                <td><?php echo e($row['submitted_by_name']); ?></td>
                                                <td><small><?php echo formatDate($row['submitted_date']); ?></small></td>
                                                <td><span
                                                        class="badge <?php echo getPerformanceBadgeClass($row['performance_level']); ?>"><?php echo $row['total_score']; ?>%</span>
                                                </td>
                                                <td><a href="<?php echo BASE_URL; ?>/manager/pending-approvals.php?review=<?php echo $row['evaluation_id']; ?>"
                                                        class="btn btn-sm btn-primary">Review</a></td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <tr>
                                            <td colspan="5" class="text-center bg-light">
                                                <a href="<?php echo BASE_URL; ?>/manager/pending-approvals.php"
                                                    class="text-decoration-none small fw-bold">View All Pending
                                                    Evaluations</a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Career Movements Tab -->
                    <div class="tab-pane fade" id="movements" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Type</th>
                                        <th>Effective Date</th>
                                        <th>Logged By</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($pending_cm_list->num_rows === 0): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No pending career movements.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php while ($row = $pending_cm_list->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?php echo e($row['employee_name']); ?></strong></td>
                                                <td><span
                                                        class="badge bg-info text-dark"><?php echo e($row['movement_type']); ?></span>
                                                </td>
                                                <td><small><?php echo formatDate($row['effective_date']); ?></small></td>
                                                <td><?php echo e($row['logged_by_name']); ?></td>
                                                <td><a href="<?php echo BASE_URL; ?>/manager/career-movement-approval.php"
                                                        class="btn btn-sm btn-primary">Review</a></td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <tr>
                                            <td colspan="5" class="text-center bg-light">
                                                <a href="<?php echo BASE_URL; ?>/manager/career-movement-approval.php"
                                                    class="text-decoration-none small fw-bold">View All Pending
                                                    Movements</a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
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

        // 1. Performance Distribution (Pie)
        new Chart(document.getElementById('perfPieChart'), {
            type: 'pie',
            data: {
                labels: ['Outstanding', 'Exceeds Expectations', 'Meets Expectations', 'Needs Improvement'],
                datasets: [{
                    data: [<?php echo $perf_data['Outstanding']; ?>, <?php echo $perf_data['Exceeds Expectations']; ?>, <?php echo $perf_data['Meets Expectations']; ?>, <?php echo $perf_data['Needs Improvement']; ?>],
                    backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: commonOptions
        });

        // 2. Evaluation Status (Doughnut)
        new Chart(document.getElementById('statusDonutChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($status_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($status_counts); ?>,
                    backgroundColor: ['#6c757d', '#ffc107', '#17a2b8', '#28a745', '#dc3545', '#007bff'],
                    hoverOffset: 4
                }]
            },
            options: commonOptions
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>