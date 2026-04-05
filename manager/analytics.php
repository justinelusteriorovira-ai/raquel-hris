<?php
$page_title = 'Analytics';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/header.php';

// Get filter values
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$filter_branch = $_GET['branch'] ?? '';
$filter_dept = $_GET['department'] ?? '';

// Build WHERE clause for filters
$where = "WHERE ev.status = 'Approved'";
$params = [];
$types = '';

if (!empty($date_from)) {
    $where .= " AND ev.approved_date >= ?";
    $params[] = $date_from;
    $types .= 's';
}
if (!empty($date_to)) {
    $where .= " AND ev.approved_date <= ?";
    $params[] = $date_to . ' 23:59:59';
    $types .= 's';
}
if (!empty($filter_branch)) {
    $where .= " AND e.branch_id = ?";
    $params[] = (int) $filter_branch;
    $types .= 'i';
}
if (!empty($filter_dept)) {
    $where .= " AND e.department_id = ?";
    $params[] = (int) $filter_dept;
    $types .= 'i';
}

// Performance Distribution
$perf_dist = ['Outstanding' => 0, 'Exceeds Expectations' => 0, 'Meets Expectations' => 0, 'Needs Improvement' => 0];
$perf_q = $conn->prepare("SELECT ev.performance_level, COUNT(*) as count FROM evaluations ev LEFT JOIN employees e ON ev.employee_id = e.employee_id $where AND ev.performance_level IS NOT NULL GROUP BY ev.performance_level");
if (!empty($params))
    $perf_q->bind_param($types, ...$params);
$perf_q->execute();
$perf_result = $perf_q->get_result();
while ($row = $perf_result->fetch_assoc()) {
    if (isset($perf_dist[$row['performance_level']])) {
        $perf_dist[$row['performance_level']] = (int) $row['count'];
    }
}
$perf_q->close();

// Monthly Trends (last 6 months)
$monthly_data = [];
for ($i = 5; $i >= 0; $i--) {
    $month_label = date('M Y', strtotime("-$i months"));
    $month_num = date('m', strtotime("-$i months"));
    $year_num = date('Y', strtotime("-$i months"));
    $avg_q = $conn->query("SELECT AVG(total_score) as avg_score FROM evaluations WHERE status = 'Approved' AND MONTH(approved_date) = $month_num AND YEAR(approved_date) = $year_num");
    $avg_val = round($avg_q->fetch_assoc()['avg_score'] ?? 0, 1);
    $monthly_data[] = ['label' => $month_label, 'value' => $avg_val];
}

