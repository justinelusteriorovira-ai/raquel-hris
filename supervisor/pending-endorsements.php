<?php
$page_title = 'Pending Endorsements';
require_once '../includes/session-check.php';
checkRole(['HR Supervisor']);
require_once '../includes/functions.php';

// Handle endorsement/return (MUST be before header.php to allow redirect)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $eval_id = (int)$_POST['evaluation_id'];
    $action = $_POST['action'];
    $comments = trim($_POST['supervisor_comments'] ?? '');

    if ($action === 'endorse') {
        $stmt = $conn->prepare("UPDATE evaluations SET status = 'Pending Manager', endorsed_by = ?, endorsed_date = NOW(), supervisor_comments = ? WHERE evaluation_id = ?");
        $stmt->bind_param("isi", $_SESSION['user_id'], $comments, $eval_id);
        $stmt->execute();
        $stmt->close();

        // Notify all HR Managers
        $managers = $conn->query("SELECT user_id FROM users WHERE role = 'HR Manager' AND is_active = 1");
        $eval_info = $conn->query("SELECT CONCAT(e.first_name, ' ', e.last_name) as emp_name FROM evaluations ev LEFT JOIN employees e ON ev.employee_id = e.employee_id WHERE ev.evaluation_id = $eval_id")->fetch_assoc();
        while ($mgr = $managers->fetch_assoc()) {
            createNotification($conn, $mgr['user_id'], 'Evaluation Endorsed', "Evaluation for {$eval_info['emp_name']} has been endorsed and requires your approval.", BASE_URL . '/manager/pending-approvals.php');
        }
        logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'Evaluation', $eval_id, "Endorsed evaluation for {$eval_info['emp_name']}");
        redirectWith(BASE_URL . '/supervisor/pending-endorsements.php', 'success', 'Evaluation endorsed and forwarded to Manager.');

    } elseif ($action === 'return') {
        if (empty($comments)) {
            redirectWith(BASE_URL . '/supervisor/pending-endorsements.php', 'danger', 'Comments are required when returning an evaluation.');
        }
        $stmt = $conn->prepare("UPDATE evaluations SET status = 'Returned', supervisor_comments = ? WHERE evaluation_id = ?");
        $stmt->bind_param("si", $comments, $eval_id);
        $stmt->execute();
        $stmt->close();

        $eval_info = $conn->query("SELECT ev.submitted_by, CONCAT(e.first_name, ' ', e.last_name) as emp_name FROM evaluations ev LEFT JOIN employees e ON ev.employee_id = e.employee_id WHERE ev.evaluation_id = $eval_id")->fetch_assoc();
        if ($eval_info['submitted_by']) {
            createNotification($conn, $eval_info['submitted_by'], 'Evaluation Returned', "Your evaluation for {$eval_info['emp_name']} has been returned for revision.", BASE_URL . '/staff/my-submissions.php');
        }
        logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'Evaluation', $eval_id, "Returned evaluation for {$eval_info['emp_name']}");
        redirectWith(BASE_URL . '/supervisor/pending-endorsements.php', 'warning', 'Evaluation returned for revision.');
    }
}

require_once '../includes/header.php';

