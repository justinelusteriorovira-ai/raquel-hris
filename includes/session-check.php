<?php
// ============================================
// Session Validation
// ============================================

require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

/**
 * Check if current user has the required role
 * @param array $allowed_roles Array of allowed role strings
 */
function checkRole($allowed_roles) {
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: " . BASE_URL . "/index.php");
        exit();
    }
}
?>
