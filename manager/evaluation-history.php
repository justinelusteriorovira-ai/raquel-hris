<?php
$page_title = 'Evaluation History';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Fetch evaluation history
$history = $conn->query("SELECT ev.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.job_title, d.department_name,
    u.full_name as submitted_by_name, u2.full_name as endorsed_by_name, u3.full_name as approved_by_name, et.template_name
    FROM evaluations ev
    LEFT JOIN employees e ON ev.employee_id = e.employee_id
    LEFT JOIN departments d ON e.department_id = d.department_id
    LEFT JOIN users u ON ev.submitted_by = u.user_id
    LEFT JOIN users u2 ON ev.endorsed_by = u2.user_id
    LEFT JOIN users u3 ON ev.approved_by = u3.user_id
    LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id
    WHERE ev.status IN ('Approved', 'Rejected', 'Returned')
    ORDER BY ev.updated_at DESC");

$total_c = 0;
$approved_c = 0;
$rejected_c = 0;
$returned_c = 0;

$all_history = [];
while ($row = $history->fetch_assoc()) {
    $all_history[] = $row;
    $total_c++;
    if ($row['status'] === 'Approved') $approved_c++;
    elseif ($row['status'] === 'Rejected') $rejected_c++;
    elseif ($row['status'] === 'Returned') $returned_c++;
}
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="content-card text-center p-3 h-100 border-start border-4 border-primary">
            <div class="display-6 fw-bold text-primary"><?php echo $total_c; ?></div>
            <div class="text-muted small fw-bold">Total Evaluations</div>
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
            <div class="display-6 fw-bold text-danger"><?php echo $rejected_c; ?></div>
            <div class="text-muted small fw-bold">Rejected</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="content-card text-center p-3 h-100 border-start border-4 border-warning">
            <div class="display-6 fw-bold text-warning"><?php echo $returned_c; ?></div>
            <div class="text-muted small fw-bold">Returned</div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <h5 class="mb-0 me-3"><i class="fas fa-history me-2 text-primary"></i>Evaluation History</h5>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-primary active" onclick="filterByStatus('All', this)">All</button>
                <button type="button" class="btn btn-outline-success" onclick="filterByStatus('Approved', this)">Approved</button>
                <button type="button" class="btn btn-outline-danger" onclick="filterByStatus('Rejected', this)">Rejected</button>
                <button type="button" class="btn btn-outline-warning" onclick="filterByStatus('Returned', this)">Returned</button>
            </div>
        </div>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="form-control form-control-sm" id="customSearchEval" placeholder="Search employee or dept...">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="evalTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3" style="cursor: pointer;" onclick="sortTable(0)">Employee <i class="fas fa-sort text-muted ms-1 small"></i></th>
                        <th style="cursor: pointer;" onclick="sortTable(1)">Department <i class="fas fa-sort text-muted ms-1 small"></i></th>
                        <th style="cursor: pointer;" onclick="sortTable(3)">Date <i class="fas fa-sort text-muted ms-1 small"></i></th>
                        <th style="cursor: pointer;" onclick="sortTable(4)">Score <i class="fas fa-sort text-muted ms-1 small"></i></th>
                        <th style="cursor: pointer;" onclick="sortTable(6)">Status <i class="fas fa-sort text-muted ms-1 small"></i></th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($all_history)): ?>
                        <tr class="no-results-row text-center"><td colspan="6" class="text-muted py-5"><i class="fas fa-history fa-3x mb-3 d-block opacity-25"></i>No evaluation history found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($all_history as $row): ?>
                            <tr data-status="<?php echo $row['status']; ?>">
                                <td class="ps-3">
                                    <div class="fw-bold"><?php echo e($row['employee_name']); ?></div>
                                    <div class="text-muted x-small"><?php echo e($row['job_title']); ?></div>
                                </td>
                                <td><div class="small fw-bold text-dark"><?php echo e($row['department_name'] ?? 'N/A'); ?></div><div class="x-small text-muted"><?php echo e($row['template_name']); ?></div></td>
                                <td><small><?php echo formatDate($row['updated_at']); ?></small></td>
                                <td>
                                    <strong><?php echo $row['total_score'] ?? '0.00'; ?>%</strong>
                                    <div class="text-muted" style="font-size:0.65rem;"><?php echo e($row['performance_level']); ?></div>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'bg-secondary';
                                    if ($row['status'] === 'Approved') $statusClass = 'bg-success';
                                    if ($row['status'] === 'Rejected') $statusClass = 'bg-danger';
                                    if ($row['status'] === 'Returned') $statusClass = 'bg-warning';
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?> rounded-pill px-2" style="font-size:0.7rem;"><?php echo e($row['status']); ?></span>
                                </td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reviewModal<?php echo $row['evaluation_id']; ?>">
                                        <i class="fas fa-eye me-1"></i>View
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination Controls -->
        <div class="d-flex justify-content-between align-items-center p-3 border-top" id="paginationWrapper">
            <div id="paginationInfo" class="text-muted small"></div>
            <ul class="pagination pagination-sm mb-0" id="paginationNumbers">
            </ul>
        </div>
    </div>
