<?php
$conn = new mysqli('localhost', 'root', 'admin', 'raquel_hris');
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

$sqls = [
    "CREATE TABLE IF NOT EXISTS departments (
        department_id INT AUTO_INCREMENT PRIMARY KEY,
        department_name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // Seed from existing unique department values in employees
    "INSERT IGNORE INTO departments (department_name)
     SELECT DISTINCT TRIM(department) FROM employees WHERE department IS NOT NULL AND TRIM(department) != '';",
];

foreach ($sqls as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "OK: " . substr($sql, 0, 60) . "...<br>";
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
}

echo "<br><strong>Done. <a href='/raquel-hris/manager/departments.php'>Go to Departments</a></strong>";
$conn->close();
?>