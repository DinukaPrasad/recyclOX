<?php
session_start();

header('Content-Type: application/json'); // Ensure the response is JSON

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

try {
    // Get JSON input
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Invalid input data.');
    }

    $category_id = $data['category_id'];
    $price_per_kg = $data['price_per_kg'];

    require_once '../../config/db_connection.php'; // Database connection file

    // Check if the rate already exists
    $sql = "SELECT buyer_id FROM GarbageRatings WHERE buyer_id = ? AND category_id = ?";
    $stmt = $con->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error preparing SQL statement: ' . $con->error);
    }
    $stmt->bind_param("ii", $_SESSION['user_id'], $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing rate
        $sql = "UPDATE GarbageRatings SET price_per_kg = ?, updated_at = NOW() WHERE buyer_id = ? AND category_id = ?";
        $stmt = $con->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error preparing SQL statement: ' . $con->error);
        }
        $stmt->bind_param("dii", $price_per_kg, $_SESSION['user_id'], $category_id);
    } else {
        // Insert new rate
        $sql = "INSERT INTO GarbageRatings (buyer_id, category_id, price_per_kg, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        $stmt = $con->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error preparing SQL statement: ' . $con->error);
        }
        $stmt->bind_param("iid", $_SESSION['user_id'], $category_id, $price_per_kg);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Error executing SQL statement: ' . $stmt->error);
    }
} catch (Exception $e) {
    // Log the error (optional)
    error_log($e->getMessage());

    // Return a JSON response with the error message
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    // Close the database connection
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($con)) {
        $con->close();
    }
}
?>