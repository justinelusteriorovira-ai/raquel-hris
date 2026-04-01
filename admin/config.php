<?php
$page_title = 'System Configuration';
require_once '../includes/session-check.php';
checkRole(['Admin']);
require_once '../includes/header.php';
?>

<div class="coming-soon-page">
    <div class="content-card coming-soon-card">
        <div class="cs-icon"><i class="fas fa-cogs"></i></div>
        <h2>System Configuration</h2>
        <span class="cs-badge">Coming Soon</span>
        <p>Manage global system settings, security protocols, session handling, and advanced password policies for the entire HRIS platform.</p>
        <div class="cs-features">
            <div class="cs-feature"><i class="fas fa-check"></i> Global security & firewall settings</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Session timeout & login policies</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Advanced password complexity rules</div>
            <div class="cs-feature"><i class="fas fa-check"></i> API & integration configurations</div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
