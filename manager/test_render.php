<?php
$_SERVER['DOCUMENT_ROOT'] = 'C:/xampp/htdocs';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/raquel-hris/manager/dashboard.php';
$_SERVER['PHP_SELF'] = '/raquel-hris/manager/dashboard.php';
$_SERVER['SCRIPT_NAME'] = '/raquel-hris/manager/dashboard.php';
$_SERVER['SCRIPT_FILENAME'] = 'C:/xampp/htdocs/raquel-hris/manager/dashboard.php';

session_start();
$_SESSION['user_id'] = 1; // Assuming manager@raquel.com is user_id 1
$_SESSION['role'] = 'HR Manager';
$_SESSION['full_name'] = 'Manager Test';
$_SESSION['email'] = 'manager@raquel.com';
$_SESSION['branch_id'] = 1;

ob_start();
include 'dashboard.php';
$output = ob_get_clean();

echo strlen($output) . " bytes rendered.\n";
if (strpos($output, 'Fatal error') !== false || strpos($output, 'Parse error') !== false) {
    echo "ERROR FOUND:\n";
    echo substr($output, 0, 1000);
} else {
    echo "Success. HTML length: " . strlen($output) . "\n";
}
