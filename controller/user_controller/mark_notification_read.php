<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate the notification ID
if (!isset($data['notification_id'])) {
    echo json_encode(['success' => false, 'message' => 'Notification ID is missing']);
    exit();
}

$notification_id = intval($data['notification_id']);

// Database connection
require_once '../../config/db_connection.php'; // Adjust the path as needed

// Update the notification status to 'read'
$sql = "UPDATE Notifications SET status = 'read' WHERE notification_id = ? AND user_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("ii", $notification_id, $_SESSION['user_id']);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No notification found or already marked as read']);
    }
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$con->close();
?>