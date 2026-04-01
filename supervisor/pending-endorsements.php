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
?>

<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-clipboard-check me-2"></i>Evaluations Pending Endorsement</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Position</th>
                        <th>Submitted By</th>
                        <th>Date</th>
                        <th>Score</th>
                        <th>Level</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pending->num_rows === 0): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No pending endorsements.</td></tr>
                    <?php else: ?>
                        <?php while ($row = $pending->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo e($row['employee_name']); ?></strong></td>
                                <td><?php echo e($row['job_title']); ?></td>
                                <td><?php echo e($row['submitted_by_name']); ?></td>
                                <td><small><?php echo formatDate($row['submitted_date']); ?></small></td>
                                <td><strong><?php echo $row['total_score']; ?>%</strong></td>
                                <td><span class="badge <?php echo getPerformanceBadgeClass($row['performance_level']); ?>"><?php echo e($row['performance_level']); ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal<?php echo $row['evaluation_id']; ?>">
                                        <i class="fas fa-eye me-1"></i>Review
                                    </button>
                                </td>
                            </tr>

                            <!-- Review Modal -->
                            <div class="modal fade" id="reviewModal<?php echo $row['evaluation_id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Review Evaluation - <?php echo e($row['employee_name']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <p><strong>Employee:</strong> <?php echo e($row['employee_name']); ?></p>
                                                    <p><strong>Position:</strong> <?php echo e($row['job_title']); ?></p>
                                                    <p><strong>Template:</strong> <?php echo e($row['template_name']); ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Submitted By:</strong> <?php echo e($row['submitted_by_name']); ?></p>
                                                    <p><strong>Date:</strong> <?php echo formatDate($row['submitted_date']); ?></p>
                                                </div>
                                            </div>

                                            <div class="score-display mb-3">
                                                <div class="score-value"><?php echo $row['total_score']; ?>%</div>
                                                <span class="badge <?php echo getPerformanceBadgeClass($row['performance_level']); ?>" style="font-size:1rem;"><?php echo e($row['performance_level']); ?></span>
                                            </div>

                                            <div class="mb-4">
                                                <h6><i class="fas fa-list-ol me-2"></i>Detailed Scores</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th>Criterion</th>
                                                                <th class="text-center">Weight</th>
                                                                <th class="text-center">Score</th>
                                                                <th class="text-center">Weighted</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $scores_q = $conn->query("SELECT es.*, ec.criterion_name, ec.scoring_method 
                                                                                    FROM evaluation_scores es 
                                                                                    JOIN evaluation_criteria ec ON es.criterion_id = ec.criterion_id 
                                                                                    WHERE es.evaluation_id = {$row['evaluation_id']} 
                                                                                    ORDER BY ec.sort_order");
                                                            while ($score = $scores_q->fetch_assoc()):
                                                                $max_score = 5;
                                                                if ($score['scoring_method'] === 'Scale_1_10') $max_score = 10;
                                                                elseif ($score['scoring_method'] === 'Percentage') $max_score = 100;
                                                            ?>
                                                                <tr>
                                                                    <td><?php echo e($score['criterion_name']); ?></td>
                                                                    <td class="text-center"><?php echo number_format($score['weighted_score'] / ($score['score_value'] / $max_score), 0); ?>%</td>
                                                                    <td class="text-center"><?php echo $score['score_value']; ?> / <?php echo $max_score; ?></td>
                                                                    <td class="text-center text-primary"><strong><?php echo $score['weighted_score']; ?>%</strong></td>
                                                                </tr>
                                                            <?php endwhile; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <?php if ($row['staff_comments']): ?>
                                                <div class="mb-3">
                                                    <strong>Staff Comments:</strong>
                                                    <p class="bg-light p-2 rounded"><?php echo e($row['staff_comments']); ?></p>
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
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
