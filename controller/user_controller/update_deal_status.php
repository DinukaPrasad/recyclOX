<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$deal_id = $data['deal_id'];
$status = $data['status'];

require_once './config/db_connection.php'; // Database connection file

// Update deal status
$sql = "UPDATE Deals SET deal_status = ? WHERE deal_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("si", $status, $deal_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$con->close();
?>