<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once './config/db_connection.php'; // Database connection file

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input data
    $seller_id = $_SESSION['user_id'];
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $city_id = filter_input(INPUT_POST, 'city_id', FILTER_VALIDATE_INT);
    $weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_FLOAT);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $description = htmlspecialchars(trim($_POST['description']));

    // Validate required fields
    if (!$category_id || !$city_id || !$weight || !$price || empty($description)) {
        die("Invalid input. Please fill in all fields correctly.");
    }

    // Insert ad into the database
    $sql = "INSERT INTO Advertisements (seller_id, category_id, city_id, weight, price, description, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'active')";
    $stmt = $con->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $con->error);
    }
    $stmt->bind_param("iiidds", $seller_id, $category_id, $city_id, $weight, $price, $description);

    if ($stmt->execute()) {
        $ad_id = $stmt->insert_id;

        // Handle image upload
        if (isset($_FILES['ad_image']) && $_FILES['ad_image']['error'] === UPLOAD_ERR_OK) {
            // Validate the uploaded file
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['ad_image']['type'];

            if (in_array($file_type, $allowed_types)) {
                $target_dir = "images/ads/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true); // Create the directory if it doesn't exist
                }
                $target_file = $target_dir . $ad_id . ".jpg";

                // Convert the image to JPEG if it's not already
                if ($file_type === 'image/png') {
                    $image = imagecreatefrompng($_FILES['ad_image']['tmp_name']);
                    imagejpeg($image, $target_file, 90); // Convert to JPEG with 90% quality
                    imagedestroy($image);
                } elseif ($file_type === 'image/gif') {
                    $image = imagecreatefromgif($_FILES['ad_image']['tmp_name']);
                    imagejpeg($image, $target_file, 90); // Convert to JPEG with 90% quality
                    imagedestroy($image);
                } else {
                    move_uploaded_file($_FILES['ad_image']['tmp_name'], $target_file);
                }
            } else {
                die("Invalid file type. Only JPEG, PNG, and GIF are allowed.");
            }
        }

        // Redirect to the user dashboard
        header("Location: user_dashboard.php");
        exit();
    } else {
        die("Error creating ad: " . $stmt->error);
    }
}

$con->close();
?>