<?php
$page_title = 'My Submissions';
require_once '../includes/session-check.php';
checkRole(['HR Staff']);
require_once '../includes/header.php';

$uid = $_SESSION['user_id'];

// Fetch submissions (excluding drafts)
$submissions = $conn->query("SELECT ev.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name, et.template_name,
    u2.full_name as endorsed_by_name, u3.full_name as approved_by_name
    FROM evaluations ev
    LEFT JOIN employees e ON ev.employee_id = e.employee_id
    LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id
    LEFT JOIN users u2 ON ev.endorsed_by = u2.user_id
    LEFT JOIN users u3 ON ev.approved_by = u3.user_id
    WHERE ev.submitted_by = $uid AND ev.status != 'Draft'
    ORDER BY ev.submitted_date DESC");
?>

<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-paper-plane me-2"></i>My Submissions</h5>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="form-control form-control-sm" id="searchSubs" placeholder="Search..." onkeyup="filterTable('searchSubs', 'subsTable')">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover" id="subsTable">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Template</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Level</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($submissions->num_rows === 0): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No submissions yet.</td></tr>
                    <?php else: ?>
                        <?php while ($sub = $submissions->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo e($sub['employee_name']); ?></strong></td>
                                <td><small><?php echo e($sub['template_name']); ?></small></td>
                                <td><small><?php echo formatDate($sub['submitted_date']); ?></small></td>
                                <td><span class="badge <?php echo getStatusBadgeClass($sub['status']); ?>"><?php echo e($sub['status']); ?></span></td>
                                <td><strong><?php echo $sub['total_score']; ?>%</strong></td>
                                <td><span class="badge <?php echo getPerformanceBadgeClass($sub['performance_level']); ?>"><?php echo e($sub['performance_level']); ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detailModal<?php echo $sub['evaluation_id']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($sub['status'] === 'Returned'): ?>
                                        <a href="<?php echo BASE_URL; ?>/staff/submit-evaluation.php?edit=<?php echo $sub['evaluation_id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Detail Modal -->
                            <div class="modal fade" id="detailModal<?php echo $sub['evaluation_id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Evaluation Details - <?php echo e($sub['employee_name']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <p><strong>Employee:</strong> <?php echo e($sub['employee_name']); ?></p>
                                                    <p><strong>Template:</strong> <?php echo e($sub['template_name']); ?></p>
                                                    <p><strong>Period:</strong> <?php echo formatDate($sub['evaluation_period_start']); ?> - <?php echo formatDate($sub['evaluation_period_end']); ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Status:</strong> <span class="badge <?php echo getStatusBadgeClass($sub['status']); ?>"><?php echo e($sub['status']); ?></span></p>
                                                    <p><strong>Endorsed By:</strong> <?php echo e($sub['endorsed_by_name'] ?? 'Pending'); ?></p>
                                                    <p><strong>Approved By:</strong> <?php echo e($sub['approved_by_name'] ?? 'Pending'); ?></p>
                                                </div>
                                            </div>

                                            <div class="score-display mb-3">
                                                <div class="score-value"><?php echo $sub['total_score']; ?>%</div>
                                                <span class="badge <?php echo getPerformanceBadgeClass($sub['performance_level']); ?>" style="font-size:1rem;"><?php echo e($sub['performance_level']); ?></span>
                                            </div>

                                            <?php if ($sub['staff_comments']): ?>
                                                <div class="mb-2">
                                                    <strong>Staff Comments:</strong>
                                                    <p class="bg-light p-2 rounded"><?php echo e($sub['staff_comments']); ?></p>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($sub['supervisor_comments']): ?>
                                                <div class="mb-2">
                                                    <strong>Supervisor Comments:</strong>
                                                    <p class="bg-light p-2 rounded"><?php echo e($sub['supervisor_comments']); ?></p>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($sub['manager_comments']): ?>
                                                <div class="mb-2">
                                                    <strong>Manager Comments:</strong>
                                                    <p class="bg-light p-2 rounded"><?php echo e($sub['manager_comments']); ?></p>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($sub['status'] === 'Returned'): ?>
                                                <div class="mt-3">
                                                    <a href="<?php echo BASE_URL; ?>/staff/submit-evaluation.php?edit=<?php echo $sub['evaluation_id']; ?>" class="btn btn-warning">
                                                        <i class="fas fa-edit me-1"></i>Edit & Re-submit
                                                    </a>
                                                </div>
                                            <?php endif; ?>
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
