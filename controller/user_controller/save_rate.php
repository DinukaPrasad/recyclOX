<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$category_id = $data['category_id'];
$price_per_kg = $data['price_per_kg'];

require_once './config/db_connection.php'; // Database connection file

// Check if the rate already exists
$sql = "SELECT rating_id FROM GarbageRatings WHERE buyer_id = ? AND category_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("ii", $_SESSION['user_id'], $category_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing rate
    $sql = "UPDATE GarbageRatings SET price_per_kg = ? WHERE buyer_id = ? AND category_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("dii", $price_per_kg, $_SESSION['user_id'], $category_id);
} else {
    // Insert new rate
    $sql = "INSERT INTO GarbageRatings (buyer_id, category_id, price_per_kg) VALUES (?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("iid", $_SESSION['user_id'], $category_id, $price_per_kg);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$con->close();
?>