<?php
        require_once('../config/db_connection.php');

        // Fetch pending advertisements
        $query = "
            SELECT 
                a.ad_id,
                u.first_name AS seller_name,
                gc.category_name,
                a.weight
            FROM Advertisements a
            JOIN Users u ON a.seller_id = u.user_id
            JOIN GarbageCategory gc ON a.category_id = gc.category_id
            WHERE a.status = 'pending'
        ";

        $result = $con->query($query);

        if ($result) {
            $pendingAds = $result->fetch_all(MYSQLI_ASSOC);

            // Generate table rows
            if (!empty($pendingAds)) {
                foreach ($pendingAds as $ad) {
                    echo "
                    <tr>
                        <td>" . htmlspecialchars($ad['seller_name']) . "</td>
                        <td>" . htmlspecialchars($ad['category_name']) . "</td>
                        <td>" . htmlspecialchars($ad['weight']) . "</td>
                        <td>
                            <button onclick='acceptAd(" . $ad['ad_id'] . ")'>Accept</button>
                            <button onclick='rejectAd(" . $ad['ad_id'] . ")'>Reject</button>
                        </td>
                    </tr>
                    ";
                }
            } else {
                echo "<tr><td colspan='4'>No pending advertisements found.</td></tr>";
            }
        } else {
            // Handle query error
            echo "<tr><td colspan='4'>Error fetching pending advertisements. Please try again later.</td></tr>";
            error_log("Database query failed: " . $con->error); // Log the error for debugging
        }
?>