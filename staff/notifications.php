<?php
$page_title = 'Notifications';
require_once '../includes/session-check.php';
checkRole(['HR Staff']);
require_once '../includes/header.php';
?>

<div class="coming-soon-page">
    <div class="content-card coming-soon-card">
        <div class="cs-icon"><i class="fas fa-bell"></i></div>
        <h2>Notifications Center</h2>
        <span class="cs-badge">Coming Soon</span>
        <p>View approval status updates, rejection feedback, and system alerts. Stay informed about your evaluation submissions.</p>
        <div class="cs-features">
            <div class="cs-feature"><i class="fas fa-check"></i> Evaluation approval / rejection alerts</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Returned-for-revision notifications</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Supervisor feedback messages</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Mark as read / bulk actions</div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
