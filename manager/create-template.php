<?php
$page_title = 'Create Evaluation Template';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../includes/functions.php';

    $template_name = trim($_POST['template_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $target_position = trim($_POST['target_position'] ?? '');
    $criteria_names = $_POST['criterion_name'] ?? [];
    $criteria_descriptions = $_POST['criterion_description'] ?? [];
    $criteria_weights = $_POST['criterion_weight'] ?? [];
    $criteria_methods = $_POST['criterion_method'] ?? [];

    if (empty($template_name)) {
        redirectWith(BASE_URL . '/manager/create-template.php', 'danger', 'Template name is required.');
    }

    if (empty($criteria_names)) {
        redirectWith(BASE_URL . '/manager/create-template.php', 'danger', 'At least one criterion is required.');
    }

    // Validate total weight = 100
    $total_weight = array_sum(array_map('floatval', $criteria_weights));
    if (abs($total_weight - 100) > 0.01) {
        redirectWith(BASE_URL . '/manager/create-template.php', 'danger', 'Total criteria weight must equal 100%. Current total: ' . $total_weight . '%');
    }

    // Insert template
    $stmt = $conn->prepare("INSERT INTO evaluation_templates (template_name, description, target_position, status, created_by) VALUES (?, ?, ?, 'Active', ?)");
    $stmt->bind_param("sssi", $template_name, $description, $target_position, $_SESSION['user_id']);
    $stmt->execute();
    $template_id = $stmt->insert_id;
    $stmt->close();

    // Insert criteria
    $crit_stmt = $conn->prepare("INSERT INTO evaluation_criteria (template_id, criterion_name, description, weight, scoring_method, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
    for ($i = 0; $i < count($criteria_names); $i++) {
        $name = trim($criteria_names[$i]);
        $desc = trim($criteria_descriptions[$i] ?? '');
        $weight = floatval($criteria_weights[$i]);
        $method = $criteria_methods[$i] ?? 'Scale_1_5';
        $order = $i + 1;
        if (!empty($name)) {
            $crit_stmt->bind_param("issdsi", $template_id, $name, $desc, $weight, $method, $order);
            $crit_stmt->execute();
        }
    }
    $crit_stmt->close();

    logAudit($conn, $_SESSION['user_id'], 'CREATE', 'Template', $template_id, "Created evaluation template: $template_name");
    redirectWith(BASE_URL . '/manager/templates.php', 'success', "Template '$template_name' created successfully.");
}

require_once '../includes/header.php';
?>

<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-plus-circle me-2"></i>Create Evaluation Template</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="templateForm">
            <!-- Template Info -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Template Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="template_name" required placeholder="e.g., Quarterly Performance Review">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Target Position (optional)</label>
                    <input type="text" class="form-control" name="target_position" placeholder="e.g., All Positions">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="2" placeholder="Brief description of this template..."></textarea>
                </div>
            </div>

            <hr>

            <!-- Criteria Builder -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0"><i class="fas fa-list me-2"></i>Evaluation Criteria</h6>
                <button type="button" class="btn btn-sm btn-success" onclick="addCriterion()">
                    <i class="fas fa-plus me-1"></i>Add Criterion
                </button>
            </div>

            <div id="criteriaContainer">
                <!-- Criteria rows inserted here by JS -->
            </div>

            <!-- Weight Total -->
            <div class="row mt-3 mb-4">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-3 p-3 rounded" id="weightStatus" style="background:#f0f0f0;">
                        <strong>Total Weight:</strong>
                        <span id="totalWeight" class="badge bg-warning text-dark" style="font-size:1rem;">0%</span>
                        <small id="weightMessage" class="text-danger">Must equal 100%</small>
                    </div>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="<?php echo BASE_URL; ?>/manager/templates.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                    <i class="fas fa-save me-2"></i>Create Template
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let criterionCount = 0;

function addCriterion() {
    criterionCount++;
    const container = document.getElementById('criteriaContainer');
    const html = `
        <div class="row align-items-end mb-3 p-3 rounded" style="background:#f8f9fa;" id="criterion_${criterionCount}">
            <div class="col-md-3 mb-2">
                <label class="form-label">Criterion Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="criterion_name[]" required placeholder="e.g., Job Knowledge">
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Description</label>
                <input type="text" class="form-control" name="criterion_description[]" placeholder="Brief description">
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Weight (%) <span class="text-danger">*</span></label>
                <input type="number" class="form-control weight-input" name="criterion_weight[]" required min="1" max="100" step="0.01" placeholder="25" onchange="updateTotalWeight()" oninput="updateTotalWeight()">
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Scoring Method</label>
                <select class="form-select" name="criterion_method[]">
                    <option value="Scale_1_5">Scale 1-5</option>
                    <option value="Scale_1_10">Scale 1-10</option>
                    <option value="Percentage">Percentage 0-100</option>
                </select>
            </div>
            <div class="col-md-1 mb-2">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCriterion(${criterionCount})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    updateTotalWeight();
}

function removeCriterion(id) {
    document.getElementById('criterion_' + id).remove();
    updateTotalWeight();
}

function updateTotalWeight() {
    let total = 0;
    document.querySelectorAll('.weight-input').forEach(input => {
        total += parseFloat(input.value) || 0;
    });

    const badge = document.getElementById('totalWeight');
    const message = document.getElementById('weightMessage');
    const submitBtn = document.getElementById('submitBtn');

    badge.textContent = total.toFixed(1) + '%';

    if (Math.abs(total - 100) < 0.01) {
        badge.className = 'badge bg-success';
        badge.style.fontSize = '1rem';
        message.textContent = '✓ Perfect!';
        message.className = 'text-success';
        submitBtn.disabled = false;
    } else {
        badge.className = 'badge bg-warning text-dark';
        badge.style.fontSize = '1rem';
        message.textContent = total > 100 ? 'Exceeds 100%!' : 'Must equal 100%';
        message.className = 'text-danger';
        submitBtn.disabled = true;
    }
}

// Add initial criterion
addCriterion();
</script>

<?php require_once '../includes/footer.php'; ?>
