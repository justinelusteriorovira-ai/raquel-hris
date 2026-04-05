<?php
/**
 * Recovery Script: Fix Missing system_settings Table
 * This script will create the system_settings table and seed it with default values.
 */

// 1. Load database configuration
require_once 'config/database.php';

echo "<h2>Raquel HRIS - Database Recovery</h2>";
echo "<p>Starting database maintenance...</p>";

// 2. Define the table creation SQL
$sql_create = "CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql_create)) {
    echo "<p style='color: green;'>✅ Table 'system_settings' created successfully or already exists.</p>";
} else {
    die("<p style='color: red;'>❌ Error creating table: " . $conn->error . "</p>");
}

// 3. Seed default values
$defaults = [
    'company_name' => 'Raquel Pawnshop',
    'contact_email' => 'hr@raquel.com',
    'system_logo' => 'assets/img/logo/logo.png',
    'session_timeout' => '240',
    'pwd_min_length' => '8',
    'pwd_require_upper' => '1',
    'pwd_require_number' => '1',
    'pwd_require_special' => '1'
];

echo "<p>Seeding default settings...</p>";
$stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");

foreach ($defaults as $key => $value) {
    $stmt->bind_param("sss", $key, $value, $value);
    if ($stmt->execute()) {
        echo "<li>Setting '{$key}' updated.</li>";
    } else {
        echo "<li style='color: orange;'>⚠️ Failed to update setting '{$key}': " . $stmt->error . "</li>";
    }
}

$stmt->close();
$conn->close();

echo "<p style='color: green;'><b>Maintenance complete!</b> You can now delete this file and refresh your dashboard.</p>";
echo "<a href='admin/dashboard.php'>Go to Dashboard</a>";
?>
