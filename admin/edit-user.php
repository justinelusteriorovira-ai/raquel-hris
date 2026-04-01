<?php
$page_title = 'Edit User';
require_once '../includes/session-check.php';
checkRole(['Admin']);
require_once '../includes/functions.php';   // REQUIRED for redirectWith() and logAudit()

// Validate user ID from URL
$uid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($uid <= 0) {
    redirectWith(BASE_URL . '/admin/users.php', 'danger', 'Invalid user ID.');
}

// Fetch the user to edit
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    redirectWith(BASE_URL . '/admin/users.php', 'danger', 'User not found.');
}

// ── Handle form submission ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username']  ?? '');
    $email      = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $full_name  = trim($_POST['full_name'] ?? '');
    $role       = $_POST['role']           ?? '';
    $branch_id  = !empty($_POST['branch_id']) ? (int)$_POST['branch_id'] : null;
    $new_pass   = trim($_POST['password']  ?? '');

    // Basic validation
    if (empty($username) || !$email || empty($full_name) || empty($role)) {
        redirectWith(BASE_URL . "/admin/edit-user.php?id=$uid", 'danger', 'Please fill in all required fields.');
    }

    // Duplicate username/email check (exclude self)
    $dup = $conn->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
    $dup->bind_param("ssi", $username, $email, $uid);
    $dup->execute();
    if ($dup->get_result()->num_rows > 0) {
        $dup->close();
        redirectWith(BASE_URL . "/admin/edit-user.php?id=$uid", 'danger', 'Username or email is already taken by another user.');
    }
    $dup->close();

    // ── Handle Profile Picture Upload ───────────────────────────────────────────
    $profile_picture = $user['profile_picture']; // Default to current
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_name = $_FILES['profile_picture']['name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($file_ext, $allowed_exts) && $file_size <= 2 * 1024 * 1024) {
            $new_file_name = 'user_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
            $upload_path = '../assets/uploads/profiles/' . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Delete old file if exists
                if ($user['profile_picture'] && file_exists('../' . $user['profile_picture'])) {
                    unlink('../' . $user['profile_picture']);
                }
                $profile_picture = 'assets/uploads/profiles/' . $new_file_name;
            }
        }
    }

    if (!empty($new_pass)) {
        if (strlen($new_pass) < 6) {
            redirectWith(BASE_URL . "/admin/edit-user.php?id=$uid", 'danger', 'Password must be at least 6 characters.');
        }
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username=?, email=?, full_name=?, role=?, branch_id=?, profile_picture=?, password_hash=? WHERE user_id=?");
        $stmt->bind_param("ssssissi", $username, $email, $full_name, $role, $branch_id, $profile_picture, $hash, $uid);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username=?, email=?, full_name=?, role=?, branch_id=?, profile_picture=? WHERE user_id=?");
        $stmt->bind_param("ssssisi", $username, $email, $full_name, $role, $branch_id, $profile_picture, $uid);
    }

    if ($stmt->execute()) {
        logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'User', $uid, "Updated user: $username ($role)");
        redirectWith(BASE_URL . '/admin/users.php', 'success', "User '$username' updated successfully.");
    } else {
        // Cleanup newly uploaded file if DB fails
        if ($profile_picture !== $user['profile_picture'] && file_exists('../' . $profile_picture)) {
            unlink('../' . $profile_picture);
        }
        redirectWith(BASE_URL . "/admin/edit-user.php?id=$uid", 'danger', 'Database error: ' . $conn->error);
    }
    $stmt->close();
}

// Load page UI
require_once '../includes/header.php';

// Branches for the dropdown
$branches = $conn->query("SELECT * FROM branches ORDER BY branch_name");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Edit user account details</p>
    <a href="<?php echo BASE_URL; ?>/admin/users.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Users
    </a>
</div>

<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-user-edit me-2"></i>Edit User: <?php echo e($user['full_name']); ?></h5>
    </div>
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="row mb-4 text-center">
                <div class="col-12">
                    <div class="position-relative d-inline-block">
                        <?php if ($user['profile_picture']): ?>
                            <img src="<?php echo BASE_URL . '/' . e($user['profile_picture']); ?>" 
                                 alt="Profile" class="rounded-circle img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white mx-auto" 
                                 style="width: 120px; height: 120px; font-size: 40px;">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="username"
                           value="<?php echo e($user['username']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email"
                           value="<?php echo e($user['email']); ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="full_name"
                           value="<?php echo e($user['full_name']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select class="form-select" name="role" required>
                        <option value="Admin"        <?php echo $user['role']==='Admin'         ? 'selected' : ''; ?>>Admin</option>
                        <option value="HR Manager"   <?php echo $user['role']==='HR Manager'   ? 'selected' : ''; ?>>HR Manager</option>
                        <option value="HR Supervisor"<?php echo $user['role']==='HR Supervisor'? 'selected' : ''; ?>>HR Supervisor</option>
                        <option value="HR Staff"     <?php echo $user['role']==='HR Staff'     ? 'selected' : ''; ?>>HR Staff</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Branch <small class="text-muted">(leave empty for Admin)</small></label>
                    <select class="form-select" name="branch_id">
                        <option value="">None (Admin)</option>
                        <?php while ($branch = $branches->fetch_assoc()): ?>
                            <option value="<?php echo $branch['branch_id']; ?>"
                                <?php echo ($user['branch_id'] == $branch['branch_id']) ? 'selected' : ''; ?>>
                                <?php echo e($branch['branch_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        New Password
                        <small class="text-muted">(leave blank to keep current)</small>
                    </label>
                    <input type="password" class="form-control" name="password"
                           minlength="6" placeholder="Min. 6 characters">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Change Profile Picture</label>
                    <input type="file" class="form-control" name="profile_picture" accept="image/*">
                    <div class="form-text">Max 2MB (JPG, PNG, WebP).</div>
                </div>
            </div>

            <!-- Active status toggle -->
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="isActiveToggle" disabled
                           <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="isActiveToggle">
                        Account is <?php echo $user['is_active'] ? '<span class="text-success">Active</span>' : '<span class="text-danger">Inactive</span>'; ?>
                        &mdash; <small class="text-muted">use the power button on the Users list to toggle</small>
                    </label>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <a href="<?php echo BASE_URL; ?>/admin/users.php" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update User
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
