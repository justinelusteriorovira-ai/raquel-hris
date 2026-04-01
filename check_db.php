<?php
$conn = new mysqli('localhost', 'root', 'admin', 'raquel_hris');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$eid = 1;
$res = $conn->query("SELECT * FROM employee_education WHERE employee_id=$eid");
echo "Education count: " . $res->num_rows . "\n";
$res2 = $conn->query("SELECT * FROM employee_work_experience WHERE employee_id=$eid");
echo "Work count: " . $res2->num_rows . "\n";
