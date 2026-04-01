<?php
$page_title = 'User Management';
require_once '../includes/session-check.php';
checkRole(['Admin']);
require_once '../includes/functions.php';

// ── Handle toggle active status ──────────────────────────────────────────────
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $uid = (int)$_GET['toggle'];
    if ($uid !== (int)$_SESSION['user_id']) {
        $conn->query("UPDATE users SET is_active = NOT is_active WHERE user_id = $uid");
        logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'User', $uid, 'Toggled user active status');
        redirectWith(BASE_URL . '/admin/users.php', 'success', 'User status updated successfully.');
    }
}

// ── Handle delete ─────────────────────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $uid = (int)$_GET['delete'];
    if ($uid !== (int)$_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE user_id = $uid");
        logAudit($conn, $_SESSION['user_id'], 'DELETE', 'User', $uid, 'Deleted user account');
        redirectWith(BASE_URL . '/admin/users.php', 'success', 'User deleted successfully.');
    } else {
        redirectWith(BASE_URL . '/admin/users.php', 'danger', 'You cannot delete your own account.');
    }
}

require_once '../includes/header.php';

// Fetch all users with branch info
$users = $conn->query("SELECT u.*, b.branch_name FROM users u LEFT JOIN branches b ON u.branch_id = b.branch_id ORDER BY u.created_at DESC");

// Fetch branches for the add form
$branches = $conn->query("SELECT * FROM branches ORDER BY branch_name");
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Manage system user accounts and roles</p>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-plus me-2"></i>Add New User
    </button>
</div>

<!-- Users Table -->
<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-users me-2"></i>All Users</h5>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="form-control form-control-sm" id="searchUsers" placeholder="Search users..." onkeyup="filterTable('searchUsers', 'usersTable')">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover" id="usersTable">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if ($user['profile_picture']): ?>
                                    <img src="<?php echo BASE_URL . '/' . e($user['profile_picture']); ?>" 
                                         alt="Profile" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" 
                                         style="width: 32px; height: 32px; font-size: 12px;">
                                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo e($user['username']); ?></strong></td>
                            <td><?php echo e($user['full_name']); ?></td>
                            <td><?php echo e($user['email']); ?></td>
                            <td><span class="badge bg-primary"><?php echo e($user['role']); ?></span></td>
                            <td><?php echo e($user['branch_name'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge <?php echo $user['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                    <!-- Edit -->
                                    <a href="<?php echo BASE_URL; ?>/admin/edit-user.php?id=<?php echo $user['user_id']; ?>"
                                       class="btn btn-sm btn-outline-primary" title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <!-- Toggle Status -->
                                    <a href="?toggle=<?php echo $user['user_id']; ?>"
                                       class="btn btn-sm btn-outline-warning" title="Toggle Active/Inactive">
                                        <i class="fas fa-power-off"></i>
                                    </a>
                                    <!-- Delete — uses Bootstrap modal, no native confirm() -->
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            title="Delete User"
                                            onclick="setDeleteTarget(<?php echo $user['user_id']; ?>, '<?php echo e(addslashes($user['username'])); ?>')"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <small class="text-muted">Current User</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── Add User Modal ─────────────────────────────────────────────────────── -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/admin/add-user.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" name="role" required>
                            <option value="">Select Role</option>
                            <option value="Admin">Admin</option>
                            <option value="HR Manager">HR Manager</option>
                            <option value="HR Supervisor">HR Supervisor</option>
                            <option value="HR Staff">HR Staff</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Branch</label>
                        <select class="form-select" name="branch_id">
                            <option value="">None (Admin)</option>
                            <?php $branches->data_seek(0); while ($branch = $branches->fetch_assoc()): ?>
                                <option value="<?php echo $branch['branch_id']; ?>"><?php echo e($branch['branch_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Profile Picture</label>
                        <input type="file" class="form-control" name="profile_picture" accept="image/*">
                        <div class="form-text">Optional. Max 2MB (JPG, PNG, WebP).</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Delete Confirmation Modal ──────────────────────────────────────────── -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Delete User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
                <p class="text-danger small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="deleteConfirmBtn" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i>Delete
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function setDeleteTarget(userId, username) {
    document.getElementById('deleteUserName').textContent = username;
    document.getElementById('deleteConfirmBtn').href = '?delete=' + userId;
}
</script>

<?php require_once '../includes/footer.php'; ?>
