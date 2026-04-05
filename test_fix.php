<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

echo "Testing getSetting('company_name'): " . getSetting($conn, 'company_name', 'Not Found') . "\n";
echo "Testing getSetting('contact_email'): " . getSetting($conn, 'contact_email', 'Not Found') . "\n";
?>
