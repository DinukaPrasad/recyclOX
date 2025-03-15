<?php
require_once('../config/db_connection.php');

// Get the category ID from the URL
$categoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($categoryId > 0) {
    // Prepare the SQL query to delete the category
    $query = "DELETE FROM GarbageCategory WHERE category_id = ?";
    $stmt = $con->prepare($query);

    // Check if the statement was prepared successfully
    if (!$stmt) {
        die("Error preparing statement: " . $con->error);
    }

    // Bind the parameter and execute the query
    $stmt->bind_param('i', $categoryId);
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
        die("Error deleting category: " . $stmt->error);
    }
} else {
    // If the category ID is invalid, redirect to the dashboard
    header("Location: ../admin_dashboard.php");
    exit();
}
?>