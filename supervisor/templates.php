<?php
$page_title = 'Template Viewing';
require_once '../includes/session-check.php';
checkRole(['HR Supervisor']);
require_once '../includes/header.php';
?>

<div class="coming-soon-page">
    <div class="content-card coming-soon-card">
        <div class="cs-icon"><i class="fas fa-file-alt"></i></div>
        <h2>Template Viewing</h2>
        <span class="cs-badge">Coming Soon</span>
        <p>Browse and review all available evaluation templates. Read-only access to understand scoring criteria and performance metrics.</p>
        <div class="cs-features">
            <div class="cs-feature"><i class="fas fa-check"></i> View all active evaluation templates</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Review criteria and weight distribution</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Understand performance level thresholds</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Read-only access (no editing)</div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
