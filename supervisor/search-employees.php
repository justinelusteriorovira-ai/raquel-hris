<?php
$page_title = 'Search Employees';
require_once '../includes/session-check.php';
checkRole(['HR Supervisor']);
require_once '../includes/header.php';
?>

<div class="coming-soon-page">
    <div class="content-card coming-soon-card">
        <div class="cs-icon"><i class="fas fa-search"></i></div>
        <h2>Search Employees</h2>
        <span class="cs-badge">Coming Soon</span>
        <p>Quickly find and view employee profiles using advanced search. Search by name, department, position, or employee ID.</p>
        <div class="cs-features">
            <div class="cs-feature"><i class="fas fa-check"></i> Search by name, ID, or department</div>
            <div class="cs-feature"><i class="fas fa-check"></i> View employee profiles and contact info</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Quick access to evaluation history</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Filter by status and employment type</div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
