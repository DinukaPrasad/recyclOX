<?php
// Start the session (if not already started)
session_start();

// Include the database connection file
require_once('../config/db_connection.php');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add-staff'])) {
    // Validate and sanitize form inputs
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password']; // Password will be hashed later
    $address = trim($_POST['address']);
    $phoneNumber = trim($_POST['phone_number']);
    $role = in_array($_POST['role'], ['admin', 'staff']) ? $_POST['role'] : 'staff'; // Default to 'staff' if invalid role

    // Validate inputs
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($address) || empty($phoneNumber)) {
        $_SESSION['staff_error'] = 'All fields are required!';
    } elseif (!$email) {
        $_SESSION['staff_error'] = 'Invalid email address!';
    } else {
        // Check if the email already exists
        $checkEmailQuery = $con->prepare("SELECT email FROM Users WHERE email = ?");
        $checkEmailQuery->bind_param("s", $email);
        $checkEmailQuery->execute();
        $checkEmailQuery->store_result();

        if ($checkEmailQuery->num_rows > 0) {
            $_SESSION['staff_error'] = 'Email is already registered!';
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new staff member into the Users table
            $insertQuery = $con->prepare("
                INSERT INTO Users (first_name, last_name, email, password, address, phone_number, role, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
            ");
            $insertQuery->bind_param("sssssss", $firstName, $lastName, $email, $hashedPassword, $address, $phoneNumber, $role);

            if ($insertQuery->execute()) {
                $_SESSION['staff_error'] = 'Staff member added successfully!';
            } else {
                $_SESSION['staff_error'] = 'Error adding staff member: ' . $con->error;
            }

            // Close the prepared statement
            $insertQuery->close();
        }

        // Close the prepared statement
        $checkEmailQuery->close();
    }

    header("Location: ../admin_dashboard.php");
    exit(); 
}
?>