// Branch Comparison
$branch_data = [];
$branch_q = $conn->query("SELECT b.branch_name, AVG(ev.total_score) as avg_score
    FROM evaluations ev
    LEFT JOIN employees e ON ev.employee_id = e.employee_id
    LEFT JOIN branches b ON e.branch_id = b.branch_id
    WHERE ev.status = 'Approved' AND b.branch_name IS NOT NULL
    GROUP BY b.branch_id, b.branch_name");
while ($row = $branch_q->fetch_assoc()) {
    $branch_data[] = ['label' => $row['branch_name'], 'value' => round($row['avg_score'], 1)];
}

// Top Performers
$top_q = $conn->prepare("SELECT CONCAT(e.first_name, ' ', e.last_name) as name, e.job_title, b.branch_name, ev.total_score
    FROM evaluations ev
    LEFT JOIN employees e ON ev.employee_id = e.employee_id
    LEFT JOIN branches b ON e.branch_id = b.branch_id
    $where
    ORDER BY ev.total_score DESC LIMIT 5");
if (!empty($params))
    $top_q->bind_param($types, ...$params);
$top_q->execute();
$top_performers = $top_q->get_result();

// Get branches and departments for filters
$branches = $conn->query("SELECT * FROM branches ORDER BY branch_name");
$departments = $conn->query("SELECT department_id, department_name FROM departments WHERE is_active = 1 ORDER BY department_name");
?>

<!-- Filters -->
<div class="content-card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row align-items-end g-3">
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" class="form-control" name="date_from" value="<?php echo e($date_from); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" class="form-control" name="date_to" value="<?php echo e($date_to); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Branch</label>
                <select class="form-select" name="branch">
                    <option value="">All Branches</option>
                    <?php while ($b = $branches->fetch_assoc()): ?>
                        <option value="<?php echo $b['branch_id']; ?>" <?php echo ($filter_branch == $b['branch_id']) ? 'selected' : ''; ?>><?php echo e($b['branch_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Department</label>
                <select class="form-select" name="department">
                    <option value="">All Departments</option>
                    <?php while ($d = $departments->fetch_assoc()): ?>
                        <option value="<?php echo $d['department_id']; ?>" <?php echo ($filter_dept == $d['department_id']) ? 'selected' : ''; ?>><?php echo e($d['department_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Apply</button>
            </div>
        </form>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <!-- Performance Distribution -->
    <div class="col-lg-4">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie me-2"></i>Performance Distribution</h5>
            </div>
            <div class="card-body">
                <div class="chart-container"><canvas id="perfPieChart"></canvas></div>
            </div>
        </div>
    </div>

    <!-- Performance Trends -->
    <div class="col-lg-4">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="fas fa-chart-line me-2"></i>Performance Trends</h5>
            </div>
            <div class="card-body">
                <div class="chart-container"><canvas id="trendLineChart"></canvas></div>
            </div>
        </div>
    </div>

    <!-- Branch Comparison -->
    <div class="col-lg-4">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar me-2"></i>Branch Comparison</h5>
            </div>
            <div class="card-body">
                <div class="chart-container"><canvas id="branchBarChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<!-- Top Performers -->
<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-trophy me-2"></i>Top Performers</h5>
        <a href="?export=csv" class="btn btn-sm btn-outline-success"><i class="fas fa-download me-1"></i>Export CSV</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Branch</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rank = 1;
                    while ($tp = $top_performers->fetch_assoc()):
                        ?>
                        <tr>
                            <td><span class="badge bg-primary"><?php echo $rank++; ?></span></td>
                            <td><strong><?php echo e($tp['name']); ?></strong></td>
                            <td><?php echo e($tp['job_title']); ?></td>
                            <td><?php echo e($tp['branch_name'] ?? 'N/A'); ?></td>
                            <td><span
                                    class="badge <?php echo getPerformanceBadgeClass(getPerformanceLevel($tp['total_score'])); ?>"><?php echo $tp['total_score']; ?>%</span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($rank === 1): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    // This would normally be at the top before output, but for prototype simplicity:
    // In production, move this above any HTML output
}
?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Pie Chart
        new Chart(document.getElementById('perfPieChart'), {
            type: 'pie',
            data: {
                labels: ['Outstanding', 'Exceeds Expectations', 'Meets Expectations', 'Needs Improvement'],
                datasets: [{
                    data: [<?php echo implode(',', array_values($perf_dist)); ?>],
                    backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545'],
                    borderWidth: 2, borderColor: '#fff'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { padding: 10, usePointStyle: true, font: { size: 11 } } } }
            }
        });

        // Line Chart
        new Chart(document.getElementById('trendLineChart'), {
            type: 'line',
            data: {
                labels: [<?php echo implode(',', array_map(function ($m) {
                    return "'" . $m['label'] . "'"; }, $monthly_data)); ?>],
                datasets: [{
                    label: 'Avg Score',
                    data: [<?php echo implode(',', array_column($monthly_data, 'value')); ?>],
                    borderColor: '#294306', backgroundColor: 'rgba(41, 67, 6, 0.1)',
                    tension: 0.3, fill: true, pointRadius: 4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { y: { beginAtZero: false, min: 0, max: 100 } },
                plugins: { legend: { display: false } }
            }
        });

        // Bar Chart
        new Chart(document.getElementById('branchBarChart'), {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', array_map(function ($b) {
                    return "'" . $b['label'] . "'"; }, $branch_data)); ?>],
                datasets: [{
                    label: 'Avg Score',
                    data: [<?php echo implode(',', array_column($branch_data, 'value')); ?>],
                    backgroundColor: ['#294306', '#BD9414', '#D71920'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, max: 100 } },
                plugins: { legend: { display: false } }
            }
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>