</div>

<?php 
foreach ($all_history as $row): 
    $status = $row['status'];
?>
    <div class="modal fade" id="reviewModal<?php echo $row['evaluation_id']; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Evaluation Details - <?php echo e($row['employee_name']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pb-0">
                    <div class="status-stepper d-flex justify-content-between mb-4 position-relative">
                        <div class="stepper-line"></div>
                        <?php
                        $steps = [
                            ['label' => 'Submitted', 'active' => true, 'icon' => 'fa-paper-plane'],
                            ['label' => 'Supervisor', 'active' => true, 'icon' => 'fa-user-tie'],
                            ['label' => 'Manager', 'active' => true, 'icon' => 'fa-user-shield'],
                            ['label' => 'Finalized', 'active' => ($status === 'Approved'), 'icon' => 'fa-check-circle']
                        ];
                        if ($status === 'Rejected') $steps[3] = ['label' => 'Rejected', 'active' => true, 'icon' => 'fa-times-circle', 'class' => 'text-danger'];
                        if ($status === 'Returned') $steps[3] = ['label' => 'Returned', 'active' => true, 'icon' => 'fa-undo', 'class' => 'text-warning'];
                        
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

                    <div class="alert <?php echo $status === 'Approved' ? 'alert-success' : ($status === 'Rejected' ? 'alert-danger' : 'alert-warning'); ?> py-2 small d-flex align-items-center">
                        <i class="fas <?php echo $status === 'Approved' ? 'fa-check-circle' : ($status === 'Rejected' ? 'fa-times-circle' : 'fa-exclamation-circle'); ?> me-2"></i>
                        <span>Historical Status: <strong><?php echo $status; ?></strong></span>
                    </div>

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

                    <div class="d-flex justify-content-end mb-3 gap-2 d-print-none">
                        <a href="print-evaluation.php?id=<?php echo $row['evaluation_id']; ?>" target="_blank" class="btn btn-sm btn-primary">
                            <i class="fas fa-print me-1"></i>Print Official Form (HRD-013)
                        </a>
                    </div>

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
                                $kra_q = $conn->query("SELECT es.*, ec.criterion_name, ec.description, ec.weight FROM evaluation_scores es JOIN evaluation_criteria ec ON es.criterion_id = ec.criterion_id WHERE es.evaluation_id = {$row['evaluation_id']} AND ec.section = 'KRA' ORDER BY ec.sort_order");
                                while ($k = $kra_q->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td class="small"><strong><?php echo e($k['criterion_name']); ?></strong><?php if($k['description']): ?><br><span class="text-muted"><?php echo e($k['description']); ?></span><?php endif; ?></td>
                                        <td class="text-center small"><?php echo $k['weight']; ?>%</td>
                                        <td class="text-center small"><?php echo $k['score_value']; ?></td>
                                        <td class="text-center small fw-bold text-primary"><?php echo $k['weighted_score']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr class="bg-light fw-bold small">
                                    <td class="text-end">SUB TOTAL</td>
                                    <td class="text-center">100%</td>
                                    <td></td>
                                    <td class="text-center text-primary"><?php echo $row['kra_subtotal']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

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
                                $beh_q = $conn->query("SELECT es.*, ec.criterion_name, ec.kpi_description FROM evaluation_scores es JOIN evaluation_criteria ec ON es.criterion_id = ec.criterion_id WHERE es.evaluation_id = {$row['evaluation_id']} AND ec.section = 'Behavior' ORDER BY ec.sort_order");
                                while ($b = $beh_q->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td class="small"><strong><?php echo e($b['criterion_name']); ?></strong><br><span class="text-muted"><?php echo e($b['kpi_description']); ?></span></td>
                                        <td class="text-center small fw-bold text-primary"><?php echo $b['score_value']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr class="bg-light fw-bold small">
                                    <td class="text-end">AVERAGE</td>
                                    <td class="text-center text-primary"><?php echo $row['behavior_average']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

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
                                            <td class="fw-bold"><?php echo $row['kra_subtotal']; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">II. Behavior and Values</td>
                                            <td>20%</td>
                                            <td class="fw-bold"><?php echo $row['behavior_average']; ?></td>
                                        </tr>
                                        <tr class="table-active fw-bold">
                                            <td class="text-end">TOTAL SCORE</td>
                                            <td>100%</td>
                                            <td class="text-primary"><?php echo $row['total_score']; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-lg-4 text-center d-flex flex-column justify-content-center">
                            <div class="p-2 border rounded bg-white">
                                <div class="small text-uppercase text-muted mb-1">Performance Level</div>
                                <span class="badge <?php echo getPerformanceBadgeClass($row['performance_level']); ?> px-3 py-2" style="font-size:0.9rem;"><?php echo e($row['performance_level'] ?? 'N/A'); ?></span>
                            </div>
                        </div>
                    </div>

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
                                    <td><?php echo e($row['current_position'] ?? 'N/A'); ?></td>
                                    <td class="text-center"><?php echo e($row['months_in_position'] ?? '0'); ?></td>
                                    <td class="fw-bold text-primary"><?php echo e($row['desired_position'] ?? 'N/A'); ?></td>
                                    <td><?php echo $row['target_date'] ? formatDate($row['target_date']) : 'N/A'; ?></td>
                                </tr>
                                <?php if(!empty($row['career_growth_details'])): ?>
                                <tr>
                                    <td colspan="4" class="bg-light italic"><strong>Admin/Manager Notes:</strong> <?php echo e($row['career_growth_details']); ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

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
                                $dev_q = $conn->query("SELECT * FROM evaluation_dev_plans WHERE evaluation_id = {$row['evaluation_id']} ORDER BY sort_order");
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

                    <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4 text-secondary small"><i class="fas fa-comments me-2"></i>VI. Comments & Signatures</h6>
                    <div class="row">
                        <?php if($row['staff_comments']): ?>
                        <div class="col-md-4 mb-3">
                            <strong class="small text-uppercase text-muted">Employee:</strong>
                            <div class="bg-light p-2 rounded small border mt-1" style="min-height:60px;"><?php echo nl2br(e($row['staff_comments'])); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if($row['supervisor_comments']): ?>
                        <div class="col-md-4 mb-3">
                            <strong class="small text-uppercase text-muted">Supervisor:</strong>
                            <div class="bg-light p-2 rounded small border mt-1" style="min-height:60px;"><?php echo nl2br(e($row['supervisor_comments'])); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if($row['manager_comments']): ?>
                        <div class="col-md-4 mb-3">
                            <strong class="small text-uppercase text-muted">Manager:</strong>
                            <div class="bg-light p-2 rounded small border mt-1" style="min-height:60px;"><?php echo nl2br(e($row['manager_comments'])); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer d-print-none">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
let sortDirection = false;
let currentSortColumn = -1;
let currentPage = 1;
const ITEMS_PER_PAGE = 10;

function filterByStatus(status, btn) {
    const buttons = btn.parentElement.querySelectorAll('.btn');
    buttons.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const rows = document.querySelectorAll('#evalTable tbody tr:not(.no-results-row)');
    rows.forEach(row => {
        if (status === 'All') {
            row.setAttribute('data-visible-filter', 'true');
        } else {
            const rowStatus = row.getAttribute('data-status');
            row.setAttribute('data-visible-filter', (rowStatus === status) ? 'true' : 'false');
        }
    });

    currentPage = 1;
    renderTable();
}

function sortTable(columnIndex) {
    if (currentSortColumn === columnIndex) {
        sortDirection = !sortDirection;
    } else {
        currentSortColumn = columnIndex;
        sortDirection = false;
    }
    
    const ths = document.querySelectorAll("#evalTable thead th");
    ths.forEach((th, idx) => {
        const icon = th.querySelector("i.fas");
        if (icon) {
            if (idx === columnIndex) {
                icon.className = sortDirection ? "fas fa-sort-up ms-1" : "fas fa-sort-down ms-1";
                icon.classList.remove("text-muted");
                icon.classList.add("text-primary");
            } else {
                icon.className = "fas fa-sort text-muted ms-1";
                icon.classList.remove("text-primary");
            }
        }
    });

    renderTable();
}

document.getElementById('customSearchEval')?.addEventListener('input', function() {
    currentPage = 1;
    renderTable();
});

function goToPage(page) {
    currentPage = page;
    renderTable();
}

function renderTable() {
    const tbody = document.querySelector("#evalTable tbody");
    if(!tbody) return;
    const allRows = Array.from(tbody.querySelectorAll("tr:not(.no-results-row)"));
    const searchInput = document.getElementById('customSearchEval');
    const filterInput = searchInput ? searchInput.value.toLowerCase().trim() : '';
    
    let visibleRows = [];
    
    allRows.forEach(row => {
        const isFilterVisible = row.getAttribute('data-visible-filter') !== 'false';
        const cells = Array.from(row.querySelectorAll("td"));
        
        if (cells.length > 1) {
            const rowText = cells.slice(0, 5).map(td => td.textContent.trim().replace(/\s+/g, ' ')).join(' ').toLowerCase();
            if (isFilterVisible && (filterInput === "" || rowText.includes(filterInput))) {
                visibleRows.push(row);
                row.classList.remove('filtered-out');
            } else {
                row.classList.add('filtered-out');
                row.style.display = "none";
            }
        }
    });

    if (currentSortColumn !== -1) {
        visibleRows.sort((a, b) => {
            let valA = a.querySelectorAll("td")[currentSortColumn].textContent.trim();
            let valB = b.querySelectorAll("td")[currentSortColumn].textContent.trim();
            
            if (currentSortColumn === 2) {
                valA = new Date(valA).getTime() || valA;
                valB = new Date(valB).getTime() || valB;
            }
            if (currentSortColumn === 3) {
                valA = parseFloat(valA.replace('%', '')) || 0;
                valB = parseFloat(valB.replace('%', '')) || 0;
            }

            if (valA < valB) return sortDirection ? -1 : 1;
            if (valA > valB) return sortDirection ? 1 : -1;
            return 0;
        });
        
        visibleRows.forEach(row => tbody.appendChild(row));
    }

    const totalPages = Math.ceil(visibleRows.length / ITEMS_PER_PAGE);
    if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const startIdx = (currentPage - 1) * ITEMS_PER_PAGE;
    const endIdx = startIdx + ITEMS_PER_PAGE;

    visibleRows.forEach((row, index) => {
        if (index >= startIdx && index < endIdx) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });

    updatePaginationUI(visibleRows.length, totalPages);
    handleNoResults(visibleRows.length, filterInput, tbody);
}

function updatePaginationUI(totalItems, totalPages) {
    const info = document.getElementById("paginationInfo");
    const digits = document.getElementById("paginationNumbers");
    if (!info || !digits) return;
    
    if (totalItems === 0) {
        info.innerHTML = "Showing 0 entries";
        digits.innerHTML = "";
        return;
    }
    
    const start = (currentPage - 1) * ITEMS_PER_PAGE + 1;
    const end = Math.min(currentPage * ITEMS_PER_PAGE, totalItems);
    info.innerHTML = `Showing ${start} to ${end} of ${totalItems} entries`;
    
    let html = "";
    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><button class="page-link" onclick="goToPage(${currentPage - 1})">Previous</button></li>`;
             
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, startPage + 4);
    if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);
    
    if (startPage > 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    for (let i = startPage; i <= endPage; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><button class="page-link" onclick="goToPage(${i})">${i}</button></li>`;
    }
    if (endPage < totalPages) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    
    html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><button class="page-link" onclick="goToPage(${currentPage + 1})">Next</button></li>`;
    digits.innerHTML = html;
}

function handleNoResults(totalItems, filterInput, tbody) {
    let noResultsRow = tbody.querySelector('.no-results-row.search-empty');
    if (totalItems === 0 && filterInput !== "") {
        if (!noResultsRow) {
            noResultsRow = document.createElement('tr');
            noResultsRow.className = 'no-results-row search-empty text-center';
            tbody.appendChild(noResultsRow);
        }
        noResultsRow.innerHTML = `<td colspan="6" class="py-4 text-muted"><i class="fas fa-search fa-2x mb-3 d-block"></i>No evaluations found matching "<strong>${filterInput}</strong>"</td>`;
        noResultsRow.style.display = '';
        const origNoResults = tbody.querySelector('.no-results-row:not(.search-empty)');
        if(origNoResults) origNoResults.style.display = 'none';
    } else if (noResultsRow) {
        noResultsRow.remove();
    }
}

document.addEventListener("DOMContentLoaded", renderTable);
</script>

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
@media print {
    body * {
        visibility: hidden;
    }
    .content-card, .content-card * {
        visibility: visible;
    }
    .content-card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        box-shadow: none;
        border: none;
    }
    .search-box, .btn, #paginationWrapper, th:last-child, td:last-child {
        display: none !important;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>
