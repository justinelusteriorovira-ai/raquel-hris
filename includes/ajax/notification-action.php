<?php
// ============================================
// AJAX Endpoint: Notification Actions
// Handles: mark_read, mark_unread, delete, delete_all_read, mark_all_read
// ============================================
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$notif_id = isset($_POST['notification_id']) ? (int) $_POST['notification_id'] : 0;

switch ($action) {
    case 'mark_read':
        if ($notif_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid notification ID']);
            exit;
        }
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notif_id, $user_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
        break;

    case 'mark_unread':
        if ($notif_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid notification ID']);
            exit;
        }
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 0 WHERE notification_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notif_id, $user_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
        break;

    case 'delete':
        if ($notif_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid notification ID']);
            exit;
        }
        $stmt = $conn->prepare("DELETE FROM notifications WHERE notification_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notif_id, $user_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
        break;

    case 'delete_all_read':
        $stmt = $conn->prepare("DELETE FROM notifications WHERE user_id = ? AND is_read = 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        echo json_encode(['success' => true, 'deleted' => $affected]);
        break;

    case 'mark_all_read':
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        echo json_encode(['success' => true, 'updated' => $affected]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
        break;
}
?>
