<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$notification_id = $data['notification_id'];

require_once './config/db_connection.php'; // Database connection file

// Mark notification as read
$sql = "UPDATE Notifications SET status = 'read' WHERE notification_id = ? AND user_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("ii", $notification_id, $_SESSION['user_id']);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$con->close();
?>