<?php
$_SERVER['DOCUMENT_ROOT'] = 'c:\\xampp\\htdocs';
require 'c:\\xampp\\htdocs\\raquel-hris\\config\\database.php';

$employees = $conn->query("
    SELECT e.*, b.branch_name, d.department_name 
    FROM employees e 
    LEFT JOIN branches b ON e.branch_id = b.branch_id 
    LEFT JOIN departments d ON e.department_id = d.department_id 
    ORDER BY e.last_name, e.first_name
");

if (!$employees) {
    echo "Error: " . $conn->error;
} else {
    echo "Success! Row count: " . $employees->num_rows . "\n";
    while ($row = $employees->fetch_assoc()) {
        echo $row['first_name'] . " " . $row['last_name'] . " - " . $row['department_name'] . "\n";
    }
}
