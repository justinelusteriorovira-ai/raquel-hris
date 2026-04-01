<?php
$page_title = 'Edit Template';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/functions.php';

// Validate template ID
$tid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($tid <= 0) {
    redirectWith(BASE_URL . '/manager/templates.php', 'danger', 'Invalid template ID.');
}

// Fetch template
$stmt = $conn->prepare("SELECT * FROM evaluation_templates WHERE template_id = ?");
$stmt->bind_param("i", $tid);
$stmt->execute();
$template = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$template) {
    redirectWith(BASE_URL . '/manager/templates.php', 'danger', 'Template not found.');
}

// Fetch existing criteria
$criteria = $conn->query("SELECT * FROM evaluation_criteria WHERE template_id = $tid ORDER BY sort_order");
$existing_criteria = [];
while ($c = $criteria->fetch_assoc()) {
    $existing_criteria[] = $c;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $template_name = trim($_POST['template_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $target_position = trim($_POST['target_position'] ?? '') ?: null;

    $criterion_names = $_POST['criterion_name'] ?? [];
    $criterion_descs = $_POST['criterion_description'] ?? [];
    $criterion_weights = $_POST['criterion_weight'] ?? [];
    $criterion_methods = $_POST['scoring_method'] ?? $_POST['criterion_method'] ?? [];

    if (empty($template_name) || empty($criterion_names)) {
        redirectWith(BASE_URL . "/manager/edit-template.php?id=$tid", 'danger', 'Template name and at least one criterion are required.');
    }

    // Validate weights sum to 100
    $total_weight = array_sum(array_map('floatval', $criterion_weights));
    if (abs($total_weight - 100) > 0.01) {
        redirectWith(BASE_URL . "/manager/edit-template.php?id=$tid", 'danger', 'Total weight must equal 100%. Currently: ' . $total_weight . '%');
    }

    // Update template info
    $stmt = $conn->prepare("UPDATE evaluation_templates SET template_name=?, description=?, target_position=? WHERE template_id=?");
    $stmt->bind_param("sssi", $template_name, $description, $target_position, $tid);
    $stmt->execute();
    $stmt->close();

    // Delete old criteria and re-insert
    $conn->query("DELETE FROM evaluation_criteria WHERE template_id = $tid");

    $stmt = $conn->prepare("INSERT INTO evaluation_criteria (template_id, criterion_name, description, weight, scoring_method, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
    for ($i = 0; $i < count($criterion_names); $i++) {
        $name = trim($criterion_names[$i]);
        $desc = trim($criterion_descs[$i] ?? '');
        $weight = (float)($criterion_weights[$i] ?? 0);
        $method = $criterion_methods[$i] ?? 'Scale_1_5';
        $order = $i + 1;
        if (!empty($name)) {
            $stmt->bind_param("issdsi", $tid, $name, $desc, $weight, $method, $order);
            $stmt->execute();
        }
    }
    $stmt->close();

    logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'Template', $tid, "Updated template: $template_name");
    redirectWith(BASE_URL . '/manager/templates.php', 'success', "Template '$template_name' updated successfully.");
}

require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Edit evaluation template and criteria</p>
    <a href="<?php echo BASE_URL; ?>/manager/templates.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Templates
    </a>
</div>

<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-edit me-2"></i>Edit Template: <?php echo e($template['template_name']); ?></h5>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="editTemplateForm">
            <!-- Template Info -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Template Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="template_name" value="<?php echo e($template['template_name']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Target Position <small class="text-muted">(optional)</small></label>
                    <input type="text" class="form-control" name="target_position" value="<?php echo e($template['target_position'] ?? ''); ?>" placeholder="e.g. Senior Appraiser">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="2"><?php echo e($template['description'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Criteria Section -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6><i class="fas fa-list-check me-2"></i>Evaluation Criteria</h6>
                <div>
                    <span class="badge bg-primary me-2" id="weightBadge">Total: 0%</span>
                    <button type="button" class="btn btn-sm btn-success" onclick="addCriterion()">
                        <i class="fas fa-plus me-1"></i>Add Criterion
                    </button>
                </div>
            </div>

            <div id="criteriaContainer">
                <?php foreach ($existing_criteria as $i => $c): ?>
                <div class="criteria-row border rounded p-3 mb-3" id="criterion_<?php echo $i; ?>">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Criterion #<?php echo $i + 1; ?></strong>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCriterion('criterion_<?php echo $i; ?>')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="criterion_name[]" value="<?php echo e($c['criterion_name']); ?>" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Weight (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control weight-input" name="criterion_weight[]" value="<?php echo (float)$c['weight']; ?>" step="0.01" min="0" max="100" required oninput="updateWeight()">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Scoring Method</label>
                            <select class="form-select" name="scoring_method[]">
                                <option value="Scale_1_5" <?php echo $c['scoring_method']==='Scale_1_5'?'selected':''; ?>>Scale 1-5</option>
                                <option value="Scale_1_10" <?php echo $c['scoring_method']==='Scale_1_10'?'selected':''; ?>>Scale 1-10</option>
                                <option value="Percentage" <?php echo $c['scoring_method']==='Percentage'?'selected':''; ?>>Percentage</option>
                            </select>
                        </div>
                        <div class="col-12 mb-2">
                            <label class="form-label">Description</label>
                            <input type="text" class="form-control" name="criterion_description[]" value="<?php echo e($c['description'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="<?php echo BASE_URL; ?>/manager/templates.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Template</button>
            </div>
        </form>
    </div>
</div>

<script>
let criterionCount = <?php echo count($existing_criteria); ?>;

function addCriterion() {
    criterionCount++;
    const container = document.getElementById('criteriaContainer');
    const div = document.createElement('div');
    div.className = 'criteria-row border rounded p-3 mb-3';
    div.id = 'criterion_new_' + criterionCount;
    div.innerHTML = `
        <div class="d-flex justify-content-between mb-2">
            <strong>Criterion #${container.children.length + 1}</strong>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCriterion('criterion_new_${criterionCount}')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="row">
            <div class="col-md-4 mb-2">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="criterion_name[]" required>
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label">Weight (%) <span class="text-danger">*</span></label>
                <input type="number" class="form-control weight-input" name="criterion_weight[]" step="0.01" min="0" max="100" required oninput="updateWeight()">
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label">Scoring Method</label>
                <select class="form-select" name="scoring_method[]">
                    <option value="Scale_1_5">Scale 1-5</option>
                    <option value="Scale_1_10">Scale 1-10</option>
                    <option value="Percentage">Percentage</option>
                </select>
            </div>
            <div class="col-12 mb-2">
                <label class="form-label">Description</label>
                <input type="text" class="form-control" name="criterion_description[]">
            </div>
        </div>
    `;
    container.appendChild(div);
    updateWeight();
}

function removeCriterion(id) {
    const el = document.getElementById(id);
    if (el && document.querySelectorAll('.criteria-row').length > 1) {
        el.remove();
        updateWeight();
    } else {
        alert('At least one criterion is required.');
    }
}

function updateWeight() {
    let total = 0;
    document.querySelectorAll('.weight-input').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    const badge = document.getElementById('weightBadge');
    badge.textContent = 'Total: ' + total.toFixed(2) + '%';
    badge.className = 'badge me-2 ' + (Math.abs(total - 100) < 0.01 ? 'bg-success' : 'bg-danger');
}

// Initialize weight on page load
updateWeight();
</script>

<?php require_once '../includes/footer.php'; ?>
