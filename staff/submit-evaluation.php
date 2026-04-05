<?php
$page_title = 'Submit Evaluation';
require_once '../includes/session-check.php';
checkRole(['HR Staff']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../includes/functions.php';

    $employee_id = (int)$_POST['employee_id'];
    $template_id = (int)$_POST['template_id'];
    $evaluation_type = $_POST['evaluation_type'] ?? 'Annual';
    $period_start = $_POST['period_start'] ?? null;
    $period_end = $_POST['period_end'] ?? null;
    $staff_comments = trim($_POST['staff_comments'] ?? '');
    $action = $_POST['submit_action'] ?? 'draft';
    $kra_scores = $_POST['kra_scores'] ?? [];
    $beh_scores = $_POST['beh_scores'] ?? [];
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    // Career growth
    $current_position = trim($_POST['current_position'] ?? '');
    $months_in_position = (int)($_POST['months_in_position'] ?? 0);
    $desired_position = trim($_POST['desired_position'] ?? '');
    $target_date = !empty($_POST['target_date']) ? $_POST['target_date'] : null;
    $career_growth_details = trim($_POST['career_growth_details'] ?? '');

    // Dev plans
    $dev_areas = $_POST['dev_area'] ?? [];
    $dev_supports = $_POST['dev_support'] ?? [];
    $dev_timeframes = $_POST['dev_timeframe'] ?? [];

    // Get template weight split
    $tmpl = $conn->query("SELECT kra_weight, behavior_weight FROM evaluation_templates WHERE template_id = $template_id")->fetch_assoc();
    $kra_weight_pct = (float)($tmpl['kra_weight'] ?? 80);
    $beh_weight_pct = (float)($tmpl['behavior_weight'] ?? 20);

    // Calculate KRA scores (Weight × Rating = Total per item, then sum)
    $kra_subtotal = 0;
    $kra_score_data = [];
    $kra_criteria = $conn->query("SELECT * FROM evaluation_criteria WHERE template_id = $template_id AND section='KRA' ORDER BY sort_order");
    while ($crit = $kra_criteria->fetch_assoc()) {
        $cid = $crit['criterion_id'];
        $rating = floatval($kra_scores[$cid] ?? 0);
        $weight = floatval($crit['weight']);
        $weighted = round(($weight / 100) * $rating, 2);
        $kra_subtotal += $weighted;
        $kra_score_data[] = ['criterion_id' => $cid, 'score_value' => $rating, 'weighted_score' => $weighted];
    }
    $kra_subtotal = round($kra_subtotal, 2);

    // Calculate Behavior scores (average of all ratings)
    $beh_score_data = [];
    $beh_total = 0;
    $beh_count = 0;
    $beh_criteria = $conn->query("SELECT * FROM evaluation_criteria WHERE template_id = $template_id AND section='Behavior' ORDER BY sort_order");
    while ($crit = $beh_criteria->fetch_assoc()) {
        $cid = $crit['criterion_id'];
        $rating = floatval($beh_scores[$cid] ?? 0);
        $beh_total += $rating;
        $beh_count++;
        $beh_score_data[] = ['criterion_id' => $cid, 'score_value' => $rating, 'weighted_score' => $rating];
    }
    $behavior_average = $beh_count > 0 ? round($beh_total / $beh_count, 2) : 0;

    // Overall total: (KRA subtotal × kra_weight%) + (behavior avg × behavior_weight%)
    $total_score = calculateEvalTotal($kra_subtotal, $behavior_average, $kra_weight_pct, $beh_weight_pct);
    $performance_level = getPerformanceLevel($total_score);
    $status = ($action === 'submit') ? 'Pending Supervisor' : 'Draft';
    $submitted_date = ($action === 'submit') ? date('Y-m-d H:i:s') : null;

    if ($edit_id) {
        $stmt = $conn->prepare("UPDATE evaluations SET employee_id=?, template_id=?, evaluation_type=?, evaluation_period_start=?, evaluation_period_end=?, status=?, total_score=?, kra_subtotal=?, behavior_average=?, performance_level=?, submitted_date=?, staff_comments=?, current_position=?, months_in_position=?, desired_position=?, target_date=?, career_growth_details=? WHERE evaluation_id=? AND submitted_by=?");
        $stmt->bind_param("iissssddssssisssii", $employee_id, $template_id, $evaluation_type, $period_start, $period_end, $status, $total_score, $kra_subtotal, $behavior_average, $performance_level, $submitted_date, $staff_comments, $current_position, $months_in_position, $desired_position, $target_date, $career_growth_details, $edit_id, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();
        $conn->query("DELETE FROM evaluation_scores WHERE evaluation_id = $edit_id");
        $conn->query("DELETE FROM evaluation_dev_plans WHERE evaluation_id = $edit_id");
        $eval_id = $edit_id;
    } else {
        $stmt = $conn->prepare("INSERT INTO evaluations (employee_id, template_id, evaluation_type, evaluation_period_start, evaluation_period_end, submitted_by, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_date, staff_comments, current_position, months_in_position, desired_position, target_date, career_growth_details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssisddssssissss", $employee_id, $template_id, $evaluation_type, $period_start, $period_end, $_SESSION['user_id'], $status, $total_score, $kra_subtotal, $behavior_average, $performance_level, $submitted_date, $staff_comments, $current_position, $months_in_position, $desired_position, $target_date, $career_growth_details);
        $stmt->execute();
        $eval_id = $stmt->insert_id;
        $stmt->close();
    }

    // Insert scores
    $score_stmt = $conn->prepare("INSERT INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES (?, ?, ?, ?)");
    foreach (array_merge($kra_score_data, $beh_score_data) as $sd) {
        $score_stmt->bind_param("iidd", $eval_id, $sd['criterion_id'], $sd['score_value'], $sd['weighted_score']);
        $score_stmt->execute();
    }
    $score_stmt->close();

    // Insert dev plans
    $dev_stmt = $conn->prepare("INSERT INTO evaluation_dev_plans (evaluation_id, improvement_area, support_needed, time_frame, sort_order) VALUES (?, ?, ?, ?, ?)");
    for ($i = 0; $i < count($dev_areas); $i++) {
        $area = trim($dev_areas[$i]);
        $support = trim($dev_supports[$i] ?? '');
        $timeframe = trim($dev_timeframes[$i] ?? '');
        $order = $i + 1;
        if (!empty($area)) {
            $dev_stmt->bind_param("isssi", $eval_id, $area, $support, $timeframe, $order);
            $dev_stmt->execute();
        }
    }
    $dev_stmt->close();

    if ($action === 'submit') {
        $supervisors = $conn->query("SELECT user_id FROM users WHERE role = 'HR Supervisor' AND is_active = 1");
        $emp_name = $conn->query("SELECT CONCAT(first_name, ' ', last_name) as name FROM employees WHERE employee_id = $employee_id")->fetch_assoc()['name'];
        while ($sup = $supervisors->fetch_assoc()) {
            createNotification($conn, $sup['user_id'], 'New Evaluation for Validation', "{$_SESSION['full_name']} submitted an evaluation for $emp_name.", BASE_URL . '/supervisor/pending-endorsements.php');
        }
        logAudit($conn, $_SESSION['user_id'], 'CREATE', 'Evaluation', $eval_id, "Submitted evaluation for $emp_name");
        redirectWith(BASE_URL . '/staff/my-submissions.php', 'success', 'Evaluation submitted successfully!');
    } else {
        logAudit($conn, $_SESSION['user_id'], 'CREATE', 'Evaluation', $eval_id, "Saved draft evaluation");
        redirectWith(BASE_URL . '/staff/my-drafts.php', 'success', 'Draft saved successfully.');
    }
}

require_once '../includes/header.php';

// Check if editing
$edit_eval = null;
$edit_scores = [];
$edit_devplans = [];
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $edit_eval = $conn->query("SELECT * FROM evaluations WHERE evaluation_id = $eid AND submitted_by = {$_SESSION['user_id']} AND status IN ('Draft', 'Returned')")->fetch_assoc();
    if ($edit_eval) {
        $es_q = $conn->query("SELECT criterion_id, score_value FROM evaluation_scores WHERE evaluation_id = $eid");
        while ($es = $es_q->fetch_assoc()) $edit_scores[$es['criterion_id']] = $es['score_value'];
        $dp_q = $conn->query("SELECT * FROM evaluation_dev_plans WHERE evaluation_id = $eid ORDER BY sort_order");
        while ($dp = $dp_q->fetch_assoc()) $edit_devplans[] = $dp;
    }
}

