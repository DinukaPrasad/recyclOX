<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login_register.php");
    exit();
}

require_once '../../config/db_connection.php'; // Database connection file

// Get form data
$ad_id = $_POST['ad_id'] ?? null;
$category = $_POST['category'] ?? null;
$location = $_POST['location'] ?? null;
$weight = $_POST['weight'] ?? null;
$description = $_POST['description'] ?? null;
$ad_image = $_FILES['ad_image'] ?? null;

// Validate input
if (!$ad_id || !$category || !$location || !$weight || !$description) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: ../../user_dashboard.php"); // Redirect back to the previous page
    exit();
}

// Handle image upload
$image_path = null;
if ($ad_image && $ad_image['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../../images/ads/';
    $file_name = uniqid() . '_' . basename($ad_image['name']);
    $file_path = $upload_dir . $file_name;

    // Move uploaded file to the target directory
    if (move_uploaded_file($ad_image['tmp_name'], $file_path)) {
        $image_path = $file_path;
    } else {
        $_SESSION['error'] = "Failed to upload image.";
        header("Location: ../../user_dashboard.php");
        exit();
    }
}

// Update ad in the database
$sql = "UPDATE Advertisements 
        SET category_id = (SELECT category_id FROM GarbageCategory WHERE category_name = ?),
            postal_code = (SELECT postal_code FROM location WHERE city = ?),
            weight = ?, 
            description = ?" .
            ($image_path ? ", ad_image = ?" : "") .
        " WHERE ad_id = ? AND seller_id = ?";
$stmt = $con->prepare($sql);

if ($image_path) {
    $stmt->bind_param("ssdsssi", $category, $location, $weight, $description, $image_path, $ad_id, $_SESSION['user_id']);
} else {
    $stmt->bind_param("ssdsi", $category, $location, $weight, $description, $ad_id, $_SESSION['user_id']);
}

$stmt->execute();

if ($stmt->affected_rows > 0) {
    $_SESSION['success'] = "Ad updated successfully.";
} else {
    $_SESSION['error'] = "Failed to update ad.";
}

$stmt->close();
$con->close();

// Redirect back to the previous page
header("Location: ../../user_dashboard.php");
exit();
?>