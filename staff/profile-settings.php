<?php
$page_title = 'Profile & Settings';
require_once '../includes/session-check.php';
checkRole(['HR Staff']);
require_once '../includes/header.php';
?>

<div class="coming-soon-page">
    <div class="content-card coming-soon-card">
        <div class="cs-icon"><i class="fas fa-user-cog"></i></div>
        <h2>Profile & Settings</h2>
        <span class="cs-badge">Coming Soon</span>
        <p>Manage your account profile, change your password, and update personal preferences for a tailored experience.</p>
        <div class="cs-features">
            <div class="cs-feature"><i class="fas fa-check"></i> View and edit your profile</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Change password securely</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Set notification preferences</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Customize display settings</div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
