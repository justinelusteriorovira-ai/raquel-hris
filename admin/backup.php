<?php
$page_title = 'System Backup';
require_once '../includes/session-check.php';
checkRole(['Admin']);
require_once '../includes/header.php';
?>

<div class="coming-soon-page">
    <div class="content-card coming-soon-card">
        <div class="cs-icon"><i class="fas fa-database"></i></div>
        <h2>System Backup & Restore</h2>
        <span class="cs-badge">Coming Soon</span>
        <p>Ensure data integrity with automated and manual system backups. Restore the system to previous states and manage backup history.</p>
        <div class="cs-features">
            <div class="cs-feature"><i class="fas fa-check"></i> Manual database and file backups</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Scheduled automated backups</div>
            <div class="cs-feature"><i class="fas fa-check"></i> System restoration from backup files</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Backup cloud synchronization</div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
