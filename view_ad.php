<?php
// Start the session
session_start();

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
    <link rel="stylesheet" href="./asset/css/view_ad.css">
    <link rel="stylesheet" href="./asset/css/market.css">
    <link rel="stylesheet" href="./asset/css/main.css">
    <link rel="stylesheet" href="./asset/css/components.css">
</head>
<body class="m-body">
    <!-- Navigation Bar -->
    <header>
    <nav class="navbar">
        <div class="logo">RecyclOX <span>Marketplace</span></div>
        <div class="nav-links">
            <a href="./index.php">Home</a>
            <?php if (isset($_SESSION['name'])): ?>
                <!-- Display username and logout button if logged in -->
                <a href="#">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></a>
                <a href="./controller/logout_function.php" class="btn-1">Logout</a>
            <?php else: ?>
                <!-- Display login button if not logged in -->
                <a href="./login_register.php" class="btn-1">Login</a>
            <?php endif; ?>
        </div>
    </nav>
    </header>

    <!-- Advertisement Details -->
    <div class="container">
        <main>
            <h1>Advertisement Details</h1>
            <?php if ($ad): ?>
                <div class="ad-details">

                    <!-- Display success or error messages at the top of the container -->
                    <?php if (isset($_SESSION['make_deal_success'])): ?>
                        <div class="alert alert-success"><?php echo $_SESSION['make_deal_success']; ?></div>
                        <?php unset($_SESSION['make_deal_success']); // Clear the success message ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['make_deal_error'])): ?>
                        <div class="alert alert-error"><?php echo $_SESSION['make_deal_error']; ?></div>
                        <?php unset($_SESSION['make_deal_error']); // Clear the error message ?>
                    <?php endif; ?>


                    <h2><?php echo htmlspecialchars($ad['description']); ?></h2>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($ad['category_name']); ?></p>
                    <p><strong>Weight:</strong> <?php echo htmlspecialchars($ad['weight']); ?> kg</p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($ad['city']); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($ad['description']); ?></p>

                    <!-- Add Make Deal and Cancel buttons -->
                    <div class="ad-actions">
                        <a href="./controller/market/make_deal.php?ad_id=<?php echo $ad['ad_id']; ?>" class="btn make-deal">Make Deal</a>
                        <a href="./market.php" class="btn cancel">Cancel</a>
                    </div>
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