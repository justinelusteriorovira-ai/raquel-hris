<?php
$page_title = 'Employees';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/functions.php';

// Handle activate/deactivate
if (isset($_GET['deactivate']) && is_numeric($_GET['deactivate'])) {
    $eid = (int)$_GET['deactivate'];
    $conn->query("UPDATE employees SET is_active = 0 WHERE employee_id = $eid");
    logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'Employee', $eid, 'Deactivated employee');
    redirectWith(BASE_URL . '/manager/employees.php', 'success', 'Employee deactivated successfully.');
}
if (isset($_GET['activate']) && is_numeric($_GET['activate'])) {
    $eid = (int)$_GET['activate'];
    $conn->query("UPDATE employees SET is_active = 1 WHERE employee_id = $eid");
    logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'Employee', $eid, 'Reactivated employee');
    redirectWith(BASE_URL . '/manager/employees.php', 'success', 'Employee reactivated successfully.');
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $eid = (int)$_GET['delete'];
    // Delete the employee and all normalized sub-tables
    $tables = [
        'employee_details', 'employee_government_ids', 'employee_addresses', 
        'employee_contacts', 'employee_emergency_contacts', 'employee_disclosures', 
        'employee_family', 'employee_children', 'employee_siblings', 
        'employee_education', 'employee_work_experience', 'employee_trainings', 
        'employee_voluntary_work', 'employee_eligibility', 'employee_skills', 
        'employee_recognitions', 'employee_memberships', 'employee_real_properties', 
        'employee_personal_properties', 'employee_liabilities', 'employee_references'
    ];
    foreach ($tables as $tbl) {
        $conn->query("DELETE FROM $tbl WHERE employee_id = $eid");
    }
    
    // Delete career movements and evaluations (Evaluations have their own score sub-deletion)
    $conn->query("DELETE FROM career_movements WHERE employee_id = $eid");
    
    $conn->query("DELETE FROM employees WHERE employee_id = $eid");
    logAudit($conn, $_SESSION['user_id'], 'DELETE', 'Employee', $eid, 'Permanently deleted employee');
    redirectWith(BASE_URL . '/manager/employees.php', 'success', 'Employee deleted permanently.');
}

require_once '../includes/header.php';

