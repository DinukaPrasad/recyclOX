<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once './config/db_connection.php'; // Database connection file

// Get form data
$user_id = $_SESSION['user_id'];
$first_name = $_POST['first_name'] ?? null;
$last_name = $_POST['last_name'] ?? null;
$phone_number = $_POST['phone_number'] ?? null;
$address = $_POST['address'] ?? null;
$profile_picture = $_FILES['profile_picture'] ?? null;

// Validate input
if (!$first_name || !$last_name || !$phone_number || !$address) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: user_dashboard.php");
    exit();
}

// Handle profile picture upload
$image_path = null;
if ($profile_picture && $profile_picture['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'images/profile_pictures/';
    $file_name = basename($profile_picture['name']);
    $file_path = $upload_dir . $file_name;

    // Move uploaded file to the target directory
    if (move_uploaded_file($profile_picture['tmp_name'], $file_path)) {
        $image_path = $file_path;
    } else {
        $_SESSION['error'] = "Failed to upload profile picture.";
        header("Location: user_dashboard.php");
        exit();
    }
}

// Update user profile in the database
$sql = "UPDATE users 
        SET first_name = ?, 
            last_name = ?, 
            phone_number = ?, 
            address = ?" .
            ($image_path ? ", profile_picture = ?" : "") .
        " WHERE user_id = ?";
$stmt = $con->prepare($sql);

if ($image_path) {
    $stmt->bind_param("sssssi", $first_name, $last_name, $phone_number, $address, $image_path, $user_id);
} else {
    $stmt->bind_param("ssssi", $first_name, $last_name, $phone_number, $address, $user_id);
}

$stmt->execute();

if ($stmt->affected_rows > 0) {
    $_SESSION['success'] = "Profile updated successfully.";
} else {
    $_SESSION['error'] = "Failed to update profile.";
}

$stmt->close();
$con->close();

// Redirect back to the dashboard
header("Location: user_dashboard.php");
exit();
?>