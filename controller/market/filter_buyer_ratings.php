<?php
// filter_buyer_ratings.php

// Start the session
session_start();

// Include the database connection file
require_once('../../config/db_connection.php');

// Check if category filter is set
if (isset($_GET['garbage_category'])) {
    $selectedCategories = $_GET['garbage_category']; // Get selected category IDs

    // Convert selected categories to a comma-separated string for SQL query
    $categoryIds = implode(",", $selectedCategories);

    // Fetch filtered ratings based on selected categories
    $sql = "SELECT 
                gr.buyer_id,
                gr.category_id,
                gr.price_per_kg,
                gr.created_at,
                gr.updated_at,
                u.first_name AS buyer_name,
                u.email AS buyer_email,
                gc.category_name
            FROM 
                garbageratings gr
            JOIN 
                users u ON gr.buyer_id = u.user_id
            JOIN 
                garbagecategory gc ON gr.category_id = gc.category_id
            WHERE 
                gr.category_id IN ($categoryIds)";
} else {
    // If no categories are selected, fetch all ratings
    $sql = "SELECT 
                gr.buyer_id,
                gr.category_id,
                gr.price_per_kg,
                gr.created_at,
                gr.updated_at,
                u.first_name AS buyer_name,
                u.email AS buyer_email,
                gc.category_name
            FROM 
                garbageratings gr
            JOIN 
                users u ON gr.buyer_id = u.user_id
            JOIN 
                garbagecategory gc ON gr.category_id = gc.category_id";
}

$result = $con->query($sql);

// Check if the query was successful
if (!$result) {
    die("Database query failed: " . $con->error);
}

// Fetch filtered ratings and store them in an array
$filteredRatings = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $filteredRatings[] = $row;
    }
}

// Return filtered ratings as JSON
header('Content-Type: application/json');
echo json_encode($filteredRatings);

// Close the database connection
$con->close();
?>