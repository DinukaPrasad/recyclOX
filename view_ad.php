<?php
// Include the database connection file
require_once('./config/db_connection.php');

// Check if ad_id is provided in the URL
if (!isset($_GET['ad_id'])) {
    die("Advertisement ID is missing.");
}

// Fetch the advertisement details based on ad_id
$ad_id = $_GET['ad_id'];
$sql = "SELECT a.*, l.city, gc.category_name 
        FROM advertisements a
        JOIN location l ON a.postal_code = l.postal_code
        JOIN garbagecategory gc ON a.category_id = gc.category_id
        WHERE a.ad_id = $ad_id";
$result = $con->query($sql);

// Check if the query was successful
if (!$result) {
    die("Database query failed: " . $con->error);
}

// Fetch the advertisement details
$ad = $result->fetch_assoc();

// Close the database connection
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Advertisement - RecyclOX Marketplace</title>
    <link rel="stylesheet" href="./asset/css/market.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">RecyclOX Marketplace</div>
        <div class="nav-links">
            <a href="./index.php">Home</a>
            <a href="./login_register.php">Login</a>
        </div>
    </nav>

    <!-- Advertisement Details -->
    <div class="container">
        <main>
            <h1>Advertisement Details</h1>
            <?php if ($ad): ?>
                <div class="ad-details">
                    <h2><?php echo htmlspecialchars($ad['description']); ?></h2>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($ad['category_name']); ?></p>
                    <p><strong>Weight:</strong> <?php echo htmlspecialchars($ad['weight']); ?> kg</p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($ad['city']); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($ad['description']); ?></p>
                </div>
            <?php else: ?>
                <p>Advertisement not found.</p>
            <?php endif; ?>
        </main>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2023 Marketplace. All rights reserved.</p>
    </footer>
</body>
</html>