<?php
// Start the session
session_start();

// Include the database connection file
require_once '../../config/db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login-register.php");
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Initialize variables
$current_password = $new_password = $confirm_password = '';
$errors = [];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input data
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate current password
    if (empty($current_password)) {
        $errors[] = "Current password is required.";
    }

    // Validate new password
    if (empty($new_password)) {
        $errors[] = "New password is required.";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "New password must be at least 8 characters long.";
    }

    // Validate confirm password
    if (empty($confirm_password)) {
        $errors[] = "Confirm password is required.";
    } elseif ($new_password !== $confirm_password) {
        $errors[] = "New password and confirm password do not match.";
    }

    // If there are no errors, proceed to verify the current password and update the password
    if (empty($errors)) {
        // Fetch the current password hash from the database
        $sql = "SELECT password FROM Users WHERE user_id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        // Verify the current password
        if (password_verify($current_password, $hashed_password)) {
            // Hash the new password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the password in the database
            $sql = "UPDATE Users SET password = ? WHERE user_id = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("si", $new_password_hash, $user_id);

            if ($stmt->execute()) {
                // Redirect to the profile page with a success message
                $_SESSION['success_message'] = "Password changed successfully!";
                header("Location: ../../user_dashboard.php");
                exit();
            } else {
                $errors[] = "Failed to update password. Please try again.";
            }

            // Close the statement
            $stmt->close();
        } else {
            $errors[] = "Current password is incorrect.";
        }
    }
}

// If there are errors, redirect back to the change password page with error messages
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: ../../user_dashboard.php");
    exit();
}

// Close the database connection
$con->close();
?>