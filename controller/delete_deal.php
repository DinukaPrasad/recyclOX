<?php
    // Include the database connection file
    require_once('../config/db_connection.php');

    // Check if the ID is provided
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Delete the deal from the database
        $sql = "DELETE FROM Deals WHERE deal_id = $id";
        if ($con->query($sql)) {
            header("Location: ../admin_dashboard.php");
            exit();
        } else {
            die("Error deleting deal: " . $con->error);
        }
    } else {
        die("Invalid request.");
    }

?>