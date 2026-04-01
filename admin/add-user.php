<?php
// Handle Add User form submission
require_once '../includes/session-check.php';
checkRole(['Admin']);
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $full_name = trim($_POST['full_name'] ?? '');
    $role = $_POST['role'] ?? '';
    $branch_id = !empty($_POST['branch_id']) ? (int)$_POST['branch_id'] : null;
    $password = $_POST['password'] ?? '';

    // Validate
    $errors = [];
    if (empty($username)) $errors[] = 'Username is required.';
    if (!$email) $errors[] = 'Valid email is required.';
    if (empty($full_name)) $errors[] = 'Full name is required.';
    if (!in_array($role, ['Admin', 'HR Manager', 'HR Supervisor', 'HR Staff'])) $errors[] = 'Invalid role.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

    // Check for duplicate username/email
    if (empty($errors)) {
        $check = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $errors[] = 'Username or email already exists.';
        }
        $check->close();
    }

    if (!empty($errors)) {
        redirectWith(BASE_URL . '/admin/users.php', 'danger', implode(' ', $errors));
    }

    // Hash password and insert
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // ── Handle Profile Picture Upload ───────────────────────────────────────────
    $profile_picture = null;
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
                $profile_picture = 'assets/uploads/profiles/' . $new_file_name;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, full_name, role, branch_id, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssis", $username, $email, $password_hash, $full_name, $role, $branch_id, $profile_picture);

    if ($stmt->execute()) {
        $new_id = $stmt->insert_id;
        logAudit($conn, $_SESSION['user_id'], 'CREATE', 'User', $new_id, "Created user: $username ($role)");
        redirectWith(BASE_URL . '/admin/users.php', 'success', "User '$username' created successfully.");
    } else {
        // Cleanup uploaded file if DB fails
        if ($profile_picture && file_exists('../' . $profile_picture)) {
            unlink('../' . $profile_picture);
        }
        redirectWith(BASE_URL . '/admin/users.php', 'danger', 'Failed to create user. Please try again.');
    }
    $stmt->close();
} else {
    header("Location: " . BASE_URL . "/admin/users.php");
    exit();
}
?>
