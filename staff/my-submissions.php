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

// Prepare counters
$total_c = 0;
$pending_c = 0;
$approved_c = 0;
$returned_c = 0;

$all_subs = [];
while ($row = $submissions->fetch_assoc()) {
    $all_subs[] = $row;
    $total_c++;
    if ($row['status'] === 'Approved') $approved_c++;
    elseif ($row['status'] === 'Returned') $returned_c++;
    else $pending_c++;
}
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="content-card text-center p-3 h-100 border-start border-4 border-primary">
            <div class="display-6 fw-bold text-primary"><?php echo $total_c; ?></div>
            <div class="text-muted small fw-bold">Total Submissions</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="content-card text-center p-3 h-100 border-start border-4 border-warning">
            <div class="display-6 fw-bold text-warning"><?php echo $pending_c; ?></div>
            <div class="text-muted small fw-bold">Pending Review</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="content-card text-center p-3 h-100 border-start border-4 border-success">
            <div class="display-6 fw-bold text-success"><?php echo $approved_c; ?></div>
            <div class="text-muted small fw-bold">Approved</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="content-card text-center p-3 h-100 border-start border-4 border-danger">
            <div class="display-6 fw-bold text-danger"><?php echo $returned_c; ?></div>
            <div class="text-muted small fw-bold">Returned</div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 me-3"><i class="fas fa-paper-plane me-2 text-primary"></i>My Submissions</h5>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-primary active" onclick="filterByStatus('All', this)">All</button>
                <button type="button" class="btn btn-outline-warning" onclick="filterByStatus('Pending', this)">Pending</button>
                <button type="button" class="btn btn-outline-success" onclick="filterByStatus('Approved', this)">Approved</button>
                <button type="button" class="btn btn-outline-danger" onclick="filterByStatus('Returned', this)">Returned</button>
            </div>
        </div>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="form-control form-control-sm" id="searchSubs" placeholder="Search employee..." onkeyup="filterTable('searchSubs', 'subsTable')">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="subsTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">Employee & Position</th>
                        <th>Template</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Progress</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($all_subs)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                            No submissions found.
                        </td></tr>
                    <?php else: 
                        foreach ($all_subs as $sub): 
                            $status = $sub['status'];
                            $progWidth = '25%';
                            $progClass = 'bg-warning';
                            if ($status === 'Approved') { $progWidth = '100%'; $progClass = 'bg-success'; }
                            elseif ($status === 'Pending Manager') { $progWidth = '75%'; }
                            elseif ($status === 'Pending Supervisor') { $progWidth = '50%'; }
                            elseif ($status === 'Returned') { $progWidth = '100%'; $progClass = 'bg-danger'; }
                    ?>
                            <tr data-status="<?php echo ($status === 'Approved' || $status === 'Returned') ? $status : 'Pending'; ?>">
                                <td class="ps-3"><strong><?php echo e($sub['employee_name']); ?></strong><br><small class="text-muted"><?php echo e($sub['current_position'] ?? 'N/A'); ?></small></td>
                                <td><small><?php echo e($sub['template_name']); ?></small></td>
                                <td><small><?php echo formatDate($sub['submitted_date']); ?></small></td>
                                <td><span class="badge <?php echo getStatusBadgeClass($sub['status']); ?>"><?php echo e($sub['status']); ?></span></td>
                                <td>
                                    <strong><?php echo $sub['total_score']; ?>%</strong>
                                    <div class="text-muted" style="font-size:0.65rem;"><?php echo e($sub['performance_level']); ?></div>
                                </td>
                                <td>
                                    <div class="progress" style="height: 6px; width: 80px;" title="<?php echo $progWidth; ?> complete">
                                        <div class="progress-bar <?php echo $progClass; ?>" role="progressbar" style="width: <?php echo $progWidth; ?>;"></div>
                                    </div>
                                    <small class="text-muted" style="font-size:0.65rem;"><?php echo $progWidth; ?></small>
                                </td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-sm btn-action btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detailModal<?php echo $sub['evaluation_id']; ?>">
                                        <i class="fas fa-eye me-1"></i>View
                                    </button>
                                    <?php if ($status === 'Returned'): ?>
                                        <a href="<?php echo BASE_URL; ?>/staff/submit-evaluation.php?edit=<?php echo $sub['evaluation_id']; ?>" class="btn btn-sm btn-action btn-warning">
                                            <i class="fas fa-edit me-1"></i>Fix
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
// Separate loop for modals to keep table structure valid
foreach ($all_subs as $sub): 
    $status = $sub['status'];
