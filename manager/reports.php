<?php
$page_title = 'Report Generation';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/header.php';
?>

<div class="coming-soon-page">
    <div class="content-card coming-soon-card">
        <div class="cs-icon"><i class="fas fa-file-pdf"></i></div>
        <h2>Report Generation</h2>
        <span class="cs-badge">Coming Soon</span>
        <p>Generate comprehensive HR reports for strategic decision-making. Export to PDF or Excel with customizable date ranges and filters.</p>
        <div class="cs-features">
            <div class="cs-feature"><i class="fas fa-check"></i> Performance Summary Report</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Employee Evaluation Report</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Career Movement Report</div>
            <div class="cs-feature"><i class="fas fa-check"></i> Attendance & Compliance Report</div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