$employees = $conn->query("SELECT employee_id, first_name, last_name, job_title FROM employees WHERE is_active = 1 ORDER BY last_name, first_name");
$templates = $conn->query("SELECT * FROM evaluation_templates WHERE status = 'Active' ORDER BY template_name");

$selected_template_id = $edit_eval['template_id'] ?? ($_GET['template'] ?? '');
$kra_criteria = [];
$beh_criteria = [];
if (!empty($selected_template_id)) {
    $crit_q = $conn->query("SELECT * FROM evaluation_criteria WHERE template_id = " . (int)$selected_template_id . " ORDER BY section, sort_order");
    while ($c = $crit_q->fetch_assoc()) {
        if ($c['section'] === 'Behavior') $beh_criteria[] = $c;
        else $kra_criteria[] = $c;
    }
}
?>

<style>
    .step-wizard { display: flex; justify-content: space-between; position: relative; z-index: 1; }
    .step-wizard .step { flex: 1; text-align: center; font-size: 0.85rem; padding-bottom: 10px; border-bottom: 3px solid #dee2e6; color: #6c757d; cursor: default; transition: all 0.3s; }
    .step-wizard .step.active { color: #5a3e1b; border-bottom-color: #5a3e1b; font-weight: bold; }
    .step-wizard .step.completed { color: #7a9a3a; border-bottom-color: #7a9a3a; }
    
    /* Official Form Inspired Styling */
    .form-style-container { border: 1px solid #000; padding: 0; background: #fff; margin-top: 15px; }
    .form-header-box { display: flex; border-bottom: 1px solid #000; }
    .form-logo-box { width: 140px; border-right: 1px solid #000; padding: 10px; display: flex; align-items: center; justify-content: center; background: #fff; }
    .form-title-box { flex: 1; padding: 10px; text-align: center; }
    .form-title-main { font-size: 1.25rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
    
    .form-info-table { width: 100%; border-collapse: collapse; }
    .form-info-table td { border: 1px solid #000; padding: 8px 12px; vertical-align: top; font-size: 0.9rem; }
    .form-section-head { background: #f2f2f2; border: 1px solid #000; padding: 6px 12px; font-weight: bold; text-transform: uppercase; font-size: 0.85rem; margin-top: 15px; }
    
    .table-official { width: 100%; border-collapse: collapse; border: 1px solid #000; }
    .table-official th, .table-official td { border: 1px solid #000; padding: 8px 12px; }
    .table-official th { background: #f8f9fa; font-weight: bold; text-align: center; font-size: 0.85rem; }
    
    .rating-scale-legend { border: 1px solid #000; background: #fcfcfc; padding: 10px; margin-bottom: 20px; }
    
    .form-label-custom { font-weight: bold; font-size: 0.85rem; color: #333; display: block; margin-bottom: 4px; }
    .form-input-clean { border: none; border-bottom: 1px border #ccc; border-radius: 0; padding: 4px 0; width: 100%; focus: outline-none; }
    .form-input-clean:focus { border-bottom-color: #5a3e1b; outline: none; }
    
    /* Summary Overlay */
    .summary-overlay { position: sticky; top: 10px; z-index: 100; background: rgba(255,255,255,0.95); border: 1px solid #5a3e1b; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 8px; padding: 10px 20px; margin-bottom: 20px; display: none; }

    @media (max-width: 768px) {
        .step-wizard { flex-direction: column; gap: 5px; }
        .step-wizard .step { text-align: left; border-bottom: none; border-left: 3px solid #dee2e6; padding: 5px 15px; }
        .step-wizard .step.active { border-left-color: #5a3e1b; }
    }
</style>

<div class="content-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-edit me-2 text-primary"></i><?php echo $edit_eval ? 'Edit Evaluation' : 'Submit New Evaluation'; ?></h5>
        <div class="badge bg-light text-dark border"><i class="fas fa-file-alt me-1"></i> HRD Form-013.01</div>
    </div>
    <div class="card-body">
        
        <!-- Sticky Summary (Visible from Step 3 onwards) -->
        <div id="stickySummary" class="summary-overlay">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex gap-4">
                    <div class="small"><strong>Total Rating:</strong> <span id="stickyTotal" class="fw-bold text-primary">0.00</span></div>
                    <div class="small"><strong>Performance Level:</strong> <span id="stickyLevel" class="badge bg-secondary">Pending</span></div>
                </div>
                <div class="small text-muted">Draft Saved: <span id="lastSaveTime">-</span></div>
            </div>
        </div>

        <!-- Step Wizard -->
        <div class="step-wizard mb-4">
            <div class="step active" id="stepLabel1">1. Info & Period</div>
            <div class="step" id="stepLabel2">2. Template</div>
            <div class="step" id="stepLabel3">3. KRA Results</div>
            <div class="step" id="stepLabel4">4. Behavior</div>
            <div class="step" id="stepLabel5">5. Finalize</div>
        </div>

        <form method="POST" action="" id="evalForm">
            <?php if ($edit_eval): ?>
                <input type="hidden" name="edit_id" value="<?php echo $edit_eval['evaluation_id']; ?>">
            <?php endif; ?>

            <!-- STEP 1: Info & Period -->
            <div id="evalStep1" class="form-style-container">
                <div class="form-header-box">
                    <div class="form-logo-box">
                        <img src="https://raquelpawnshop.com/wp-content/uploads/2023/05/png-logo.png" style="max-width:110px; opacity:0.8;" alt="Logo">
                    </div>
                    <div class="form-title-box">
                        <div class="form-title-main">Performance Evaluation Form</div>
                        <div class="small text-muted">EMPLOYEE INFORMATION & PERIOD</div>
                    </div>
                </div>
                <table class="form-info-table">
                    <tr>
                        <td style="width:50%;">
                            <label class="form-label-custom">Name of Employee <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm border-0 bg-light fw-bold" name="employee_id" id="empSelect" required>
                                <option value="">-- Choose Employee --</option>
                                <?php $employees->data_seek(0); while ($emp = $employees->fetch_assoc()): ?>
                                    <option value="<?php echo $emp['employee_id']; ?>"
                                        <?php $sel = $edit_eval['employee_id'] ?? ($_GET['emp'] ?? ''); echo ($sel == $emp['employee_id']) ? 'selected' : ''; ?>>
                                        <?php echo e($emp['last_name'] . ', ' . $emp['first_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </td>
                        <td style="width:30%;">
                            <label class="form-label-custom">Position</label>
                            <input type="text" class="form-control form-control-sm border-0 bg-transparent fw-bold" id="currentPosStatic" readonly placeholder="Select employee...">
                        </td>
                        <td>
                            <label class="form-label-custom">Date Filed</label>
                            <input type="text" class="form-control form-control-sm border-0 bg-transparent" value="<?php echo date('m/d/Y'); ?>" readonly>
                        </td>
                    </tr>
                    <tr>
                        <td rowspan="2">
                            <label class="form-label-custom">Evaluation Period <span class="text-muted small">(mm/dd/yyyy)</span></label>
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <span class="small">From:</span>
                                <input type="date" class="form-control form-control-sm" name="period_start" value="<?php echo e($edit_eval['evaluation_period_start'] ?? ''); ?>" required>
                                <span class="small">To:</span>
                                <input type="date" class="form-control form-control-sm" name="period_end" value="<?php echo e($edit_eval['evaluation_period_end'] ?? ''); ?>" required>
                            </div>
                        </td>
                        <td colspan="2">
                            <label class="form-label-custom">Evaluation Type</label>
                            <div class="d-flex gap-3 mt-1">
                                <?php foreach (['Initial','Final','Quarterly','Annual'] as $et): ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="evaluation_type" id="type_<?php echo $et; ?>" value="<?php echo $et; ?>" <?php echo ($edit_eval['evaluation_type'] ?? 'Annual') === $et ? 'checked' : ''; ?>>
                                        <label class="form-check-label small" for="type_<?php echo $et; ?>"><?php echo $et; ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="p-3 text-end bg-light border-top">
                    <button type="button" class="btn btn-primary px-4" onclick="goToStep(2)">Next Step <i class="fas fa-chevron-right ms-2"></i></button>
                </div>
            </div>

            <!-- STEP 2: Template -->
            <div id="evalStep2" class="form-style-container" style="display:none;">
                <div class="form-section-head">Step 2: Selection of Template</div>
                <div class="p-4">
                    <label class="form-label-custom mb-3 text-center">Choose the appropriate evaluation template for this employee:</label>
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <select class="form-select form-select-lg border-primary mb-4" name="template_id" id="templateSelect" required onchange="loadCriteria()">
                                <option value="">-- Choose Template --</option>
                                <?php $templates->data_seek(0); while ($t = $templates->fetch_assoc()): ?>
                                    <option value="<?php echo $t['template_id']; ?>"
                                        <?php echo ($edit_eval && $edit_eval['template_id'] == $t['template_id']) ? 'selected' : ''; ?>>
                                        <?php echo e($t['template_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <div class="rating-scale-legend shadow-sm">
                                <h6 class="fw-bold mb-3 border-bottom pb-2">Reference: Performance Rating Scale</h6>
                                <div class="row g-2">
                                    <div class="col-6"><span class="badge bg-success me-2">3.60 – 4.00</span> <span class="small font-weight-bold">Outstanding</span></div>
                                    <div class="col-6"><span class="badge bg-primary me-2">2.60 – 3.59</span> <span class="small font-weight-bold">Exceeds Expectations</span></div>
                                    <div class="col-6"><span class="badge bg-info me-2">2.00 – 2.59</span> <span class="small font-weight-bold">Meets Expectations</span></div>
                                    <div class="col-6"><span class="badge bg-danger me-2">1.00 – 1.99</span> <span class="small font-weight-bold">Needs Improvement</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-3 d-flex justify-content-between bg-light border-top">
                    <button type="button" class="btn btn-outline-secondary px-4" onclick="goToStep(1)"><i class="fas fa-chevron-left me-2"></i>Back</button>
                    <button type="button" class="btn btn-primary px-4" onclick="goToStep(3)">Next Step <i class="fas fa-chevron-right ms-2"></i></button>
                </div>
            </div>

            <!-- STEP 3: KRA Scoring -->
            <div id="evalStep3" class="form-style-container" style="display:none;">
                <div class="form-section-head">I. Key Result Areas (80%)</div>
                <div class="p-3">
                    <div id="kraScoreArea">
                        <!-- Loaded dynamically -->
                    </div>
                </div>
                <div class="p-3 d-flex justify-content-between bg-light border-top">
                    <button type="button" class="btn btn-outline-secondary px-4" onclick="goToStep(2)"><i class="fas fa-chevron-left me-2"></i>Back</button>
                    <button type="button" class="btn btn-primary px-4" onclick="goToStep(4)">Next Step <i class="fas fa-chevron-right ms-2"></i></button>
                </div>
            </div>

            <!-- STEP 4: Behavior -->
            <div id="evalStep4" class="form-style-container" style="display:none;">
                <div class="form-section-head">II. Behavior and Values (20%)</div>
                <div class="p-3">
                    <div id="behScoreArea">
                        <!-- Loaded dynamically -->
                    </div>
                </div>
                <div class="p-3 d-flex justify-content-between bg-light border-top">
                    <button type="button" class="btn btn-outline-secondary px-4" onclick="goToStep(3)"><i class="fas fa-chevron-left me-2"></i>Back</button>
                    <button type="button" class="btn btn-primary px-4" onclick="goToStep(5)">Next Step <i class="fas fa-chevron-right ms-2"></i></button>
                </div>
            </div>

            <!-- STEP 5: Finalize -->
            <div id="evalStep5" class="form-style-container" style="display:none;">
                <div class="form-section-head">III. Developmental Plan & Career Growth</div>
                
                <div class="p-3">
                    <label class="form-label-custom mb-2">Developmental Plan</label>
                    <table class="table-official mb-3" id="devPlanTable">
                        <thead>
                            <tr>
                                <th>Area of Improvement</th>
                                <th>Support Needed</th>
                                <th style="width:150px;">Time Frame</th>
                                <th style="width:50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="devPlanContainer">
                            <!-- Rows added via JS -->
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-success mb-4" onclick="addDevPlan()">
                        <i class="fas fa-plus me-1"></i>Add Development Goal
                    </button>

                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label-custom">IV. Career Growth and Development</label>
                            <div class="p-3 border bg-light">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="small fw-bold">Current Position</label>
                                        <input type="text" class="form-control form-control-sm" name="current_position" id="currentPosInput" value="<?php echo e($edit_eval['current_position'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small fw-bold">Months in position</label>
                                        <input type="number" class="form-control form-control-sm" name="months_in_position" value="<?php echo e($edit_eval['months_in_position'] ?? '0'); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small fw-bold text-primary">Desired Position (Career Goal)</label>
                                        <input type="text" class="form-control form-control-sm border-primary" name="desired_position" value="<?php echo e($edit_eval['desired_position'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small fw-bold text-primary">Target Date</label>
                                        <input type="date" class="form-control form-control-sm border-primary" name="target_date" value="<?php echo e($edit_eval['target_date'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label-custom">V. Employee's Comments / Justification</label>
                        <textarea class="form-control border-dark" name="staff_comments" rows="4" placeholder="Enter your observations and justifications..."><?php echo e($edit_eval['staff_comments'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="p-3 d-flex justify-content-between bg-light border-top">
                    <button type="button" class="btn btn-outline-secondary px-4" onclick="goToStep(4)"><i class="fas fa-chevron-left me-2"></i>Back</button>
                    <div class="d-flex gap-2">
                        <button type="submit" name="submit_action" value="draft" class="btn btn-outline-dark px-4">
                            <i class="fas fa-save me-1"></i>Save Draft
                        </button>
                        <button type="submit" name="submit_action" value="submit" class="btn btn-success px-5 fw-bold">
                            <i class="fas fa-paper-plane me-2"></i>SUBMIT EVALUATION
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let templateWeights = { kra: 80, behavior: 20 };

function goToStep(step) {
    if (step === 2 && !document.getElementById('empSelect').value) { alert('Please select an employee.'); return; }
    if (step === 3 && !document.getElementById('templateSelect').value) { alert('Please select a template.'); return; }

    document.querySelectorAll('[id^="evalStep"]').forEach(el => el.style.display = 'none');
    document.getElementById('evalStep' + step).style.display = 'block';

    document.querySelectorAll('.step-wizard .step').forEach(el => el.classList.remove('active', 'completed'));
    for (let i = 1; i <= 5; i++) {
        const label = document.getElementById('stepLabel' + i);
        if (i < step) label.classList.add('completed');
        else if (i === step) label.classList.add('active');
    }

    if (step >= 3) {
        calculateAllScores();
        document.getElementById('stickySummary').style.display = 'block';
    } else {
        document.getElementById('stickySummary').style.display = 'none';
    }
    
    if (step === 5) updateSummary();
}

const employees = <?php echo json_encode($conn->query("SELECT employee_id, job_title FROM employees")->fetch_all(MYSQLI_ASSOC)); ?>;

function loadCriteria() {
    const templateId = document.getElementById('templateSelect').value;
    if (!templateId) return;

    const kraArea = document.getElementById('kraScoreArea');
    const behArea = document.getElementById('behScoreArea');
    kraArea.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading...</p></div>';
    behArea.innerHTML = kraArea.innerHTML;

    fetch('<?php echo BASE_URL; ?>/staff/get-criteria.php?template_id=' + templateId)
        .then(r => r.json())
        .then(data => {
            templateWeights.kra = data.kra_weight || 80;
            templateWeights.behavior = data.behavior_weight || 20;

            // Build KRA table
            if (data.kra && data.kra.length > 0) {
                let html = `<div class="p-2 border bg-light mb-3"><table class="table-official" id="kraTable">`;
                html += `<thead><tr><th style="text-align:left;">I. KEY RESULT AREAS (${data.kra_weight}%)</th><th style="width:90px;">Weight</th><th style="width:110px;">Rating</th><th style="width:100px;">Total</th></tr></thead><tbody>`;
                data.kra.forEach((c, i) => {
                    html += `<tr>
                        <td><div class="fw-bold">KRA ${i+1}: ${esc(c.criterion_name)}</div><div class="small text-muted">${esc(c.description)}</div></td>
                        <td class="text-center fw-bold">${c.weight}%</td>
                        <td><input type="number" class="form-control form-control-sm kra-score-input text-center fw-bold border-primary" name="kra_scores[${c.criterion_id}]" data-weight="${c.weight}" min="1" max="4" step="0.01" required oninput="calculateAllScores()"></td>
                        <td class="text-center fw-bold text-primary kra-total-cell" id="kraTotal_${c.criterion_id}">-</td>
                    </tr>`;
                });
                html += `<tr class="bg-light fw-bold"><td class="text-end">SUB TOTAL</td><td class="text-center" id="kraWeightTotal">0%</td><td></td><td class="text-center text-primary" id="kraSubTotal">-</td></tr></tbody></table></div>`;
                kraArea.innerHTML = html;
            }

            // Build Behavior table
            if (data.behavior && data.behavior.length > 0) {
                let html = `<div class="p-2 border bg-light"><table class="table-official" id="behTable">`;
                html += `<thead><tr><th style="text-align:left;">II. BEHAVIOR AND VALUES (${data.behavior_weight}%)</th><th>KPI Description</th><th style="width:110px;">Rating</th></tr></thead><tbody>`;
                data.behavior.forEach((c, i) => {
                    html += `<tr>
                        <td class="fw-bold text-nowrap">${i+1}. ${esc(c.criterion_name)}</td>
                        <td class="small">${esc(c.kpi_description || c.description || '')}</td>
                        <td><input type="number" class="form-control form-control-sm beh-score-input text-center fw-bold border-primary" name="beh_scores[${c.criterion_id}]" min="1" max="4" step="0.01" required oninput="calculateAllScores()"></td>
                    </tr>`;
                });
                html += `<tr class="bg-light fw-bold"><td colspan="2" class="text-end">AVERAGE</td><td class="text-center text-primary" id="behAverage">-</td></tr></tbody></table></div>`;
                behArea.innerHTML = html;
            }
        })
        .catch(err => { console.error(err); kraArea.innerHTML = '<div class="alert alert-danger">Error loading criteria.</div>'; });
}

function calculateAllScores() {
    let kraSubTotal = 0, kraWeightTotal = 0;
    document.querySelectorAll('.kra-score-input').forEach(input => {
        const rating = parseFloat(input.value) || 0;
        const weight = parseFloat(input.dataset.weight) || 0;
        kraWeightTotal += weight;
        const total = (weight / 100) * rating;
        kraSubTotal += total;
        const inputName = input.getAttribute('name');
        if (inputName) {
            const cidMatch = inputName.match(/\[(\d+)\]/);
            if (cidMatch) {
                const cell = document.getElementById('kraTotal_' + cidMatch[1]);
                if (cell) cell.textContent = rating > 0 ? total.toFixed(2) : '-';
            }
        }
    });
    
    if (document.getElementById('kraWeightTotal')) document.getElementById('kraWeightTotal').textContent = kraWeightTotal.toFixed(0) + '%';
    if (document.getElementById('kraSubTotal')) document.getElementById('kraSubTotal').textContent = kraSubTotal.toFixed(2);

    let behTotal = 0, behCount = 0;
    document.querySelectorAll('.beh-score-input').forEach(input => {
        const rating = parseFloat(input.value) || 0;
        if (rating > 0) { behTotal += rating; behCount++; }
    });
    const behAvg = behCount > 0 ? behTotal / behCount : 0;
    if (document.getElementById('behAverage')) document.getElementById('behAverage').textContent = behCount > 0 ? behAvg.toFixed(2) : '-';

    const overall = (kraSubTotal * templateWeights.kra / 100) + (behAvg * templateWeights.behavior / 100);
    
    // Update displays
    const scoreVal = overall.toFixed(2);
    let level = 'Needs Improvement', cls = 'bg-danger';
    if (overall >= 3.60) { level = 'Outstanding'; cls = 'bg-success'; }
    else if (overall >= 2.60) { level = 'Exceeds Expectations'; cls = 'bg-primary'; }
    else if (overall >= 2.00) { level = 'Meets Expectations'; cls = 'bg-info'; }

    if (document.getElementById('stickyTotal')) document.getElementById('stickyTotal').textContent = scoreVal;
    const sLevel = document.getElementById('stickyLevel');
    if (sLevel) { sLevel.textContent = level; sLevel.className = 'badge ' + cls; }
}

function updateSummary() {
    calculateAllScores();
    // Step 5 specific summary
    const summaryTotal = document.getElementById('stickyTotal').textContent;
}

function addDevPlan(area = '', support = '', timeframe = '') {
    const container = document.getElementById('devPlanContainer');
    const html = `<tr class="dev-plan-row">
        <td><input type="text" class="form-control form-control-sm border-0" name="dev_area[]" placeholder="Improvement area..." value="${esc(area)}"></td>
        <td><input type="text" class="form-control form-control-sm border-0" name="dev_support[]" placeholder="Support needed..." value="${esc(support)}"></td>
        <td><input type="text" class="form-control form-control-sm border-0" name="dev_timeframe[]" placeholder="Time frame" value="${esc(timeframe)}"></td>
        <td class="text-center"><button type="button" class="btn btn-sm text-danger" onclick="this.closest('tr').remove()"><i class="fas fa-times"></i></button></td>
    </tr>`;
    container.insertAdjacentHTML('beforeend', html);
}

function esc(str) {
    const d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('empSelect').addEventListener('change', function() {
        const emp = employees.find(e => e.employee_id == this.value);
        if (emp) {
            document.getElementById('currentPosStatic').value = emp.job_title;
            const target = document.getElementById('currentPosInput');
            if (target) target.value = emp.job_title;
        }
    });

    if (document.querySelectorAll('.dev-plan-row').length === 0) {
        for(let i=0; i<3; i++) addDevPlan();
    }
});

function esc(str) {
    const d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
}

<?php if ($edit_eval || !empty($selected_template_id)): ?>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($edit_eval): ?>
    goToStep(3);
    <?php endif; ?>
    calculateAllScores();
});
<?php endif; ?>

// Auto-fill Current Position when employee is selected
document.getElementById('empSelect').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex].text;
    if (selected.includes(' — ')) {
        const title = selected.split(' — ')[1];
        const input = document.getElementById('currentPosInput');
        if (input && !input.value) input.value = title;
    }
});

// Add initial dev plan rows if none exist
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelectorAll('.dev-plan-row').length === 0) {
        addDevPlan(); addDevPlan(); addDevPlan();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
