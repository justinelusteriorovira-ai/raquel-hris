<?php
$page_title = 'Employee Search';
require_once '../includes/session-check.php';
checkRole(['HR Staff']);
require_once '../includes/header.php';
?>

<div class="coming-soon-page">
    <div class="content-card coming-soon-card">
        <div class="cs-icon"><i class="fas fa-search"></i></div>
        <h2>Employee Search</h2>
        <span class="cs-badge">Coming Soon</span>
        <p>Find employee profiles and view basic information. Search by name, ID, or department to quickly access the records you need.</p>
        <div class="cs-features">
            <div class="cs-feature"><i class="fas fa-check"></i> Search employees by name or ID</div>
            <div class="cs-feature"><i class="fas fa-check"></i> View employee profiles</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Update contact information only</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Quick access to department directory</div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
