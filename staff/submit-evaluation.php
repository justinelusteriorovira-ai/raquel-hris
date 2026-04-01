<?php
$page_title = 'Submit Evaluation';
require_once '../includes/session-check.php';
checkRole(['HR Staff']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../includes/functions.php';

    $employee_id = (int)$_POST['employee_id'];
    $template_id = (int)$_POST['template_id'];
    $period_start = $_POST['period_start'] ?? null;
    $period_end = $_POST['period_end'] ?? null;
    $staff_comments = trim($_POST['staff_comments'] ?? '');
    $action = $_POST['submit_action'] ?? 'draft';
    $scores = $_POST['scores'] ?? [];
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    // Calculate total and weighted scores
    $total_score = 0;
    $score_data = [];

    // Get criteria for this template
    $criteria = $conn->query("SELECT * FROM evaluation_criteria WHERE template_id = $template_id ORDER BY sort_order");
    while ($crit = $criteria->fetch_assoc()) {
        $cid = $crit['criterion_id'];
        $raw_score = floatval($scores[$cid] ?? 0);
        $weight = floatval($crit['weight']);

        // Calculate weighted score based on scoring method
        $max_score = 5; // default
        switch ($crit['scoring_method']) {
            case 'Scale_1_5': $max_score = 5; break;
            case 'Scale_1_10': $max_score = 10; break;
            case 'Percentage': $max_score = 100; break;
        }

        $weighted = ($raw_score / $max_score) * $weight;
        $total_score += $weighted;

        $score_data[] = [
            'criterion_id' => $cid,
            'score_value' => $raw_score,
            'weighted_score' => round($weighted, 2)
        ];
    }

    $total_score = round($total_score, 2);
    $performance_level = getPerformanceLevel($total_score);
    $status = ($action === 'submit') ? 'Pending Supervisor' : 'Draft';
    $submitted_date = ($action === 'submit') ? date('Y-m-d H:i:s') : null;

    if ($edit_id) {
        // Update existing evaluation
        $stmt = $conn->prepare("UPDATE evaluations SET employee_id = ?, template_id = ?, evaluation_period_start = ?, evaluation_period_end = ?, status = ?, total_score = ?, performance_level = ?, submitted_date = ?, staff_comments = ? WHERE evaluation_id = ? AND submitted_by = ?");
        $stmt->bind_param("iisssdsssii", $employee_id, $template_id, $period_start, $period_end, $status, $total_score, $performance_level, $submitted_date, $staff_comments, $edit_id, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();

        // Delete old scores and re-insert
        $conn->query("DELETE FROM evaluation_scores WHERE evaluation_id = $edit_id");
        $eval_id = $edit_id;
    } else {
        // Insert new evaluation
        $stmt = $conn->prepare("INSERT INTO evaluations (employee_id, template_id, evaluation_period_start, evaluation_period_end, submitted_by, status, total_score, performance_level, submitted_date, staff_comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissisdsss", $employee_id, $template_id, $period_start, $period_end, $_SESSION['user_id'], $status, $total_score, $performance_level, $submitted_date, $staff_comments);
        $stmt->execute();
        $eval_id = $stmt->insert_id;
        $stmt->close();
    }

    // Insert scores
    $score_stmt = $conn->prepare("INSERT INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES (?, ?, ?, ?)");
    foreach ($score_data as $sd) {
        $score_stmt->bind_param("iidd", $eval_id, $sd['criterion_id'], $sd['score_value'], $sd['weighted_score']);
        $score_stmt->execute();
    }
    $score_stmt->close();

    if ($action === 'submit') {
        // Notify supervisors
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

// Check if editing an existing draft
$edit_eval = null;
$edit_scores = [];
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $edit_eval = $conn->query("SELECT * FROM evaluations WHERE evaluation_id = $eid AND submitted_by = {$_SESSION['user_id']} AND status IN ('Draft', 'Returned')")->fetch_assoc();
    if ($edit_eval) {
        $es_q = $conn->query("SELECT criterion_id, score_value FROM evaluation_scores WHERE evaluation_id = $eid");
        while ($es = $es_q->fetch_assoc()) {
            $edit_scores[$es['criterion_id']] = $es['score_value'];
        }
    }
}

// Fetch employees and templates
$employees = $conn->query("SELECT employee_id, first_name, last_name, job_title, department FROM employees WHERE is_active = 1 ORDER BY last_name, first_name");
$templates = $conn->query("SELECT * FROM evaluation_templates WHERE status = 'Active' ORDER BY template_name");

// If employee/template are pre-selected (editing), load criteria
$selected_template_id = $edit_eval['template_id'] ?? ($_GET['template'] ?? '');
$criteria = [];
if (!empty($selected_template_id)) {
    $crit_q = $conn->query("SELECT * FROM evaluation_criteria WHERE template_id = " . (int)$selected_template_id . " ORDER BY sort_order");
    while ($c = $crit_q->fetch_assoc()) {
        $criteria[] = $c;
    }
}
?>

<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-edit me-2"></i><?php echo $edit_eval ? 'Edit Evaluation' : 'Submit New Evaluation'; ?></h5>
    </div>
    <div class="card-body">
        <!-- Step Wizard -->
        <div class="step-wizard mb-4">
            <div class="step active" id="stepLabel1">1. Select Employee</div>
            <div class="step" id="stepLabel2">2. Select Template</div>
            <div class="step" id="stepLabel3">3. Fill Evaluation</div>
        </div>

        <form method="POST" action="" id="evalForm">
            <?php if ($edit_eval): ?>
                <input type="hidden" name="edit_id" value="<?php echo $edit_eval['evaluation_id']; ?>">
            <?php endif; ?>

            <!-- Step 1: Select Employee -->
            <div id="evalStep1">
                <h6 class="mb-3">Select the employee to evaluate:</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <select class="form-select" name="employee_id" id="empSelect" required>
                            <option value="">-- Choose Employee --</option>
                            <?php while ($emp = $employees->fetch_assoc()): ?>
                                <option value="<?php echo $emp['employee_id']; ?>"
                                    <?php
                                    $sel_emp_id = $edit_eval['employee_id'] ?? ($_GET['emp'] ?? '');
                                    echo ($sel_emp_id == $emp['employee_id']) ? 'selected' : '';
                                    ?>>
                                    <?php echo e($emp['last_name'] . ', ' . $emp['first_name'] . ' — ' . $emp['job_title']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-primary" onclick="goToStep(2)">Next <i class="fas fa-arrow-right ms-2"></i></button>
                </div>
            </div>

            <!-- Step 2: Select Template -->
            <div id="evalStep2" style="display:none;">
                <h6 class="mb-3">Select an evaluation template:</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <select class="form-select" name="template_id" id="templateSelect" required onchange="loadCriteria()">
                            <option value="">-- Choose Template --</option>
                            <?php
                            $templates->data_seek(0);
                            while ($t = $templates->fetch_assoc()): ?>
                                <option value="<?php echo $t['template_id']; ?>"
                                    <?php echo ($edit_eval && $edit_eval['template_id'] == $t['template_id']) ? 'selected' : ''; ?>>
                                    <?php echo e($t['template_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Evaluation Period Start</label>
                        <input type="date" class="form-control" name="period_start" value="<?php echo e($edit_eval['evaluation_period_start'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Evaluation Period End</label>
                        <input type="date" class="form-control" name="period_end" value="<?php echo e($edit_eval['evaluation_period_end'] ?? ''); ?>">
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" onclick="goToStep(1)"><i class="fas fa-arrow-left me-2"></i>Back</button>
                    <button type="button" class="btn btn-primary" onclick="goToStep(3)">Next <i class="fas fa-arrow-right ms-2"></i></button>
                </div>
            </div>

            <!-- Step 3: Fill Evaluation -->
            <div id="evalStep3" style="display:none;">
                <div id="criteriaArea">
                    <?php if (!empty($criteria)): ?>
                        <?php foreach ($criteria as $idx => $crit): ?>
                            <div class="p-3 mb-3 rounded" style="background:#f8f9fa;">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong><?php echo e($crit['criterion_name']); ?></strong>
                                        <?php if ($crit['description']): ?>
                                            <br><small class="text-muted"><?php echo e($crit['description']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge bg-primary"><?php echo $crit['weight']; ?>%</span>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <label class="form-label">Score
                                            (<?php
                                            switch ($crit['scoring_method']) {
                                                case 'Scale_1_5': echo '1-5'; break;
                                                case 'Scale_1_10': echo '1-10'; break;
                                                case 'Percentage': echo '0-100'; break;
                                            }
                                            ?>)</label>
                                        <?php
                                        $max = 5;
                                        switch ($crit['scoring_method']) {
                                            case 'Scale_1_5': $max = 5; break;
                                            case 'Scale_1_10': $max = 10; break;
                                            case 'Percentage': $max = 100; break;
                                        }
                                        $existing_score = $edit_scores[$crit['criterion_id']] ?? '';
                                        ?>
                                        <input type="number" class="form-control score-input"
                                               name="scores[<?php echo $crit['criterion_id']; ?>]"
                                               id="score_<?php echo $crit['criterion_id']; ?>"
                                               data-weight="<?php echo $crit['weight']; ?>"
                                               data-max="<?php echo $max; ?>"
                                               min="<?php echo ($crit['scoring_method'] === 'Percentage') ? 0 : 1; ?>"
                                               max="<?php echo $max; ?>"
                                               step="0.01"
                                               required
                                               value="<?php echo e($existing_score); ?>"
                                               oninput="calculateScores()">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Weighted Score</label>
                                        <input type="text" class="form-control" id="weighted_<?php echo $crit['criterion_id']; ?>" readonly style="background:#e9ecef;">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-clipboard-list d-block"></i>
                            <p>Select a template to load evaluation criteria.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Score Display -->
                <div class="score-display" id="scoreDisplay">
                    <div class="score-label">Total Score</div>
                    <div class="score-value" id="totalScoreDisplay">0.00%</div>
                    <input type="hidden" name="total_score" id="totalScoreInput">
                    <span class="badge bg-secondary mt-2" id="performanceBadge" style="font-size:1rem;">-</span>
                    <input type="hidden" name="performance_level" id="performanceLevelInput">
                </div>

                <!-- Staff Comments -->
                <div class="mt-3 mb-3">
                    <label class="form-label"><strong>Staff Comments / Observations</strong></label>
                    <textarea class="form-control" name="staff_comments" rows="3" placeholder="Enter your observations and justifications..."><?php echo e($edit_eval['staff_comments'] ?? ''); ?></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" onclick="goToStep(2)">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </button>
                    <div class="d-flex gap-2">
                        <button type="submit" name="submit_action" value="draft" class="btn btn-outline-secondary">
                            <i class="fas fa-save me-1"></i>Save as Draft
                        </button>
                        <button type="submit" name="submit_action" value="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane me-1"></i>Submit for Validation
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function goToStep(step) {
    // Validate before proceeding
    if (step === 2 && !document.getElementById('empSelect').value) {
        alert('Please select an employee.');
        return;
    }
    if (step === 3 && !document.getElementById('templateSelect').value) {
        alert('Please select a template.');
        return;
    }

    document.querySelectorAll('[id^="evalStep"]').forEach(el => el.style.display = 'none');
    document.getElementById('evalStep' + step).style.display = 'block';

    document.querySelectorAll('.step-wizard .step').forEach(el => el.classList.remove('active', 'completed'));
    for (let i = 1; i <= 3; i++) {
        const label = document.getElementById('stepLabel' + i);
        if (i < step) label.classList.add('completed');
        else if (i === step) label.classList.add('active');
    }

    if (step === 3) calculateScores();
}

function loadCriteria() {
    const templateId = document.getElementById('templateSelect').value;
    if (!templateId) return;

    const area = document.getElementById('criteriaArea');
    area.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading criteria...</p></div>';

    fetch('<?php echo BASE_URL; ?>/staff/get-criteria.php?template_id=' + templateId)
        .then(r => r.json())
        .then(criteria => {
            if (criteria.length === 0) {
                area.innerHTML = '<div class="empty-state"><i class="fas fa-clipboard-list d-block"></i><p>No criteria found for this template.</p></div>';
                return;
            }
            let html = '';
            criteria.forEach(crit => {
                let maxScore = 5, rangeLabel = '1-5';
                if (crit.scoring_method === 'Scale_1_10') { maxScore = 10; rangeLabel = '1-10'; }
                else if (crit.scoring_method === 'Percentage') { maxScore = 100; rangeLabel = '0-100'; }
                const minVal = (crit.scoring_method === 'Percentage') ? 0 : 1;

                html += '<div class="p-3 mb-3 rounded" style="background:#f8f9fa;">';
                html += '<div class="d-flex justify-content-between align-items-start mb-2"><div>';
                html += '<strong>' + escHtml(crit.criterion_name) + '</strong>';
                if (crit.description) html += '<br><small class="text-muted">' + escHtml(crit.description) + '</small>';
                html += '</div><span class="badge bg-primary">' + crit.weight + '%</span></div>';
                html += '<div class="row align-items-center"><div class="col-md-4">';
                html += '<label class="form-label">Score (' + rangeLabel + ')</label>';
                html += '<input type="number" class="form-control score-input" name="scores[' + crit.criterion_id + ']" '
                      + 'id="score_' + crit.criterion_id + '" data-weight="' + crit.weight + '" data-max="' + maxScore + '" '
                      + 'min="' + minVal + '" max="' + maxScore + '" step="0.01" required oninput="calculateScores()">';
                html += '</div><div class="col-md-4">';
                html += '<label class="form-label">Weighted Score</label>';
                html += '<input type="text" class="form-control" id="weighted_' + crit.criterion_id + '" readonly style="background:#e9ecef;">';
                html += '</div></div></div>';
            });
            area.innerHTML = html;
            calculateScores();
        })
        .catch(() => {
            area.innerHTML = '<div class="alert alert-danger">Failed to load criteria. Please try again.</div>';
        });
}

function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function calculateScores() {
    let total = 0;
    document.querySelectorAll('.score-input').forEach(function(input) {
        const score = parseFloat(input.value) || 0;
        const weight = parseFloat(input.dataset.weight) || 0;
        const max = parseFloat(input.dataset.max) || 5;

        const weighted = (score / max) * weight;
        total += weighted;

        const weightedField = document.getElementById('weighted_' + input.name.match(/\d+/)[0]);
        if (weightedField) {
            weightedField.value = weighted.toFixed(2) + '%';
        }
    });

    document.getElementById('totalScoreDisplay').textContent = total.toFixed(2) + '%';
    document.getElementById('totalScoreInput').value = total.toFixed(2);

    // Performance level
    let level, badgeClass;
    if (total >= 90) { level = 'Excellent'; badgeClass = 'bg-success'; }
    else if (total >= 80) { level = 'Above Average'; badgeClass = 'bg-info'; }
    else if (total >= 70) { level = 'Average'; badgeClass = 'bg-warning text-dark'; }
    else { level = 'Needs Improvement'; badgeClass = 'bg-danger'; }

    document.getElementById('performanceBadge').textContent = level;
    document.getElementById('performanceBadge').className = 'badge mt-2 ' + badgeClass;
    document.getElementById('performanceBadge').style.fontSize = '1rem';
    document.getElementById('performanceLevelInput').value = level;
}

<?php if ($edit_eval || !empty($selected_template_id)): ?>
// Auto-navigate to appropriate step
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($edit_eval): ?>
        goToStep(3);
    <?php elseif (!empty($selected_template_id)): ?>
        goToStep(3);
    <?php endif; ?>
    calculateScores();
});
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>
