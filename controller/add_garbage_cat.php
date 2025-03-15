<?php
// Include the database connection file
require_once('../config/db_connection.php');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the category name from the form
    $categoryName = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';

    // Validate the input
    if (empty($categoryName)) {
        die("Category name is required.");
    }

    // Prepare the SQL query
    $query = "INSERT INTO GarbageCategory (category_name) VALUES (?)";
    $stmt = $con->prepare($query);

    // Check if the statement was prepared successfully
    if (!$stmt) {
        die("Error preparing statement: " . $con->error);
    }

    // Bind the parameter and execute the query
    $stmt->bind_param('s', $categoryName);
    if ($stmt->execute()) {
        // Close the statement
        $stmt->close();

        // Close the database connection
        $con->close();

        // Redirect back to the dashboard with a success message
        header("Location: ../admin_dashboard.php");
        exit();
    } else {
        // Handle database errors
        die("Error adding category: " . $stmt->error);
    }
} else {
    // If the request method is not POST, redirect to the dashboard
    header("Location: ../admin_dashboard.php");
    exit();
}
?>