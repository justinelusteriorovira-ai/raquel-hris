<?php
$page_title = 'Career Movement Approval';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/header.php';
?>

<div class="coming-soon-page">
    <div class="content-card coming-soon-card">
        <div class="cs-icon"><i class="fas fa-route"></i></div>
        <h2>Career Movement Approval</h2>
        <span class="cs-badge">Coming Soon</span>
        <p>Review and provide final approval for all career movements including promotions, transfers, and demotions submitted by HR Supervisors.</p>
        <div class="cs-features">
            <div class="cs-feature"><i class="fas fa-check"></i> Final approve or reject career movements</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Auto-update employee records upon approval</div>
            <div class="cs-feature"><i class="fas fa-check"></i> View full movement history per employee</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Add remarks and conditions to approvals</div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
