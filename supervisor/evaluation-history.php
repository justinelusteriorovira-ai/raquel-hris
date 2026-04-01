<?php
$page_title = 'Evaluation History';
require_once '../includes/session-check.php';
checkRole(['HR Supervisor']);
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Fetch evaluation history
$history = $conn->query("SELECT ev.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.job_title, e.department,
    u.full_name as submitted_by_name, u2.full_name as endorsed_by_name, u3.full_name as approved_by_name, et.template_name
    FROM evaluations ev
    LEFT JOIN employees e ON ev.employee_id = e.employee_id
    LEFT JOIN users u ON ev.submitted_by = u.user_id
    LEFT JOIN users u2 ON ev.endorsed_by = u2.user_id
    LEFT JOIN users u3 ON ev.approved_by = u3.user_id
    LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id
    WHERE ev.status IN ('Approved', 'Rejected', 'Returned')
    ORDER BY ev.updated_at DESC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Access the complete history of all employee evaluations. Review past assessments across departments.</p>
    <div>
        <button class="btn btn-outline-primary" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Print / Export
        </button>
    </div>
</div>

<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-history me-2"></i>Evaluation History</h5>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="form-control form-control-sm" id="customSearchEval" placeholder="Search evaluations...">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover" id="evalTable">
                <thead>
                    <tr>
                        <th style="cursor: pointer;" onclick="sortTable(0)">Employee <i class="fas fa-sort text-muted ms-1" style="font-size: 0.8rem;"></i></th>
                        <th style="cursor: pointer;" onclick="sortTable(1)">Department <i class="fas fa-sort text-muted ms-1" style="font-size: 0.8rem;"></i></th>
                        <th style="cursor: pointer;" onclick="sortTable(2)">Template <i class="fas fa-sort text-muted ms-1" style="font-size: 0.8rem;"></i></th>
                        <th style="cursor: pointer;" onclick="sortTable(3)">Date <i class="fas fa-sort text-muted ms-1" style="font-size: 0.8rem;"></i></th>
                        <th style="cursor: pointer;" onclick="sortTable(4)">Score <i class="fas fa-sort text-muted ms-1" style="font-size: 0.8rem;"></i></th>
                        <th style="cursor: pointer;" onclick="sortTable(5)">Level <i class="fas fa-sort text-muted ms-1" style="font-size: 0.8rem;"></i></th>
                        <th style="cursor: pointer;" onclick="sortTable(6)">Status <i class="fas fa-sort text-muted ms-1" style="font-size: 0.8rem;"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($history->num_rows === 0): ?>
                        <tr class="no-results-row text-center"><td colspan="8" class="text-muted py-4">No historical evaluations found.</td></tr>
                    <?php else: ?>
                        <?php while ($row = $history->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo e($row['employee_name']); ?></strong><br><small class="text-muted"><?php echo e($row['job_title']); ?></small></td>
                                <td><?php echo e($row['department']); ?></td>
                                <td><small><?php echo e($row['template_name']); ?></small></td>
                                <td><small><?php echo formatDate($row['updated_at']); ?></small></td>
                                <td><strong><?php echo $row['total_score'] ?? '0.00'; ?>%</strong></td>
                                <td>
                                    <?php if ($row['performance_level']): ?>
                                        <span class="badge <?php echo getPerformanceBadgeClass($row['performance_level']); ?>"><?php echo e($row['performance_level']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'bg-secondary';
                                    if ($row['status'] === 'Approved') $statusClass = 'bg-success';
                                    if ($row['status'] === 'Rejected') $statusClass = 'bg-danger';
                                    if ($row['status'] === 'Returned') $statusClass = 'bg-warning';
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo e($row['status']); ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reviewModal<?php echo $row['evaluation_id']; ?>" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Review Modal -->
                            <div class="modal fade" id="reviewModal<?php echo $row['evaluation_id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Evaluation Details - <?php echo e($row['employee_name']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row border-bottom pb-3 mb-3">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Employee:</strong> <?php echo e($row['employee_name']); ?></p>
                                                    <p class="mb-1"><strong>Position:</strong> <?php echo e($row['job_title']); ?> (<?php echo e($row['department']); ?>)</p>
                                                    <p class="mb-1"><strong>Template:</strong> <?php echo e($row['template_name']); ?></p>
                                                    <p class="mb-1"><strong>Period:</strong> <?php echo $row['evaluation_period_start'] ? formatDate($row['evaluation_period_start']) : 'N/A'; ?> to <?php echo $row['evaluation_period_end'] ? formatDate($row['evaluation_period_end']) : 'N/A'; ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Status:</strong> <span class="badge <?php echo $statusClass; ?>"><?php echo e($row['status']); ?></span></p>
                                                    <p class="mb-1"><strong>Submitted By:</strong> <?php echo e($row['submitted_by_name'] ?? 'N/A'); ?> <small class="text-muted">(<?php echo $row['submitted_date'] ? formatDate($row['submitted_date']) : 'N/A'; ?>)</small></p>
                                                    <?php if($row['endorsed_by_name']): ?>
                                                    <p class="mb-1"><strong>Endorsed By:</strong> <?php echo e($row['endorsed_by_name']); ?> <small class="text-muted">(<?php echo $row['endorsed_date'] ? formatDate($row['endorsed_date']) : 'N/A'; ?>)</small></p>
                                                    <?php endif; ?>
                                                    <?php if($row['approved_by_name']): ?>
                                                    <p class="mb-1"><strong>Processed By:</strong> <?php echo e($row['approved_by_name']); ?> <small class="text-muted">(<?php echo $row['approved_date'] ? formatDate($row['approved_date']) : 'N/A'; ?>)</small></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <?php if ($row['total_score']): ?>
                                            <div class="score-display mb-3 mx-auto" style="max-width:300px;">
                                                <div class="score-value"><?php echo $row['total_score']; ?>%</div>
                                                <span class="badge <?php echo getPerformanceBadgeClass($row['performance_level']); ?>" style="font-size:1rem;"><?php echo e($row['performance_level']); ?></span>
                                            </div>
                                            <?php endif; ?>

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
                                                            if ($scores_q->num_rows > 0):
                                                                while ($score = $scores_q->fetch_assoc()):
                                                                    $max_score = 5;
                                                                    if ($score['scoring_method'] === 'Scale_1_10') $max_score = 10;
                                                                    elseif ($score['scoring_method'] === 'Percentage') $max_score = 100;
                                                                    
                                                                    $weight_display = 0;
                                                                    if ($score['score_value'] > 0) {
                                                                        $weight_display = ($score['weighted_score'] / ($score['score_value'] / $max_score));
                                                                    }
                                                            ?>
                                                                <tr>
                                                                    <td><?php echo e($score['criterion_name']); ?></td>
                                                                    <td class="text-center"><?php echo number_format($weight_display, 0); ?>%</td>
                                                                    <td class="text-center"><?php echo $score['score_value']; ?> / <?php echo $max_score; ?></td>
                                                                    <td class="text-center text-primary"><strong><?php echo $score['weighted_score']; ?>%</strong></td>
                                                                </tr>
                                                                <?php endwhile; ?>
                                                            <?php else: ?>
                                                                <tr><td colspan="4" class="text-center text-muted">No detailed scores available.</td></tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <?php if ($row['staff_comments']): ?>
                                                <div class="mb-3">
                                                    <strong>Staff Comments:</strong>
                                                    <p class="bg-light p-2 rounded small"><?php echo nl2br(e($row['staff_comments'])); ?></p>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($row['supervisor_comments']): ?>
                                                <div class="mb-3">
                                                    <strong>Supervisor Comments:</strong>
                                                    <p class="bg-light p-2 rounded small"><?php echo nl2br(e($row['supervisor_comments'])); ?></p>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($row['manager_comments']): ?>
                                                <div class="mb-3">
                                                    <strong>Manager Comments:</strong>
                                                    <p class="bg-light p-2 rounded small"><?php echo nl2br(e($row['manager_comments'])); ?></p>
                                                </div>
                                            <?php endif; ?>

                                        </div>
                                        <div class="modal-footer d-print-none">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
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

<script>
// State Variables
let sortDirection = false;
let currentSortColumn = -1;
let currentPage = 1;
const ITEMS_PER_PAGE = 10;

function sortTable(columnIndex) {
    if (currentSortColumn === columnIndex) {
        sortDirection = !sortDirection;
    } else {
        currentSortColumn = columnIndex;
        sortDirection = false;
    }
    
    // Update icons
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
    
    // 1. Filter
    allRows.forEach(row => {
        const cells = Array.from(row.querySelectorAll("td"));
        if (cells.length > 1) {
            const rowText = cells.slice(0, 7).map(td => td.textContent.trim().replace(/\s+/g, ' ')).join(' ').toLowerCase();
            if (filterInput === "" || rowText.includes(filterInput)) {
                visibleRows.push(row);
                row.classList.remove('filtered-out');
            } else {
                row.classList.add('filtered-out');
                row.style.display = "none";
            }
        }
    });

    // 2. Sort
    if (currentSortColumn !== -1) {
        visibleRows.sort((a, b) => {
            let valA = a.querySelectorAll("td")[currentSortColumn].textContent.trim();
            let valB = b.querySelectorAll("td")[currentSortColumn].textContent.trim();
            
            if (currentSortColumn === 3) { // Date
                valA = new Date(valA).getTime() || valA;
                valB = new Date(valB).getTime() || valB;
            }
            if (currentSortColumn === 4) { // Score
                valA = parseFloat(valA.replace('%', '')) || 0;
                valB = parseFloat(valB.replace('%', '')) || 0;
            }

            if (valA < valB) return sortDirection ? -1 : 1;
            if (valA > valB) return sortDirection ? 1 : -1;
            return 0;
        });
        
        visibleRows.forEach(row => tbody.appendChild(row));
    }

    // 3. Paginate
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
    
    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <button class="page-link" onclick="goToPage(${currentPage - 1})">Previous</button>
             </li>`;
             
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, startPage + 4);
    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }
    
    if (startPage > 1) {
        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }
    for (let i = startPage; i <= endPage; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                    <button class="page-link" onclick="goToPage(${i})">${i}</button>
                 </li>`;
    }
    if (endPage < totalPages) {
        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }
    
    html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <button class="page-link" onclick="goToPage(${currentPage + 1})">Next</button>
             </li>`;
             
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
        noResultsRow.innerHTML = `<td colspan="8" class="py-4 text-muted"><i class="fas fa-search fa-2x mb-3 d-block"></i>No evaluations found matching "<strong>${filterInput}</strong>"</td>`;
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
