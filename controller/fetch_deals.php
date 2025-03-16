<?php
require_once('../config/db_connection.php'); // Update the path to db_connection.php

// Get search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$priceRange = isset($_GET['priceRange']) ? $_GET['priceRange'] : '';

// Build the SQL query
$query = "
    SELECT 
        d.deal_id, 
        d.deal_price, 
        d.created_at, 
        d.deal_status,
        b.first_name AS buyer_name, 
        s.first_name AS seller_name, 
        a.ad_id
    FROM Deals d
    JOIN Users b ON d.buyer_id = b.user_id
    JOIN Advertisements a ON d.ad_id = a.ad_id
    JOIN Users s ON a.seller_id = s.user_id
    WHERE 1=1
";

// Add search filter
if (!empty($search)) {
    $query .= " AND (b.first_name LIKE '%$search%' OR s.first_name LIKE '%$search%')";
}

// Add status filter
if (!empty($status)) {
    $query .= " AND d.deal_status = '$status'";
}

// Add price range filter
if (!empty($priceRange)) {
    list($minPrice, $maxPrice) = explode('-', $priceRange);
    if ($maxPrice == '1000+') {
        $query .= " AND d.deal_price >= $minPrice";
    } else {
        $query .= " AND d.deal_price BETWEEN $minPrice AND $maxPrice";
    }
}

// Execute the query
$result = $con->query($query);
$deals = $result->fetch_all(MYSQLI_ASSOC);

// Generate table rows
if (!empty($deals)) {
    foreach ($deals as $deal) {
        echo "
        <tr>
            <td>" . htmlspecialchars($deal['deal_id']) . "</td>
            <td>" . htmlspecialchars($deal['buyer_name']) . "</td>
            <td>" . htmlspecialchars($deal['seller_name']) . "</td>
            <td>" . htmlspecialchars($deal['ad_id']) . "</td>
            <td>" . htmlspecialchars($deal['deal_price']) . "</td>
            <td>" . htmlspecialchars($deal['created_at']) . "</td>
            <td>" . htmlspecialchars($deal['deal_status']) . "</td>
        </tr>
        ";
    }
} else {
    echo "<tr><td colspan='8'>No deals found.</td></tr>";
}
?>