// Fetch employees
$employees = $conn->query("
    SELECT e.*, b.branch_name, d.department_name 
    FROM employees e 
    LEFT JOIN branches b ON e.branch_id = b.branch_id 
    LEFT JOIN departments d ON e.department_id = d.department_id 
    ORDER BY e.last_name, e.first_name
");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Manage employee records</p>
    <a href="<?php echo BASE_URL; ?>/manager/add-employee.php" class="btn btn-primary">
        <i class="fas fa-user-plus me-2"></i>Add Employee
    </a>
</div>

<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-users me-2"></i>All Employees</h5>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="form-control form-control-sm" id="customSearchEmp" placeholder="Search employees...">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover" id="empTable">
                <thead>
                    <tr>
                        <th style="cursor: pointer;" onclick="sortTable(0)">Name <i class="fas fa-sort text-muted ms-1" style="font-size: 0.8rem;"></i></th>
                        <th style="cursor: pointer;" onclick="sortTable(1)">Job Title <i class="fas fa-sort text-muted ms-1" style="font-size: 0.8rem;"></i></th>
                        <th style="cursor: pointer;" onclick="sortTable(2)">Department <i class="fas fa-sort text-muted ms-1" style="font-size: 0.8rem;"></i></th>
                        <th style="cursor: pointer;" onclick="sortTable(3)">Branch <i class="fas fa-sort text-muted ms-1" style="font-size: 0.8rem;"></i></th>
                        <th style="cursor: pointer;" onclick="sortTable(4)">Status <i class="fas fa-sort text-muted ms-1" style="font-size: 0.8rem;"></i></th>
                        <th style="cursor: pointer;" onclick="sortTable(5)">Hire Date <i class="fas fa-sort text-muted ms-1" style="font-size: 0.8rem;"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($emp = $employees->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($emp['profile_picture']) && file_exists('../assets/img/employees/' . $emp['profile_picture'])): ?>
                                        <img src="<?php echo BASE_URL; ?>/assets/img/employees/<?php echo e($emp['profile_picture']); ?>" alt="Profile" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle me-2 text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: var(--primary-light); font-size: 0.8rem; font-weight: bold;">
                                            <?php echo strtoupper(substr($emp['first_name'], 0, 1) . substr($emp['last_name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <strong><?php echo e($emp['last_name'] . ', ' . $emp['first_name']); ?></strong>
                                </div>
                            </td>
                            <td><?php echo e($emp['job_title']); ?></td>
                            <td><?php echo e($emp['department_name'] ?? 'N/A'); ?></td>
                            <td><?php echo e($emp['branch_name'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge <?php echo $emp['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $emp['employment_status']; ?>
                                </span>
                            </td>
                            <td><small><?php echo formatDate($emp['hire_date']); ?></small></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/manager/view-employee.php?id=<?php echo $emp['employee_id']; ?>" class="btn btn-sm btn-outline-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/manager/edit-employee.php?id=<?php echo $emp['employee_id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($emp['is_active']): ?>
                                    <button type="button" class="btn btn-sm btn-outline-warning" title="Deactivate"
                                            onclick="setDeactivateTarget(<?php echo $emp['employee_id']; ?>, '<?php echo e(addslashes($emp['first_name'] . ' ' . $emp['last_name'])); ?>')"
                                            data-bs-toggle="modal" data-bs-target="#deactivateModal">
                                        <i class="fas fa-user-slash"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-outline-success" title="Activate"
                                            onclick="setActivateTarget(<?php echo $emp['employee_id']; ?>, '<?php echo e(addslashes($emp['first_name'] . ' ' . $emp['last_name'])); ?>')"
                                            data-bs-toggle="modal" data-bs-target="#activateModal">
                                        <i class="fas fa-user-check"></i>
                                    </button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-outline-danger" title="Delete Permanently"
                                        onclick="setDeleteTarget(<?php echo $emp['employee_id']; ?>, '<?php echo e(addslashes($emp['first_name'] . ' ' . $emp['last_name'])); ?>')"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
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

<?php require_once '../includes/footer.php'; ?>

<!-- Deactivate Confirmation Modal -->
<div class="modal fade" id="deactivateModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-user-slash me-2"></i>Deactivate Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>Deactivate <strong id="deactivateEmpName"></strong>?</p>
                <p class="text-muted small">This will mark them as inactive. You can reactivate later.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="deactivateConfirmBtn" class="btn btn-warning"><i class="fas fa-user-slash me-1"></i>Deactivate</a>
            </div>
        </div>
    </div>
</div>

<!-- Activate Confirmation Modal -->
<div class="modal fade" id="activateModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-user-check me-2"></i>Activate Employee</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>Reactivate <strong id="activateEmpName"></strong>?</p>
                <p class="text-muted small">This will mark them as an active employee again.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="activateConfirmBtn" class="btn btn-success"><i class="fas fa-user-check me-1"></i>Activate</a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Delete Employee</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>Permanently delete <strong id="deleteEmpName"></strong>?</p>
                <p class="text-danger small"><i class="fas fa-exclamation-circle me-1"></i>This will remove all their records including evaluations. This cannot be undone!</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="deleteConfirmBtn" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Delete Permanently</a>
            </div>
        </div>
    </div>
</div>

<script>
function setDeactivateTarget(id, name) {
    document.getElementById('deactivateEmpName').textContent = name;
    document.getElementById('deactivateConfirmBtn').href = '?deactivate=' + id;
}
function setActivateTarget(id, name) {
    document.getElementById('activateEmpName').textContent = name;
    document.getElementById('activateConfirmBtn').href = '?activate=' + id;
}
function setDeleteTarget(id, name) {
    document.getElementById('deleteEmpName').textContent = name;
    document.getElementById('deleteConfirmBtn').href = '?delete=' + id;
}

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
    const ths = document.querySelectorAll("#empTable thead th");
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

document.getElementById('customSearchEmp').addEventListener('input', function() {
    currentPage = 1; // Reset to page 1 on search
    renderTable();
});

function goToPage(page) {
    currentPage = page;
    renderTable();
}

function renderTable() {
    const tbody = document.querySelector("#empTable tbody");
    const allRows = Array.from(tbody.querySelectorAll("tr:not(.no-results-row)"));
    const filterInput = document.getElementById('customSearchEmp').value.toLowerCase().trim();
    
    let visibleRows = [];
    
    // 1. Filter
    allRows.forEach(row => {
        const cells = Array.from(row.querySelectorAll("td"));
        if (cells.length > 1) {
            const rowText = cells.slice(0, 6).map(td => td.textContent.trim().replace(/\s+/g, ' ')).join(' ').toLowerCase();
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
            
            if (currentSortColumn === 5) { // Hire Date
                valA = new Date(valA).getTime() || valA;
                valB = new Date(valB).getTime() || valB;
            }

            if (valA < valB) return sortDirection ? -1 : 1;
            if (valA > valB) return sortDirection ? 1 : -1;
            return 0;
        });
        
        // Re-append sorted rows to DOM
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
    
    // Previous Button
    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <button class="page-link" onclick="goToPage(${currentPage - 1})">Previous</button>
             </li>`;
             
    // Page Numbers (Show max 5 pagination buttons for cleaner look if many pages)
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
    
    // Next Button
    html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <button class="page-link" onclick="goToPage(${currentPage + 1})">Next</button>
             </li>`;
             
    digits.innerHTML = html;
}

function handleNoResults(totalItems, filterInput, tbody) {
    let noResultsRow = tbody.querySelector('.no-results-row');
    if (totalItems === 0 && filterInput !== "") {
        if (!noResultsRow) {
            noResultsRow = document.createElement('tr');
            noResultsRow.className = 'no-results-row text-center';
            tbody.appendChild(noResultsRow);
        }
        noResultsRow.innerHTML = `<td colspan="7" class="py-4 text-muted"><i class="fas fa-search fa-2x mb-3 d-block"></i>No employees found matching "<strong>${filterInput}</strong>"</td>`;
        noResultsRow.style.display = '';
    } else if (noResultsRow) {
        noResultsRow.remove();
    }
}

// Initial Render on Load
document.addEventListener("DOMContentLoaded", renderTable);
</script>

