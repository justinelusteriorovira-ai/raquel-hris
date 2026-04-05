<?php
require_once 'config/database.php';
$query = "
    SELECT e.*, b.branch_name, d.department_name 
    FROM employees e 
    LEFT JOIN branches b ON e.branch_id = b.branch_id 
    LEFT JOIN departments d ON e.department_id = d.department_id 
    ORDER BY e.last_name, e.first_name
";
$result = $conn->query($query);
if ($result) {
    echo "Query successful. Found " . $result->num_rows . " employees.\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['employee_id'] . " - Name: " . $row['first_name'] . " " . $row['last_name'] . " - Dept: " . $row['department_name'] . "\n";
    }
} else {
    echo "Query failed: " . $conn->error . "\n";
}
?>
