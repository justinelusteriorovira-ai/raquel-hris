<?php
$page_title = 'Notifications';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/header.php';
?>

<div class="coming-soon-page">
    <div class="content-card coming-soon-card">
        <div class="cs-icon"><i class="fas fa-bell"></i></div>
        <h2>Notifications Center</h2>
        <span class="cs-badge">Coming Soon</span>
        <p>Centralized notification hub for all approval-related alerts, system updates, and important HR announcements.</p>
        <div class="cs-features">
            <div class="cs-feature"><i class="fas fa-check"></i> Real-time approval alerts</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Evaluation submission notifications</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Career movement pending alerts</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Mark as read / bulk actions</div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
