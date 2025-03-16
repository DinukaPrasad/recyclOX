<?php
require_once('../config/db_connection.php');

// Get search and sorting parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

// Build the SQL query
$query = "
    SELECT 
        gr.buyer_id,
        u.first_name AS buyer_name,
        gr.category_id,
        gc.category_name,
        gr.price_per_kg,
        gr.created_at,
        gr.updated_at
    FROM GarbageRatings gr
    JOIN Users u ON gr.buyer_id = u.user_id
    JOIN GarbageCategory gc ON gr.category_id = gc.category_id
    WHERE 1=1
";

// Add search filter
if (!empty($search)) {
    $query .= " AND (
        u.first_name LIKE '%$search%' OR
        gc.category_name LIKE '%$search%'
    )";
}

// Add category filter
if (!empty($category)) {
    $query .= " AND gc.category_name = '$category'";
}

// Add sorting
if ($sort === 'highest') {
    $query .= " ORDER BY gr.price_per_kg DESC";
}

// Execute the query
$result = $con->query($query);
$ratings = $result->fetch_all(MYSQLI_ASSOC);

// Generate table rows
if (!empty($ratings)) {
    foreach ($ratings as $rating) {
        echo "
        <tr>
            <td>" . htmlspecialchars($rating['buyer_name']) . "</td>
            <td>" . htmlspecialchars($rating['category_name']) . "</td>
            <td>" . htmlspecialchars($rating['price_per_kg']) . "</td>
            <td>" . htmlspecialchars($rating['created_at']) . "</td>
            <td>" . htmlspecialchars($rating['updated_at']) . "</td>
        </tr>
        ";
    }
} else {
    echo "<tr><td colspan='6'>No garbage ratings found.</td></tr>";
}
?>