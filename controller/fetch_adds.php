<?php
require_once('../config/db_connection.php');

// Get search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Build the SQL query
$query = "
    SELECT 
        a.ad_id, 
        a.seller_id, 
        gc.category_name,
        a.weight,
        a.description, 
        a.status, 
        a.created_at, 
        a.updated_at, 
        u.first_name AS seller_name
    FROM Advertisements a
    JOIN Users u ON a.seller_id = u.user_id
    JOIN GarbageCategory gc ON a.category_id = gc.category_id
    WHERE 1=1
";

// Add search filter
if (!empty($search)) {
    $query .= " AND u.first_name LIKE '%$search%'";
}

// Add status filter
if (!empty($status)) {
    $query .= " AND a.status = '$status'";
}

// Add category filter
if (!empty($category)) {
    $query .= " AND gc.category_name = '$category'";
}

// Execute the query
$result = $con->query($query);
$advertisements = $result->fetch_all(MYSQLI_ASSOC);

// Generate table rows
if (!empty($advertisements)) {
    foreach ($advertisements as $ad) {
        echo "
        <tr>
            <td>" . htmlspecialchars($ad['ad_id']) . "</td>
            <td>" . htmlspecialchars($ad['seller_name']) . "</td>
            <td>" . htmlspecialchars($ad['category_name']) . "</td>
            <td>" . htmlspecialchars($ad['weight']) . "</td>
            <td>" . htmlspecialchars($ad['description']) . "</td>
            <td>" . htmlspecialchars($ad['status']) . "</td>
            <td>" . htmlspecialchars($ad['created_at']) . "</td>
            <td>" . htmlspecialchars($ad['updated_at']) . "</td>
        </tr>
        ";
    }
} else {
    echo "<tr><td colspan='10'>No advertisements found.</td></tr>";
}
?>