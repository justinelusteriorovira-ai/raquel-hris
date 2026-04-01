<?php
// ============================================
// Helper Functions
// ============================================

/**
 * Sanitize output to prevent XSS
 */
function e($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Create a notification for a user
 */
function createNotification($conn, $user_id, $title, $message, $link = null)
{
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $message, $link);
    $stmt->execute();
    $stmt->close();
}

/**
 * Log an audit event
 */
function logAudit($conn, $user_id, $action_type, $entity_type, $entity_id = null, $details = null)
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action_type, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississ", $user_id, $action_type, $entity_type, $entity_id, $details, $ip);
    $stmt->execute();
    $stmt->close();
}

/**
 * Get unread notification count for current user
 */
function getUnreadNotificationCount($conn, $user_id)
{
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['count'];
}

/**
 * Get recent notifications for a user
 */
function getRecentNotifications($conn, $user_id, $limit = 5)
{
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();
    return $notifications;
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'M d, Y')
{
    if (empty($date))
        return 'N/A';
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = 'M d, Y h:i A')
{
    if (empty($datetime))
        return 'N/A';
    return date($format, strtotime($datetime));
}

/**
 * Get performance level badge class
 */
function getPerformanceBadgeClass($level)
{
    switch ($level) {
        case 'Excellent':
            return 'bg-success';
        case 'Above Average':
            return 'bg-info';
        case 'Average':
            return 'bg-warning text-dark';
        case 'Needs Improvement':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

/**
 * Get status badge class
 */
function getStatusBadgeClass($status)
{
    switch ($status) {
        case 'Draft':
            return 'bg-secondary';
        case 'Pending Supervisor':
            return 'bg-warning text-dark';
        case 'Pending Manager':
            return 'bg-info';
        case 'Approved':
            return 'bg-success';
        case 'Rejected':
            return 'bg-danger';
        case 'Returned':
            return 'bg-purple';
        default:
            return 'bg-secondary';
    }
}

/**
 * Calculate performance level based on score
 */
function getPerformanceLevel($score)
{
    if ($score >= 90)
        return 'Excellent';
    if ($score >= 80)
        return 'Above Average';
    if ($score >= 70)
        return 'Average';
    return 'Needs Improvement';
}

/**
 * Redirect with a flash message
 */
function redirectWith($url, $type, $message)
{
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
    header("Location: " . $url);
    exit();
}

/**
 * Apply approved career movements whose effective_date has arrived
 * Call this on any page load where employee status matters.
 */
function applyPendingCareerMovements($conn)
{
    $today = date('Y-m-d');
    $result = $conn->query("SELECT * FROM career_movements WHERE approval_status = 'Approved' AND is_applied = 0 AND effective_date <= '$today'");
    if (!$result || $result->num_rows === 0)
        return;
    while ($m = $result->fetch_assoc()) {
        $emp_id = $m['employee_id'];
        $new_pos = $conn->real_escape_string($m['new_position']);
        if (!empty($m['new_branch_id'])) {
            $new_branch = (int) $m['new_branch_id'];
            $conn->query("UPDATE employees SET job_title='$new_pos', branch_id=$new_branch WHERE employee_id=$emp_id");
        } else {
            $conn->query("UPDATE employees SET job_title='$new_pos' WHERE employee_id=$emp_id");
        }
        $conn->query("UPDATE career_movements SET is_applied=1 WHERE movement_id={$m['movement_id']}");
    }
}

/**
 * Display flash message if exists
 */
function displayFlashMessage()
{
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'info';
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_type'], $_SESSION['flash_message']);
        echo '<div class="alert alert-' . e($type) . ' alert-dismissible fade show" role="alert">';
        echo e($message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
}
?>