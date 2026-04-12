<?php
require_once __DIR__ . '/config/database.php';

// Simulate Staff User (usually user_id = 4 for staff@raquel.com)
$staff_user_id = 4;
$employee_id = 512; // Tester One
$template_id = 1;

// 1. Submit Evaluation
$kra_subtotal = 3.20; // assumed max 4.0 * 80% = 3.20
$beh_average = 4.0;   // max average
$total_score = 4.0;   // Wait, if it's 80/20 of 100%, total score should be out of 100? No, it's out of 100%. Actually the form calculates total in percentages. Let's say 100.
// Wait, function calculateEvalTotal() returns percentage out of 100. 
// A score of 4.0 * wait. Let's just do 95.5 as total score.
$total_score = 98.5;

$stmt = $conn->prepare("INSERT INTO evaluations (employee_id, template_id, evaluation_type, evaluation_period_start, evaluation_period_end, submitted_by, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_date, staff_comments, desired_position, career_growth_suited) VALUES (?, ?, 'Annual', '2026-01-01', '2026-12-31', ?, 'Pending Supervisor', ?, ?, ?, 'Outstanding', NOW(), 'Excellent tester', 'Senior Tester', 1)");

$stmt->bind_param("iiiddd", $employee_id, $template_id, $staff_user_id, $total_score, $kra_subtotal, $beh_average);

if ($stmt->execute()) {
    echo "Evaluation inserted successfully! Eval ID: " . $stmt->insert_id . "\n";
} else {
    echo "Error inserting evaluation: " . $stmt->error . "\n";
}
$eval_id = $stmt->insert_id;
$stmt->close();

// Insert dummy dev plan
$conn->query("INSERT INTO evaluation_dev_plans (evaluation_id, improvement_area, support_needed, time_frame) VALUES ($eval_id, 'Testing Tools', 'Training', '1 month')");

// 2. Simulate Supervisor Endorsement
// Update status to 'Pending Manager'
echo "Simulating Supervisor Endorsement...\n";
$conn->query("UPDATE evaluations SET status = 'Pending Manager', supervisor_comments = 'Agree with staff' WHERE evaluation_id = $eval_id");
echo "Supervisor endorsed.\n";

// 3. Simulate Manager Approval
echo "Simulating Manager Approval...\n";
$conn->query("UPDATE evaluations SET status = 'Approved', manager_comments = 'Approved. Proceed with promotion.', approved_date = NOW(), approved_by = 3 WHERE evaluation_id = $eval_id");
echo "Manager approved.\n";

$conn->query("INSERT INTO career_movements (employee_id, movement_type, previous_position, new_position, previous_branch_id, new_branch_id, effective_date, reason, approval_status, approved_by, logged_by, is_applied) VALUES ($employee_id, 'Role Change', 'Junior Developer', 'Senior Tester', 2, 2, '2026-05-01', 'Automatically generated from approved Performance Evaluation (ID: $eval_id)', 'Approved', 3, 4, 0)");
echo "Career movement queued for promotion.\n";

