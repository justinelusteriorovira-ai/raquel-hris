<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$test_email = 'test@example.com';
$test_ip = '127.0.0.99'; // Use a unique test IP

// 1. Clear any existing attempts
clearLoginAttempts($conn, $test_email, $test_ip);
echo "1. Cleared attempts.\n";

// 2. Register 5 failed attempts
for ($i = 1; $i <= 5; $i++) {
    registerLoginAttempt($conn, $test_email, $test_ip);
    echo "Registering attempt $i...\n";
}

// 3. Check if blocked
$blocked = checkLoginBruteForce($conn, $test_email, $test_ip);
if ($blocked) {
    echo "SUCCESS: Account is blocked after 5 attempts.\n";
} else {
    echo "FAILURE: Account is NOT blocked after 5 attempts.\n";
}

// 4. Clear attempts
clearLoginAttempts($conn, $test_email, $test_ip);
$blocked_after_clear = checkLoginBruteForce($conn, $test_email, $test_ip);
if (!$blocked_after_clear) {
    echo "SUCCESS: Account is unblocked after clearing attempts.\n";
} else {
    echo "FAILURE: Account is still blocked after clearing attempts.\n";
}

$conn->close();
?>