?>
    <!-- Detail Modal for <?php echo $sub['evaluation_id']; ?> -->
    <div class="modal fade" id="detailModal<?php echo $sub['evaluation_id']; ?>" tabindex="-1">
                               <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Evaluation Details - <?php echo e($sub['employee_name']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body pb-0">
                                            <!-- Status Stepper -->
                                            <div class="status-stepper d-flex justify-content-between mb-4 position-relative">
                                                <div class="stepper-line"></div>
                                                <?php
                                                $steps = [
                                                    ['label' => 'Submitted', 'active' => true, 'icon' => 'fa-paper-plane'],
                                                    ['label' => 'Supervisor', 'active' => ($status !== 'Pending Supervisor'), 'icon' => 'fa-user-tie'],
                                                    ['label' => 'Manager', 'active' => ($status === 'Approved' || $status === 'Returned'), 'icon' => 'fa-user-shield'],
                                                    ['label' => 'Finalized', 'active' => ($status === 'Approved'), 'icon' => 'fa-check-circle']
                                                ];
                                                if ($status === 'Returned') $steps[3] = ['label' => 'Returned', 'active' => true, 'icon' => 'fa-undo', 'class' => 'text-danger'];
                                                
                                                foreach ($steps as $st):
                                                ?>
                                                    <div class="step-item text-center <?php echo $st['active'] ? 'active' : ''; ?> <?php echo $st['class'] ?? ''; ?>" style="z-index: 1;">
                                                        <div class="step-icon mb-1">
                                                            <i class="fas <?php echo $st['icon']; ?>"></i>
                                                        </div>
                                                        <div class="step-label x-small fw-bold"><?php echo $st['label']; ?></div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>

                                            <div class="alert <?php echo $status === 'Approved' ? 'alert-success' : ($status === 'Returned' ? 'alert-danger' : 'alert-info'); ?> py-2 small d-flex align-items-center">
                                                <i class="fas <?php echo $status === 'Approved' ? 'fa-check-circle' : ($status === 'Returned' ? 'fa-exclamation-circle' : 'fa-info-circle'); ?> me-2"></i>
                                                <span>Current Status: <strong><?php echo $status; ?></strong></span>
                                            </div>
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

                                            <!-- Action Buttons -->
                                            <div class="d-flex justify-content-end mb-3 gap-2 d-print-none text-end">
                                                <a href="../manager/print-evaluation.php?id=<?php echo $sub['evaluation_id']; ?>" target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-print me-1"></i>Print Official Form (HRD-013)
                                                </a>
                                            </div>

                                            <!-- Section I: KRA -->
                                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4 text-success small"><i class="fas fa-bullseye me-2"></i>I. Performance Result: Strategic Programs and Job Requirements</h6>
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
                                                        $kra_q = $conn->query("SELECT es.*, ec.criterion_name, ec.description, ec.weight FROM evaluation_scores es JOIN evaluation_criteria ec ON es.criterion_id = ec.criterion_id WHERE es.evaluation_id = {$sub['evaluation_id']} AND ec.section = 'KRA' ORDER BY ec.sort_order");
                                                        while ($k = $kra_q->fetch_assoc()):
                                                        ?>
                                                            <tr class="small">
                                                                <td><strong><?php echo e($k['criterion_name']); ?></strong><?php if($k['description']): ?><br><span class="text-muted"><?php echo e($k['description']); ?></span><?php endif; ?></td>
                                                                <td class="text-center"><?php echo $k['weight']; ?>%</td>
                                                                <td class="text-center"><?php echo $k['score_value']; ?></td>
                                                                <td class="text-center fw-bold text-primary"><?php echo $k['weighted_score']; ?></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                        <tr class="bg-light fw-bold small">
                                                            <td class="text-end">SUB TOTAL</td>
                                                            <td class="text-center">100%</td>
                                                            <td></td>
                                                            <td class="text-center text-primary"><?php echo $sub['kra_subtotal']; ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Section II: Behavior -->
                                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4 text-primary small"><i class="fas fa-heart me-2"></i>II. Behavior and Values</h6>
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
                                                        $beh_q = $conn->query("SELECT es.*, ec.criterion_name, ec.kpi_description FROM evaluation_scores es JOIN evaluation_criteria ec ON es.criterion_id = ec.criterion_id WHERE es.evaluation_id = {$sub['evaluation_id']} AND ec.section = 'Behavior' ORDER BY ec.sort_order");
                                                        while ($b = $beh_q->fetch_assoc()):
                                                        ?>
                                                            <tr class="small">
                                                                <td><strong><?php echo e($b['criterion_name']); ?></strong><br><span class="text-muted"><?php echo e($b['kpi_description']); ?></span></td>
                                                                <td class="text-center fw-bold text-primary"><?php echo $b['score_value']; ?></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                        <tr class="bg-light fw-bold small">
                                                            <td class="text-end">AVERAGE</td>
                                                            <td class="text-center text-primary"><?php echo $sub['behavior_average']; ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Section III: Performance Evaluation Summary -->
                                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4 text-dark small"><i class="fas fa-calculator me-2"></i>III. Performance Evaluation Summary</h6>
                                            <div class="row mb-4">
                                                <div class="col-lg-8">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered text-center align-middle mb-0">
                                                            <thead class="bg-light small fw-bold">
                                                                <tr>
                                                                    <th class="text-start">Summary</th>
                                                                    <th style="width:80px;">Weight</th>
                                                                    <th style="width:80px;">Rating</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="small">
                                                                <tr>
                                                                    <td class="text-start">I. Key Result Areas</td>
                                                                    <td>80%</td>
                                                                    <td class="fw-bold"><?php echo $sub['kra_subtotal']; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-start">II. Behavior and Values</td>
                                                                    <td>20%</td>
                                                                    <td class="fw-bold"><?php echo $sub['behavior_average']; ?></td>
                                                                </tr>
                                                                <tr class="table-active fw-bold">
                                                                    <td class="text-end">TOTAL SCORE</td>
                                                                    <td>100%</td>
                                                                    <td class="text-primary"><?php echo $sub['total_score']; ?></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 text-center d-flex flex-column justify-content-center">
                                                    <div class="p-2 border rounded bg-white">
                                                        <div class="small text-uppercase text-muted mb-1">Performance Level</div>
                                                        <span class="badge <?php echo getPerformanceBadgeClass($sub['performance_level']); ?> px-3 py-2" style="font-size:0.9rem;"><?php echo e($sub['performance_level'] ?? 'N/A'); ?></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section IV: Career Growth -->
                                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4 text-info small"><i class="fas fa-chart-line me-2"></i>IV. Career Growth and Development</h6>
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
                                                            <td><?php echo e($sub['current_position'] ?? 'N/A'); ?></td>
                                                            <td class="text-center"><?php echo e($sub['months_in_position'] ?? '0'); ?></td>
                                                            <td class="fw-bold text-primary"><?php echo e($sub['desired_position'] ?? 'N/A'); ?></td>
                                                            <td><?php echo $sub['target_date'] ? formatDate($sub['target_date']) : 'N/A'; ?></td>
                                                        </tr>
                                                        <?php if(!empty($sub['career_growth_details'])): ?>
                                                        <tr>
                                                            <td colspan="4" class="bg-light italic"><strong>Notes:</strong> <?php echo e($sub['career_growth_details']); ?></td>
                                                        </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Section V: Developmental Plan -->
                                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4 text-warning small"><i class="fas fa-seedling me-2"></i>V. Developmental Plan</h6>
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
                                                        $dev_q = $conn->query("SELECT * FROM evaluation_dev_plans WHERE evaluation_id = {$sub['evaluation_id']} ORDER BY sort_order");
                                                        if ($dev_q->num_rows > 0):
                                                            while ($dp = $dev_q->fetch_assoc()): ?>
                                                            <tr>
                                                                <td><?php echo e($dp['improvement_area']); ?></td>
                                                                <td><?php echo e($dp['support_needed']); ?></td>
                                                                <td class="text-center"><?php echo e($dp['time_frame']); ?></td>
                                                            </tr>
                                                        <?php endwhile; else: ?>
                                                            <tr><td colspan="3" class="text-center text-muted small py-3">No developmental plan recorded.</td></tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Section VI: Comments -->
                                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4 text-secondary small"><i class="fas fa-comments me-2"></i>VI. Comments & Signatures</h6>
                                            <div class="row">
                                                <?php if($sub['staff_comments']): ?>
                                                <div class="col-sm-4 mb-3">
                                                    <strong class="small text-uppercase text-muted d-block">Me:</strong>
                                                    <div class="bg-light p-2 rounded small border mt-1" style="min-height:60px;"><?php echo nl2br(e($sub['staff_comments'])); ?></div>
                                                </div>
                                                <?php endif; ?>
                                                <?php if($sub['supervisor_comments']): ?>
                                                <div class="col-sm-4 mb-3">
                                                    <strong class="small text-uppercase text-muted d-block">Supervisor:</strong>
                                                    <div class="bg-light p-2 rounded small border border-primary mt-1" style="min-height:60px;"><?php echo nl2br(e($sub['supervisor_comments'])); ?></div>
                                                </div>
                                                <?php endif; ?>
                                                <?php if($sub['manager_comments']): ?>
                                                <div class="col-sm-4 mb-3">
                                                    <strong class="small text-uppercase text-muted d-block">Manager:</strong>
                                                    <div class="bg-light p-2 rounded small border mt-1" style="min-height:60px;"><?php echo nl2br(e($sub['manager_comments'])); ?></div>
                                                </div>
                                                <?php endif; ?>
                                            </div>

                                            <?php if ($sub['status'] === 'Returned'): ?>
                                                <div class="mt-3 text-center">
                                                    <a href="<?php echo BASE_URL; ?>/staff/submit-evaluation.php?edit=<?php echo $sub['evaluation_id']; ?>" class="btn btn-warning">
                                                        <i class="fas fa-edit me-1"></i>Edit & Re-submit
                                                    </a>
                                                </div>
                                            <?php endif; ?>
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
    }
    .step-item.active .step-icon {
        background: var(--primary-blue);
        border-color: var(--primary-blue);
        color: #fff;
    }
    .step-item.active .step-label {
        color: var(--primary-blue);
    }
    .x-small { font-size: 0.65rem; }
</style>

<script>
function filterByStatus(status, btn) {
    // Update active button
    const buttons = btn.parentElement.querySelectorAll('.btn');
    buttons.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Filter table rows
    const rows = document.querySelectorAll('#subsTable tbody tr');
    rows.forEach(row => {
        if (status === 'All') {
            row.style.display = '';
        } else {
            const rowStatus = row.getAttribute('data-status');
            row.style.display = (rowStatus === status) ? '' : 'none';
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
