<?php
$page_title = 'Employee Information';
require_once '../includes/session-check.php';
checkRole(['HR Supervisor']);
require_once '../includes/header.php';
?>

<div class="coming-soon-page">
    <div class="content-card coming-soon-card">
        <div class="cs-icon"><i class="fas fa-address-book"></i></div>
        <h2>Employee Information</h2>
        <span class="cs-badge">Coming Soon</span>
        <p>View all employee profiles and update contact information. Access comprehensive employee records for oversight purposes.</p>
        <div class="cs-features">
            <div class="cs-feature"><i class="fas fa-check"></i> View all active employee profiles</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Update employee contact information</div>
            <div class="cs-feature"><i class="fas fa-check"></i> View department and position details</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Access employment history</div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
