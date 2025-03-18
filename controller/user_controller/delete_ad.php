<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once ('../../config/db_connection.php');

// Delete ad
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $ad_id = $_GET['ad_id'];
    $sql = "DELETE FROM Advertisements WHERE ad_id = ? AND seller_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ii", $ad_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    $stmt->close();
}
?>