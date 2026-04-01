<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'HR Manager';

// include db
require_once "includes/session-check.php";
// check what error
if ($conn->error) echo "Error: " . $conn->error . "\n";

$total_employees = $conn->query("SELECT COUNT(*) as c FROM employees WHERE is_active = 1")->fetch_assoc()['c'];
$pending_evals = $conn->query("SELECT COUNT(*) as c FROM evaluations WHERE status = 'Pending Manager'")->fetch_assoc()['c'];
$pending_movements = $conn->query("SELECT COUNT(*) as c FROM career_movements WHERE approval_status = 'Pending'")->fetch_assoc()['c'];
$avg_score_result = $conn->query("SELECT AVG(total_score) as avg FROM evaluations WHERE status = 'Approved'");
$avg_score = round($avg_score_result->fetch_assoc()['avg'] ?? 0, 1);
$new_evals_month = $conn->query("SELECT COUNT(*) as c FROM evaluations WHERE MONTH(submitted_date) = MONTH(CURRENT_DATE()) AND YEAR(submitted_date) = YEAR(CURRENT_DATE())")->fetch_assoc()['c'];

echo "Total: $total_employees\n";
echo "Pending Evals: $pending_evals\n";
echo "Pending Moves: $pending_movements\n";
echo "Avg Score: $avg_score\n";
echo "New Evals: $new_evals_month\n";
