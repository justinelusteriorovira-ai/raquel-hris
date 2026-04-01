<?php
/**
 * AJAX endpoint: returns criteria for a given template as JSON.
 */
require_once '../includes/session-check.php';
checkRole(['HR Staff']);

header('Content-Type: application/json');

$template_id = isset($_GET['template_id']) ? (int)$_GET['template_id'] : 0;
if ($template_id <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM evaluation_criteria WHERE template_id = ? ORDER BY sort_order");
$stmt->bind_param("i", $template_id);
$stmt->execute();
$result = $stmt->get_result();

$criteria = [];
while ($row = $result->fetch_assoc()) {
    $criteria[] = $row;
}
$stmt->close();

echo json_encode($criteria);
