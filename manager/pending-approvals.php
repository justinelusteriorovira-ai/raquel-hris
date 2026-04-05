<?php
$page_title = 'Pending Approvals';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/functions.php';

// Handle approval/rejection (MUST be before header.php to allow redirect)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $eval_id = (int) $_POST['evaluation_id'];
    $action = $_POST['action'];
    $comments = trim($_POST['manager_comments'] ?? '');

    if ($action === 'approve') {
        // 1. Update status to Approved
        $stmt = $conn->prepare("UPDATE evaluations SET status = 'Approved', approved_by = ?, manager_comments = ? WHERE evaluation_id = ?");
        $stmt->bind_param("isi", $_SESSION['user_id'], $comments, $eval_id);
        $stmt->execute();
        $stmt->close();

        // 2. Fetch evaluation details for career movement and notifications
        $eval_info_q = $conn->prepare("SELECT ev.*, CONCAT(e.first_name, ' ', e.last_name) as emp_name, e.branch_id as emp_branch_id 
                                     FROM evaluations ev 
                                     LEFT JOIN employees e ON ev.employee_id = e.employee_id 
                                     WHERE ev.evaluation_id = ?");
        $eval_info_q->bind_param("i", $eval_id);
        $eval_info_q->execute();
        $eval_info = $eval_info_q->get_result()->fetch_assoc();
        $eval_info_q->close();

        // 3. Automatically create a Career Movement record if a Desired Position is set
        if (!empty($eval_info['desired_position'])) {
            $move_stmt = $conn->prepare("INSERT INTO career_movements 
                (employee_id, movement_type, previous_position, new_position, previous_branch_id, new_branch_id, effective_date, reason, approval_status, approved_by, logged_by, is_applied) 
                VALUES (?, 'Role Change', ?, ?, ?, ?, ?, ?, 'Approved', ?, ?, 0)");
            
            $reason = "Automatically generated from approved Performance Evaluation (ID: " . $eval_id . ")";
            $move_stmt->bind_param("issiissii", 
                $eval_info['employee_id'],
                $eval_info['current_position'],
                $eval_info['desired_position'],
                $eval_info['emp_branch_id'],
                $eval_info['emp_branch_id'], // Assuming same branch for role change goals
                $eval_info['target_date'],
                $reason,
                $_SESSION['user_id'],
                $eval_info['submitted_by']
            );
            $move_stmt->execute();
            $move_stmt->close();
        }

        // 4. Send Notifications
        if ($eval_info['submitted_by']) {
            createNotification($conn, $eval_info['submitted_by'], 'Evaluation Approved', "Your evaluation for {$eval_info['emp_name']} has been approved.", BASE_URL . '/staff/my-submissions.php');
        }
        if ($eval_info['endorsed_by']) {
            createNotification($conn, $eval_info['endorsed_by'], 'Evaluation Approved', "Evaluation for {$eval_info['emp_name']} has been approved.", BASE_URL . '/supervisor/pending-endorsements.php');
        }
        logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'Evaluation', $eval_id, "Approved evaluation for {$eval_info['emp_name']}");
        redirectWith(BASE_URL . '/manager/pending-approvals.php', 'success', 'Evaluation approved successfully.');

    } elseif ($action === 'reject') {
        if (empty($comments)) {
            redirectWith(BASE_URL . '/manager/pending-approvals.php', 'danger', 'Comments are required when rejecting an evaluation.');
        }
        $stmt = $conn->prepare("UPDATE evaluations SET status = 'Rejected', approved_by = ?, manager_comments = ? WHERE evaluation_id = ?");
        $stmt->bind_param("isi", $_SESSION['user_id'], $comments, $eval_id);
        $stmt->execute();
        $stmt->close();

        $eval_info = $conn->query("SELECT ev.submitted_by, CONCAT(e.first_name, ' ', e.last_name) as emp_name FROM evaluations ev LEFT JOIN employees e ON ev.employee_id = e.employee_id WHERE ev.evaluation_id = $eval_id")->fetch_assoc();
        if ($eval_info['submitted_by']) {
            createNotification($conn, $eval_info['submitted_by'], 'Evaluation Rejected', "Your evaluation for {$eval_info['emp_name']} has been rejected.", BASE_URL . '/staff/my-submissions.php');
        }
        logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'Evaluation', $eval_id, "Rejected evaluation for {$eval_info['emp_name']}");
        redirectWith(BASE_URL . '/manager/pending-approvals.php', 'warning', 'Evaluation rejected.');

    } elseif ($action === 'revision') {
        if (empty($comments)) {
            redirectWith(BASE_URL . '/manager/pending-approvals.php', 'danger', 'Comments are required when requesting revision.');
        }
        $stmt = $conn->prepare("UPDATE evaluations SET status = 'Returned', manager_comments = ? WHERE evaluation_id = ?");
        $stmt->bind_param("si", $comments, $eval_id);
        $stmt->execute();
        $stmt->close();

        $eval_info = $conn->query("SELECT ev.submitted_by, CONCAT(e.first_name, ' ', e.last_name) as emp_name FROM evaluations ev LEFT JOIN employees e ON ev.employee_id = e.employee_id WHERE ev.evaluation_id = $eval_id")->fetch_assoc();
        if ($eval_info['submitted_by']) {
            createNotification($conn, $eval_info['submitted_by'], 'Revision Requested', "Your evaluation for {$eval_info['emp_name']} needs revision.", BASE_URL . '/staff/my-submissions.php');
        }
        logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'Evaluation', $eval_id, "Requested revision for evaluation of {$eval_info['emp_name']}");
        redirectWith(BASE_URL . '/manager/pending-approvals.php', 'info', 'Revision requested.');
    }
}

