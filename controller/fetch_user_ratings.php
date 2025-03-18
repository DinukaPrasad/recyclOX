<?php
require_once('../config/db_connection.php');

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$rating = isset($_GET['rating']) ? intval($_GET['rating']) : 0; 

// Validate and sanitize inputs
$search = $con->real_escape_string($search); 
$rating = ($rating >= 1 && $rating <= 5) ? $rating : 0; 

// Build the SQL query
$query = "
    SELECT 
        f.feedback_id,
        f.deal_id,
        f.from_user_id,
        fu.first_name AS from_user_name,
        f.to_user_id,
        tu.first_name AS to_user_name,
        f.rating,
        f.comment,
        f.created_at
    FROM Feedback f
    JOIN Users fu ON f.from_user_id = fu.user_id
    JOIN Users tu ON f.to_user_id = tu.user_id
    WHERE 1=1
";

// Add search filter
if (!empty($search)) {
    $query .= " AND (
        fu.first_name LIKE '%$search%' OR
        tu.first_name LIKE '%$search%'
    )";
}

// Add rating filter
if ($rating > 0) {
    $query .= " AND f.rating = $rating";
}

// Execute the query
$result = $con->query($query);

// Check if the query was successful
if ($result) {
    $ratings = $result->fetch_all(MYSQLI_ASSOC);

    // Generate table rows
    if (!empty($ratings)) {
        foreach ($ratings as $rating) {
            echo "
            <tr>
                <td>" . htmlspecialchars($rating['feedback_id']) . "</td>
                <td>" . htmlspecialchars($rating['deal_id']) . "</td>
                <td>" . htmlspecialchars($rating['from_user_name']) . "</td>
                <td>" . htmlspecialchars($rating['to_user_name']) . "</td>
                <td>" . htmlspecialchars($rating['rating']) . "</td>
                <td>" . htmlspecialchars($rating['comment']) . "</td>
                <td>" . htmlspecialchars($rating['created_at']) . "</td>
                <td>
                    <a class='btn-3 btn-red' href='./controller/delete_user_rating.php?id=" . htmlspecialchars($rating['feedback_id']) . "' onclick='return confirm(\"Are you sure you want to delete this rating?\");'>Delete</a>
                </td>
            </tr>
            ";
        }
    } else {
        echo "<tr><td colspan='8'>No user ratings found.</td></tr>";
    }
} else {
    // Handle query error
    echo "<tr><td colspan='8'>Error fetching user ratings. Please try again later.</td></tr>";
    error_log("Database query failed: " . $con->error); // Log the error for debugging
}
?>