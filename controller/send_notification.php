<?php
session_start();
require_once('../config/db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $recipient_type = $_POST['recipient_type'];
    $user_id = $_POST['user_id'] ?? null; // Optional, only for specific_user
    $message = $_POST['message'];

    // Validate inputs
    if (empty($recipient_type) || empty($message)) {
        $_SESSION['notification_error'] = "Please fill in all fields.";
        header("Location: ../admin_dashboard.php");
        exit();
    }

    // Handle specific user case
    if ($recipient_type === 'specific_user' && empty($user_id)) {
        $_SESSION['notification_error'] = "Please select a user.";
        header("Location: ../admin_dashboard.php");
        exit();
    }

    // Prepare the query based on recipient type
    if ($recipient_type === 'all_users') {
        // Send to all users with role = 'user'
        $query = "INSERT INTO Notifications (user_id, message, status, created_at) 
                  SELECT user_id, ?, 'unread', NOW() FROM Users WHERE role = 'user'";
        $stmt = $con->prepare($query);
        $stmt->bind_param("s", $message);
    } elseif ($recipient_type === 'all_staff') {
        // Send to all staff members with role = 'staff'
        $query = "INSERT INTO Notifications (user_id, message, status, created_at) 
                  SELECT user_id, ?, 'unread', NOW() FROM Users WHERE role = 'staff'";
        $stmt = $con->prepare($query);
        $stmt->bind_param("s", $message);
    } elseif ($recipient_type === 'specific_user') {
        // Send to a specific user
        $query = "INSERT INTO Notifications (user_id, message, status, created_at) 
                  VALUES (?, ?, 'unread', NOW())";
        $stmt = $con->prepare($query);
        $stmt->bind_param("is", $user_id, $message);
    }

    // Execute the query
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