// Fetch pending evaluations
$pending = $conn->query("SELECT ev.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.job_title,
    u.full_name as submitted_by_name, et.template_name
    FROM evaluations ev
    LEFT JOIN employees e ON ev.employee_id = e.employee_id
    LEFT JOIN users u ON ev.submitted_by = u.user_id
    LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id
    WHERE ev.status = 'Pending Supervisor'
    ORDER BY ev.submitted_date DESC");

// Fetch history count for summary
$history_count = $conn->query("SELECT COUNT(*) as cnt FROM evaluations WHERE endorsed_by = {$_SESSION['user_id']} AND status IN ('Pending Manager', 'Approved', 'Rejected', 'Returned')")->fetch_assoc()['cnt'];
$pending_count = $pending->num_rows;

// Prepare results in array
$all_pending = [];
while ($row = $pending->fetch_assoc()) {
    $all_pending[] = $row;
}
?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="content-card text-center p-3 h-100 border-start border-4 border-primary shadow-sm">
            <div class="display-6 fw-bold text-primary"><?php echo $pending_count; ?></div>
            <div class="text-muted small fw-bold text-uppercase">Pending Your Endorsement</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="content-card text-center p-3 h-100 border-start border-4 border-success shadow-sm">
            <div class="display-6 fw-bold text-success"><?php echo $history_count; ?></div>
            <div class="text-muted small fw-bold text-uppercase">Total Processed</div>
        </div>
    </div>
    <div class="col-md-4">
        <a href="evaluation-history.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 bg-white" style="border: 2px dashed #dee2e6;">
            <i class="fas fa-history mb-2 fa-2x"></i>
            <span class="fw-bold text-uppercase small">View Evaluation History</span>
        </a>
    </div>
</div>

<div class="content-card border-0 shadow-sm">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <h5 class="mb-0"><i class="fas fa-clipboard-check me-2 text-primary"></i>Evaluations Pending Endorsement</h5>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="form-control form-control-sm" id="customSearchPending" placeholder="Search employee or position...">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="pendingTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">Employee</th>
                        <th>Position</th>
                        <th>Submitted By</th>
                        <th>Date</th>
                        <th>Score</th>
                        <th>Level</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($all_pending)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-check-circle fa-3x mb-3 d-block opacity-25"></i>No pending endorsements.</td></tr>
                    <?php else: ?>
                        <?php foreach ($all_pending as $row): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold"><?php echo e($row['employee_name']); ?></div>
                                    <div class="x-small text-muted"><?php echo e($row['template_name']); ?></div>
                                </td>
                                <td><?php echo e($row['job_title']); ?></td>
                                <td><div class="small"><?php echo e($row['submitted_by_name']); ?></div></td>
                                <td><small class="text-muted"><?php echo formatDate($row['submitted_date']); ?></small></td>
                                <td>
                                    <div class="fw-bold"><?php echo $row['total_score']; ?>%</div>
                                    <div class="progress mt-1" style="height: 4px; width: 60px;">
                                        <div class="progress-bar bg-primary" style="width: <?php echo min(100, (float)$row['total_score'] / 4 * 100); ?>%;"></div>
                                    </div>
                                </td>
                                <td><span class="badge <?php echo getPerformanceBadgeClass($row['performance_level']); ?> rounded-pill px-2" style="font-size:0.7rem;"><?php echo e($row['performance_level']); ?></span></td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#reviewModal<?php echo $row['evaluation_id']; ?>">
                                        <i class="fas fa-clipboard-check me-1"></i>Review
                                    </button>
                                </td>
                            </tr>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Performance Evaluation Summary & Rating Scale -->
                                            <div class="row mb-4">
                                                <div class="col-lg-8">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered text-center align-middle mb-0">
                                                            <thead class="bg-light small fw-bold">
                                                                <tr>
                                                                    <th class="text-start">Performance Evaluation Summary</th>
                                                                    <th style="width:80px;">Weight</th>
                                                                    <th style="width:100px;">Rating</th>
                                                                    <th style="width:140px;">Signature</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-start small">I. Key Result Areas based on Strategic Programs and Regular Job Requirements</td>
                                                                    <td class="small"><?php echo $row['kra_weight'] ?? 80; ?>%</td>
                                                                    <td class="fw-bold small"><?php echo $row['kra_subtotal'] ?? '-'; ?></td>
                                                                    <td class="small text-muted border-bottom-0"><div style="border-bottom: 1px solid #ccc; height: 15px;"></div>Employee</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-start small">II. Key Result Areas based on Behavior and Values</td>
                                                                    <td class="small"><?php echo $row['behavior_weight'] ?? 20; ?>%</td>
                                                                    <td class="fw-bold small"><?php echo $row['behavior_average'] ?? '-'; ?></td>
                                                                    <td class="small text-muted border-bottom-0"><div style="border-bottom: 1px solid #ccc; height: 15px;"></div>Rater</td>
                                                                </tr>
                                                                <tr class="table-active fw-bold small">
                                                                    <td class="text-end">TOTAL</td>
                                                                    <td>100%</td>
                                                                    <td class="text-primary"><?php echo $row['total_score']; ?></td>
                                                                    <td></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="content-card mb-0 border-info">
                                                        <div class="card-header bg-light py-1">
                                                            <h6 class="mb-0 fw-bold fw-bold small text-info text-uppercase" style="font-size:0.65rem;"><i class="fas fa-info-circle me-1"></i>Performance Rating Scale</h6>
                                                        </div>
                                                        <div class="p-0">
                                                            <table class="table table-sm table-bordered mb-0 small align-middle" style="font-size:0.65rem; line-height:1.1;">
                                                                <thead class="bg-light text-center fw-bold">
                                                                    <tr>
                                                                        <th style="width:65px;">Scale</th>
                                                                        <th>Description</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td class="text-center fw-bold">3.60 – 4.00</td>
                                                                        <td class="text-center"><span class="badge bg-success w-100 p-1" style="font-size:0.6rem;">Outstanding</span></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="text-center fw-bold">2.60 – 3.59</td>
                                                                        <td class="text-center"><span class="badge bg-primary w-100 p-1" style="font-size:0.6rem;">Exceeds Expectations</span></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="text-center fw-bold">2.00 – 2.59</td>
                                                                        <td class="text-center"><span class="badge bg-info w-100 p-1" style="font-size:0.6rem;">Meets Expectations</span></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="text-center fw-bold">1.00 – 1.99</td>
                                                                        <td class="text-center"><span class="badge bg-danger w-100 p-1" style="font-size:0.6rem;">Needs Improvement</span></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="text-center mb-4">
                                                <span class="badge <?php echo getPerformanceBadgeClass($row['performance_level']); ?> px-4 py-2" style="font-size:1rem;"><?php echo e($row['performance_level'] ?? 'N/A'); ?></span>
                                            </div>

                                            <!-- Section I: KRA -->
                                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4 text-success"><i class="fas fa-bullseye me-2"></i>I. Performance Result: KRA</h6>
                                            <div class="table-responsive mb-4">
                                                <table class="table table-sm table-bordered align-middle">
                                                    <thead class="bg-light small fw-bold text-center">
                                                        <tr>
                                                            <th>Description</th>
                                                            <th style="width:80px;">Weight</th>
                                                            <th style="width:80px;">Rating</th>
                                                            <th style="width:80px;">Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $kra_q = $conn->query("SELECT es.*, ec.criterion_name, ec.description, ec.weight FROM evaluation_scores es JOIN evaluation_criteria ec ON es.criterion_id = ec.criterion_id WHERE es.evaluation_id = {$row['evaluation_id']} AND ec.section = 'KRA' ORDER BY ec.sort_order");
                                                        while ($k = $kra_q->fetch_assoc()):
                                                        ?>
                                                            <tr>
                                                                <td><strong><?php echo e($k['criterion_name']); ?></strong><?php if($k['description']): ?><br><small class="text-muted"><?php echo e($k['description']); ?></small><?php endif; ?></td>
                                                                <td class="text-center"><?php echo $k['weight']; ?>%</td>
                                                                <td class="text-center"><?php echo $k['score_value']; ?></td>
                                                                <td class="text-center fw-bold text-primary"><?php echo $k['weighted_score']; ?></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                        <tr class="bg-light fw-bold">
                                                            <td class="text-end">SUB TOTAL</td>
                                                            <td class="text-center">100%</td>
                                                            <td></td>
                                                            <td class="text-center text-primary"><?php echo $row['kra_subtotal']; ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Section II: Behavior -->
                                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4 text-primary"><i class="fas fa-heart me-2"></i>II. Behavior and Values</h6>
                                            <div class="table-responsive mb-4">
                                                <table class="table table-sm table-bordered align-middle">
                                                    <thead class="bg-light small fw-bold text-center">
                                                        <tr>
                                                            <th>Behavior Item / KPI</th>
                                                            <th style="width:100px;">Rating (1-4)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $beh_q = $conn->query("SELECT es.*, ec.criterion_name, ec.kpi_description FROM evaluation_scores es JOIN evaluation_criteria ec ON es.criterion_id = ec.criterion_id WHERE es.evaluation_id = {$row['evaluation_id']} AND ec.section = 'Behavior' ORDER BY ec.sort_order");
                                                        while ($b = $beh_q->fetch_assoc()):
                                                        ?>
                                                            <tr>
                                                                <td><strong><?php echo e($b['criterion_name']); ?></strong><br><small class="text-muted"><?php echo e($b['kpi_description']); ?></small></td>
                                                                <td class="text-center fw-bold text-primary"><?php echo $b['score_value']; ?></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                        <tr class="bg-light fw-bold">
                                                            <td class="text-end">AVERAGE</td>
                                                            <td class="text-center text-primary"><?php echo $row['behavior_average']; ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Developmental Plan -->
                                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4 text-warning"><i class="fas fa-seedling me-2"></i>Developmental Plan</h6>
                                            <div class="table-responsive mb-4">
                                                <table class="table table-sm table-bordered align-middle">
                                                    <thead class="bg-light small fw-bold text-center">
                                                        <tr>
                                                            <th>Area of Improvement</th>
                                                            <th>Support Needed</th>
                                                            <th>Time Frame</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $dev_q = $conn->query("SELECT * FROM evaluation_dev_plans WHERE evaluation_id = {$row['evaluation_id']} ORDER BY sort_order");
                                                        if ($dev_q->num_rows > 0):
                                                            while ($dp = $dev_q->fetch_assoc()): ?>
                                                            <tr>
                                                                <td><?php echo e($dp['improvement_area']); ?></td>
                                                                <td><?php echo e($dp['support_needed']); ?></td>
                                                                <td class="text-center"><?php echo e($dp['time_frame']); ?></td>
                                                            </tr>
                                                        <?php endwhile; else: ?>
                                                            <tr><td colspan="3" class="text-center text-muted small">No developmental plan recorded.</td></tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Section IV: Career Growth -->
                                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4"><i class="fas fa-chart-line me-2 text-info"></i>IV. Career Growth and Development</h6>
                                            <div class="table-responsive mb-4">
                                                <table class="table table-sm table-bordered mb-0">
                                                    <thead class="bg-light small">
                                                        <tr>
                                                            <th>Current Position</th>
                                                            <th style="width:100px;">Months</th>
                                                            <th>Desired Position</th>
                                                            <th style="width:120px;">Target Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="small">
                                                        <tr>
                                                            <td><?php echo e($row['current_position'] ?? 'N/A'); ?></td>
                                                            <td class="text-center"><?php echo e($row['months_in_position'] ?? '0'); ?></td>
                                                            <td class="fw-bold text-primary"><?php echo e($row['desired_position'] ?? 'N/A'); ?></td>
                                                            <td><?php echo $row['target_date'] ? formatDate($row['target_date']) : 'N/A'; ?></td>
                                                        </tr>
                                                        <?php if(!empty($row['career_growth_details'])): ?>
                                                        <tr>
                                                            <td colspan="4" class="bg-light"><strong>Notes:</strong> <?php echo e($row['career_growth_details']); ?></td>
                                                        </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Section V: Comments -->
                                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4"><i class="fas fa-comments me-2 text-secondary"></i>Comments & Remarks</h6>
                                            
                                            <?php if ($row['staff_comments']): ?>
                                                <div class="mb-3">
                                                    <label class="small fw-bold text-uppercase d-block mb-1">Employee's Comments:</label>
                                                    <div class="bg-light p-2 rounded small border"><?php echo nl2br(e($row['staff_comments'])); ?></div>
                                                </div>
                                            <?php endif; ?>

                                            <form method="POST" action="">
                                                <input type="hidden" name="evaluation_id" value="<?php echo $row['evaluation_id']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label"><strong>Supervisor Validation Comments</strong></label>
                                                    <textarea class="form-control" name="supervisor_comments" rows="3" placeholder="Enter your validation notes..."></textarea>
                                                </div>
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <button type="submit" name="action" value="return" class="btn btn-warning">
                                                        <i class="fas fa-undo me-1"></i>Return for Revision
                                                    </button>
                                                    <button type="submit" name="action" value="endorse" class="btn btn-success">
                                                        <i class="fas fa-check-double me-1"></i>Validate & Forward to Manager
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
// Separate loop for modals to keep table structure valid
foreach ($all_pending as $row): 
?>
    <!-- Review Modal for <?php echo $row['evaluation_id']; ?> -->
    <div class="modal fade" id="reviewModal<?php echo $row['evaluation_id']; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Review Evaluation - <?php echo e($row['employee_name']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pb-0">
                    <!-- Status Stepper -->
                    <div class="status-stepper d-flex justify-content-between mb-4 position-relative">
                        <div class="stepper-line"></div>
                        <?php
                        $steps = [
                            ['label' => 'Submitted', 'active' => true, 'icon' => 'fa-paper-plane'],
                            ['label' => 'Supervisor', 'active' => true, 'icon' => 'fa-user-tie', 'current' => true],
                            ['label' => 'Manager', 'active' => false, 'icon' => 'fa-user-shield'],
                            ['label' => 'Finalized', 'active' => false, 'icon' => 'fa-check-circle']
                        ];
                        
                        foreach ($steps as $st):
                        ?>
                            <div class="step-item text-center <?php echo $st['active'] ? 'active' : ''; ?> <?php echo isset($st['current']) ? 'border-primary' : ''; ?>" style="z-index: 1;">
                                <div class="step-icon mb-1 <?php echo isset($st['current']) ? 'shadow-sm border-primary' : ''; ?>">
                                    <i class="fas <?php echo $st['icon']; ?>"></i>
                                </div>
                                <div class="step-label x-small fw-bold <?php echo isset($st['current']) ? 'text-primary' : ''; ?>"><?php echo $st['label']; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<style>
    .status-stepper .stepper-line {
        position: absolute;
        top: 15px;
        left: 10%;
        right: 10%;
        height: 2px;
        background: #e9ecef;
        z-index: 0;
    }
    .step-item .step-icon {
        width: 32px;
        height: 32px;
        line-height: 32px;
        background: #fff;
        border: 2px solid #e9ecef;
        border-radius: 50%;
        margin: 0 auto;
        color: #adb5bd;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .step-item.active .step-icon {
        background: var(--primary-blue);
        border-color: var(--primary-blue);
        color: #fff;
    }
    .step-item.active .step-label {
        color: var(--primary-blue);
    }
    .x-small { font-size: 0.65rem !important; }
</style>

<script>
document.getElementById('customSearchPending')?.addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#pendingTable tbody tr:not(.no-results-row)');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
