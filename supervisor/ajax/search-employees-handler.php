<?php
require_once '../../includes/session-check.php';
checkRole(['HR Supervisor']);
require_once '../../includes/functions.php';

header('Content-Type: application/json');

$branch_id = $_SESSION['branch_id'];
$search = trim($_GET['search'] ?? '');
$dept = trim($_GET['department'] ?? '');
$status = trim($_GET['status'] ?? '');
$type = trim($_GET['type'] ?? '');
$position = trim($_GET['position'] ?? '');

$query = "SELECT e.*, b.branch_name FROM employees e 
          LEFT JOIN branches b ON e.branch_id = b.branch_id 
          WHERE e.branch_id = $branch_id AND e.is_active = 1";

$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_id LIKE ?)";
    $sterm = "%$search%";
    $params[] = $sterm;
    $params[] = $sterm;
    $params[] = $sterm;
    $types .= "sss";
}

if (!empty($dept)) {
    $query .= " AND e.department = ?";
    $params[] = $dept;
    $types .= "s";
}

if (!empty($status)) {
    $query .= " AND e.employment_status = ?";
    $params[] = $status;
    $types .= "s";
}

if (!empty($type)) {
    $query .= " AND e.employment_type = ?";
    $params[] = $type;
    $types .= "s";
}

if (!empty($position)) {
    $query .= " AND e.job_title LIKE ?";
    $pterm = "%$position%";
    $params[] = $pterm;
    $types .= "s";
}

$query .= " ORDER BY e.last_name, e.first_name LIMIT 50";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$employees = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Prepare data for JSON (formatting badges, etc.)
foreach ($employees as &$emp) {
    $emp['full_name'] = $emp['last_name'] . ', ' . $emp['first_name'];
    $emp['hire_date_fmt'] = formatDate($emp['hire_date']);
    $emp['base_url'] = BASE_URL;
}

echo json_encode($employees);
?>
