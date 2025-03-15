<?php
session_start();
require_once('../config/db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $user_id = $_POST['user_id'];
    $message = $_POST['message'];

    // Validate inputs
    if (empty($user_id) || empty($message)) {
        $_SESSION['notification_error'] = "Please fill in all fields.";
        header("Location: ../admin_dashboard.php");
        exit();
    }

    // Insert notification into the database
    $query = "INSERT INTO Notifications (user_id, message, status, created_at) VALUES (?, ?, 'unread', NOW())";
    $stmt = $con->prepare($query);
    $stmt->bind_param("is", $user_id, $message);

    if ($stmt->execute()) {
        $_SESSION['notification_success'] = "Notification sent successfully!";
    } else {
        $_SESSION['notification_error'] = "Failed to send notification. Please try again.";
    }

    $stmt->close();
    header("Location: ../admin_dashboard.php");
    exit();
}
?>