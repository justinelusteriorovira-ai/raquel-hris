<?php
$conn = new mysqli('localhost', 'root', 'admin', 'raquel_hris');
$res = $conn->query("SELECT ev.evaluation_id, ev.status, ev.employee_id, e.branch_id FROM evaluations ev JOIN employees e ON ev.employee_id = e.employee_id;");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
echo "DONE\n";
