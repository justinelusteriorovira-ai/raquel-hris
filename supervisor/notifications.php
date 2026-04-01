<?php
$page_title = 'Notifications';
require_once '../includes/session-check.php';
checkRole(['HR Supervisor']);
require_once '../includes/header.php';
?>

<div class="coming-soon-page">
    <div class="content-card coming-soon-card">
        <div class="cs-icon"><i class="fas fa-bell"></i></div>
        <h2>Notifications Center</h2>
        <span class="cs-badge">Coming Soon</span>
        <p>Stay updated with endorsement requests, evaluation submissions, and system alerts. Manage all your notifications in one place.</p>
        <div class="cs-features">
            <div class="cs-feature"><i class="fas fa-check"></i> Endorsement request alerts</div>
            <div class="cs-feature"><i class="fas fa-check"></i> New evaluation submission notifications</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Career movement update alerts</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Mark as read / bulk actions</div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
