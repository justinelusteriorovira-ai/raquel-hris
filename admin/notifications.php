<?php
$page_title = 'Notifications';
require_once '../includes/session-check.php';
checkRole(['Admin']);
require_once '../includes/header.php';
?>

<div class="coming-soon-page">
    <div class="content-card coming-soon-card">
        <div class="cs-icon"><i class="fas fa-bell"></i></div>
        <h2>Admin Notifications</h2>
        <span class="cs-badge">Coming Soon</span>
        <p>Monitor system-wide alerts, security notifications, and administrative updates. Stay informed about the health and security of the system.</p>
        <div class="cs-features">
            <div class="cs-feature"><i class="fas fa-check"></i> System performance & health alerts</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Security breach & failed login notifications</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Database maintenance scheduling</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Broadcast administrative messages</div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