require_once '../includes/header.php';

// Fetch counts for summary
$finalized_count_q = $conn->prepare("SELECT COUNT(*) as cnt FROM evaluations WHERE approved_by = ? AND status IN ('Approved', 'Rejected')");
$finalized_count_q->bind_param("i", $_SESSION['user_id']);
$finalized_count_q->execute();
$finalized_count = $finalized_count_q->get_result()->fetch_assoc()['cnt'];
$finalized_count_q->close();

// Fetch pending evaluations
$pending = $conn->query("SELECT ev.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.job_title,
    u.full_name as submitted_by_name, et.template_name
    FROM evaluations ev
    LEFT JOIN employees e ON ev.employee_id = e.employee_id
    LEFT JOIN users u ON ev.submitted_by = u.user_id
    LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id
    WHERE ev.status = 'Pending Manager'
    ORDER BY ev.submitted_date DESC");

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
            <div class="text-muted small fw-bold text-uppercase">Pending Your Approval</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="content-card text-center p-3 h-100 border-start border-4 border-success shadow-sm">
            <div class="display-6 fw-bold text-success"><?php echo $finalized_count; ?></div>
            <div class="text-muted small fw-bold text-uppercase">Total Finalized</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="content-card p-3 h-100 bg-light border-0 shadow-sm d-flex flex-column justify-content-center">
            <div class="small text-muted mb-1 italic"><i class="fas fa-info-circle me-1"></i>Quick Tip</div>
            <div class="x-small text-muted">Approved evaluations with growth aspirations automatically create career movement records for tracking.</div>
        </div>
    </div>
</div>

<div class="content-card border-0 shadow-sm">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <h5 class="mb-0"><i class="fas fa-check-double me-2 text-primary"></i>Pending Evaluations</h5>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="form-control form-control-sm" id="customSearchPending" placeholder="Search employee or template...">
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
                        <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-clipboard-list fa-3x mb-3 d-block opacity-25"></i>No pending evaluations to review.</td></tr>
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
                                        <div class="progress-bar bg-success" style="width: <?php echo min(100, (float)$row['total_score'] / 4 * 100); ?>%;"></div>
                                    </div>
                                </td>
                                <td><span class="badge <?php echo getPerformanceBadgeClass($row['performance_level']); ?> rounded-pill px-2" style="font-size:0.7rem;"><?php echo e($row['performance_level']); ?></span></td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#reviewModal<?php echo $row['evaluation_id']; ?>">
                                        <i class="fas fa-check-double me-1"></i>Review
                                    </button>
                                </td>
                            </tr>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Performance Rating Scale -->
                                            <div class="mb-4">
                                                <h6 class="fw-bold text-uppercase small text-muted border-bottom pb-2 mb-2"><i class="fas fa-info-circle me-2"></i>Performance Rating Scale</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered mb-0 small align-middle" style="font-size:0.75rem;">
                                                        <thead class="bg-light text-center fw-bold">
                                                            <tr>
                                                                <th style="width:90px;">Scale</th>
                                                                <th>Description</th>
                                                                <th>Definition</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td class="text-center fw-bold">3.60 – 4.00</td>
                                                                <td class="text-center"><span class="badge bg-success w-100">Outstanding</span></td>
                                                                <td class="small">Performance significantly exceeds standards and expectations</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-center fw-bold">2.60 – 3.59</td>
                                                                <td class="text-center"><span class="badge bg-primary w-100">Exceeds Expectations</span></td>
                                                                <td class="small">Performance exceeds standards and expectations</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-center fw-bold">2.00 – 2.59</td>
                                                                <td class="text-center"><span class="badge bg-info w-100">Meets Expectations</span></td>
                                                                <td class="small">Performance meets standards and expectations</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-center fw-bold">1.00 – 1.99</td>
                                                                <td class="text-center"><span class="badge bg-danger w-100">Needs Improvement</span></td>
                                                                <td class="small">Performance did not meet standards and expectations</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- Section I: KRA -->
                                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4 text-success small">
                                                <i class="fas fa-bullseye me-2"></i>I. Performance Result: Strategic Programs
                                                and Job Requirements</h6>
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
                                                            <tr class="small">
                                                                <td><strong><?php echo e($k['criterion_name']); ?></strong><?php if ($k['description']): ?><br><small
                                                                            class="text-muted"><?php echo e($k['description']); ?></small><?php endif; ?>
                                                                </td>
                                                                <td class="text-center"><?php echo $k['weight']; ?>%</td>
                                                                <td class="text-center"><?php echo $k['score_value']; ?></td>
                                                                <td class="text-center fw-bold text-primary">
                                                                    <?php echo $k['weighted_score']; ?></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                        <tr class="bg-light fw-bold">
                                                            <td class="text-end small">SUB TOTAL</td>
                                                            <td class="text-center small">100%</td>
                                                            <td></td>
                                                            <td class="text-center text-primary small">
                                                                <?php echo $row['kra_subtotal']; ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Section II: Behavior -->
                                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4 text-primary small">
                                                <i class="fas fa-heart me-2"></i>II. Behavior and Values</h6>
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
                                                            <tr class="small">
                                                                <td><strong><?php echo e($b['criterion_name']); ?></strong><br><small
                                                                        class="text-muted"><?php echo e($b['kpi_description']); ?></small>
                                                                </td>
                                                                <td class="text-center fw-bold text-primary">
                                                                    <?php echo $b['score_value']; ?></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                        <tr class="bg-light fw-bold small">
                                                            <td class="text-end">AVERAGE</td>
                                                            <td class="text-center text-primary">
                                                                <?php echo $row['behavior_average']; ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Section III: Performance Evaluation Summary -->
                                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4 text-dark small"><i
                                                    class="fas fa-calculator me-2"></i>III. Performance Evaluation Summary</h6>
                                            <div class="row mb-4">
                                                <div class="col-lg-8">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered text-center align-middle mb-0">
                                                            <thead class="bg-light small fw-bold">
                                                                <tr>
                                                                    <th class="text-start">Summary</th>
                                                                    <th style="width:80px;">Weight</th>
                                                                    <th style="width:100px;">Rating</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="small">
                                                                <tr>
                                                                    <td class="text-start">I. Key Result Areas</td>
                                                                    <td>80%</td>
                                                                    <td class="fw-bold">
                                                                        <?php echo $row['kra_subtotal'] ?? '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-start">II. Behavior and Values</td>
                                                                    <td>20%</td>
                                                                    <td class="fw-bold">
                                                                        <?php echo $row['behavior_average'] ?? '-'; ?></td>
                                                                </tr>
                                                                <tr class="table-active fw-bold">
                                                                    <td class="text-end">TOTAL SCORE</td>
                                                                    <td>100%</td>
                                                                    <td class="text-primary"><?php echo $row['total_score']; ?>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 text-center d-flex flex-column justify-content-center">
                                                    <div class="p-2 border rounded bg-white shadow-sm">
                                                        <div class="small text-uppercase text-muted mb-1">Performance Level
                                                        </div>
                                                        <span
                                                            class="badge <?php echo getPerformanceBadgeClass($row['performance_level']); ?> px-3 py-2"
                                                            style="font-size:0.9rem;"><?php echo e($row['performance_level'] ?? 'N/A'); ?></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section IV: Career Growth -->
                                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4 text-info small"><i
                                                    class="fas fa-chart-line me-2"></i>IV. Career Growth and Development</h6>
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
                                                            <td class="text-center">
                                                                <?php echo e($row['months_in_position'] ?? '0'); ?></td>
                                                            <td class="fw-bold text-primary">
                                                                <?php echo e($row['desired_position'] ?? 'N/A'); ?></td>
                                                            <td><?php echo $row['target_date'] ? formatDate($row['target_date']) : 'N/A'; ?>
                                                            </td>
                                                        </tr>
                                                        <?php if (!empty($row['career_growth_details'])): ?>
                                                            <tr>
                                                                <td colspan="4" class="bg-light italic"><strong>Admin/Manager
                                                                        Notes:</strong>
                                                                    <?php echo e($row['career_growth_details']); ?></td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Section V: Developmental Plan -->
                                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4 text-warning small">
                                                <i class="fas fa-seedling me-2"></i>V. Developmental Plan</h6>
                                            <div class="table-responsive mb-4">
                                                <table class="table table-sm table-bordered align-middle">
                                                    <thead class="bg-light small fw-bold text-center">
                                                        <tr>
                                                            <th>Area of Improvement</th>
                                                            <th>Support Needed</th>
                                                            <th>Time Frame</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="small">
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
                                                            <tr>
                                                                <td colspan="3" class="text-center text-muted small py-3">No
                                                                    developmental plan recorded.</td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Section VI: Comments -->
                                            <h6
                                                class="fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4 text-secondary small">
                                                <i class="fas fa-comments me-2"></i>VI. Comments & Remarks</h6>

                                            <?php if ($row['staff_comments']): ?>
                                                <div class="mb-3">
                                                    <label class="small fw-bold text-uppercase d-block mb-1">Employee's
                                                        Comments:</label>
                                                    <div class="bg-light p-2 rounded small border">
                                                        <?php echo nl2br(e($row['staff_comments'])); ?></div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($row['supervisor_comments']): ?>
                                                <div class="mb-3">
                                                    <label class="small fw-bold text-uppercase d-block mb-1">Supervisor's
                                                        Remarks:</label>
                                                    <div class="bg-light p-2 rounded small border border-primary">
                                                        <?php echo nl2br(e($row['supervisor_comments'])); ?></div>
                                                </div>
                                            <?php endif; ?>

                                            <form method="POST" action="">
                                                <input type="hidden" name="evaluation_id"
                                                    value="<?php echo $row['evaluation_id']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label"><strong>Manager Comments</strong></label>
                                                    <textarea class="form-control" name="manager_comments" rows="3"
                                                        placeholder="Enter your comments..."></textarea>
                                                </div>
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <button type="submit" name="action" value="revision"
                                                        class="btn btn-warning">
                                                        <i class="fas fa-undo me-1"></i>Request Revision
                                                    </button>
                                                    <button type="submit" name="action" value="reject" class="btn btn-danger">
                                                        <i class="fas fa-times me-1"></i>Reject
                                                    </button>
                                                    <button type="submit" name="action" value="approve" class="btn btn-success">
                                                        <i class="fas fa-check me-1"></i>Approve
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
                            ['label' => 'Supervisor', 'active' => true, 'icon' => 'fa-user-tie'],
                            ['label' => 'Manager', 'active' => true, 'icon' => 'fa-user-shield', 'current' => true],
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