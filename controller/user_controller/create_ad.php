<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../../config/db_connection.php'; // Database connection file

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input data
    $seller_id = $_SESSION['user_id'];
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $city_id = filter_input(INPUT_POST, 'city_id', FILTER_VALIDATE_INT);
    $weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_FLOAT);
    $description = htmlspecialchars(trim($_POST['description']));

    // Validate required fields
    if (!$category_id || !$city_id || !$weight || empty($description)) {
        die("Invalid input. Please fill in all fields correctly.");
    }

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['ad_image']) && $_FILES['ad_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['ad_image']['type'];

        if (in_array($file_type, $allowed_types)) {
            $target_dir = "../../image/ads/"; // Set the target directory to "image/ads/"
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true); // Create the directory if it doesn't exist
            }

            $file_extension = pathinfo($_FILES['ad_image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . "." . $file_extension; // Generate a unique file name
            $target_file = $target_dir . $file_name;

            if (move_uploaded_file($_FILES['ad_image']['tmp_name'], $target_file)) {
                $image_path = $target_file; // Save the correct file path
            } else {
                die("Error uploading file.");
            }
        } else {
            die("Invalid file type. Only JPEG, PNG, and GIF are allowed.");
        }
    }

    // Insert ad into the database
    $sql = "INSERT INTO Advertisements (seller_id, category_id, postal_code, weight, description, ad_image, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $con->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $con->error);
    }
    $stmt->bind_param("iiidss", $seller_id, $category_id, $city_id, $weight, $description, $image_path);

    if ($stmt->execute()) {
        // Redirect to the user dashboard
        header("Location: ../../user_dashboard.php");
        exit();
    } else {
        die("Error creating ad: " . $stmt->error);
    }
}

$con->close();
?>