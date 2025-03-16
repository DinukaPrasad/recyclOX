<?php
// Include the database connection file
require_once('../../config/db_connection.php');

// Fetch filter parameters from the request
$categories = isset($_GET['category']) ? $_GET['category'] : [];
$postalCodes = isset($_GET['postal_code']) ? $_GET['postal_code'] : [];
$weights = isset($_GET['weight']) ? $_GET['weight'] : [];

// Base SQL query
$sql = "SELECT a.*, l.city 
        FROM advertisements a
        JOIN location l ON a.postal_code = l.postal_code
        WHERE a.status = 'active'";

// Apply filters
if (!empty($categories)) {
    $categories = implode("','", $categories);
    $sql .= " AND a.category_id IN ('$categories')";
}

if (!empty($postalCodes)) {
    $postalCodes = implode("','", $postalCodes);
    $sql .= " AND a.postal_code IN ('$postalCodes')";
}

if (!empty($weights)) {
    $weightConditions = [];
    foreach ($weights as $weightRange) {
        list($min, $max) = explode('-', $weightRange);
        if ($max === '+') {
            $weightConditions[] = "a.weight >= $min";
        } else {
            $weightConditions[] = "(a.weight >= $min AND a.weight <= $max)";
        }
    }
    $sql .= " AND (" . implode(' OR ', $weightConditions) . ")";
}

// Execute the query
$result = $con->query($sql);

// Fetch data and store it in an array
$advertisements = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $advertisements[] = $row;
    }
}

// Close the database connection
$con->close();

// Return filtered advertisements as JSON
header('Content-Type: application/json');
echo json_encode($advertisements);
?>