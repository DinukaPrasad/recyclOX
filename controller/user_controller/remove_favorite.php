<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

require_once './config/db_connection.php'; // Database connection file

$data = json_decode(file_get_contents('php://input'), true);
$category_id = $data['category_id'];

$sql = "DELETE FROM UserFavorites WHERE user_id = ? AND category_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("ii", $_SESSION['user_id'], $category_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$con->close();
?>