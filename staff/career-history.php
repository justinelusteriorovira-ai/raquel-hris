<?php
$page_title = 'Career History';
require_once '../includes/session-check.php';
checkRole(['HR Staff']);
require_once '../includes/header.php';
?>

<div class="coming-soon-page">
    <div class="content-card coming-soon-card">
        <div class="cs-icon"><i class="fas fa-route"></i></div>
        <h2>Career History Viewing</h2>
        <span class="cs-badge">Coming Soon</span>
        <p>View the career movement history of employees. Track promotions, transfers, and demotions with read-only access.</p>
        <div class="cs-features">
            <div class="cs-feature"><i class="fas fa-check"></i> View career movement timeline</div>
            <div class="cs-feature"><i class="fas fa-check"></i> See promotion and transfer history</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Track position changes over time</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Read-only access (no modifications)</div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
