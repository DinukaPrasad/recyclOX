<?php
require_once('../config/db_connection.php');

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Validate and sanitize inputs
$search = $con->real_escape_string($search); // Sanitize search input
$status = in_array($status, ['read', 'unread']) ? $status : ''; // Ensure status is valid

// Build the SQL query
$query = "
    SELECT 
        n.notification_id,
        n.user_id,
        u.first_name AS user_name,
        n.message,
        n.status,
        n.created_at
    FROM Notifications n
    JOIN Users u ON n.user_id = u.user_id
    WHERE 1=1
";

// Add search filter
if (!empty($search)) {
    $query .= " AND u.first_name LIKE '%$search%'";
}

// Add status filter
if (!empty($status)) {
    $query .= " AND n.status = '$status'";
}

// Execute the query
$result = $con->query($query);

// Check if the query was successful
if ($result) {
    $notifications = $result->fetch_all(MYSQLI_ASSOC);

    // Generate table rows
    if (!empty($notifications)) {
        foreach ($notifications as $notification) {
            echo "
            <tr>
                <td>" . htmlspecialchars($notification['notification_id']) . "</td>
                <td>" . htmlspecialchars($notification['user_name']) . "</td>
                <td>" . htmlspecialchars($notification['message']) . "</td>
                <td>" . htmlspecialchars($notification['status']) . "</td>
                <td>" . htmlspecialchars($notification['created_at']) . "</td>
                <td>
                    <a href='./controller/delete_notification.php?id=" . htmlspecialchars($notification['notification_id']) . "' onclick='return confirm(\"Are you sure you want to delete this notification?\");'>Delete</a>
                </td>
            </tr>
            ";
        }
    } else {
        echo "<tr><td colspan='6'>No notifications found.</td></tr>";
    }
} else {
    // Handle query error
    echo "<tr><td colspan='6'>Error fetching notifications. Please try again later.</td></tr>";
    error_log("Database query failed: " . $con->error); // Log the error for debugging
}
?>