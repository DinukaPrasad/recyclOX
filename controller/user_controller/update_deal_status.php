<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$deal_id = $data['deal_id'];
$status = $data['status'];

require_once '../../config/db_connection.php'; // Database connection file

// Update deal status
$sql = "UPDATE Deals SET deal_status = ? WHERE deal_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("si", $status, $deal_id);

if ($stmt->execute()) {
    // If the status is 'accepted', update the corresponding advertisement status to 'sold'
    if ($status === 'accepted') {
        // Fetch the ad_id from the deal
        $ad_sql = "SELECT ad_id FROM Deals WHERE deal_id = ?";
        $ad_stmt = $con->prepare($ad_sql);
        $ad_stmt->bind_param("i", $deal_id);
        $ad_stmt->execute();
        $ad_result = $ad_stmt->get_result();
        $ad_row = $ad_result->fetch_assoc();
        $ad_id = $ad_row['ad_id'];

        // Update the advertisement status to 'sold'
        $update_ad_sql = "UPDATE advertisements SET status = 'sold' WHERE ad_id = ?";
        $update_ad_stmt = $con->prepare($update_ad_sql);
        $update_ad_stmt->bind_param("i", $ad_id);
        $update_ad_stmt->execute();
        $update_ad_stmt->close();
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$con->close();
?>