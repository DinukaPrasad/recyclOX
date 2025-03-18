<?php
// Start the session
session_start();

// Include the database connection file
require_once('./config/db_connection.php');

// Check if buyer_id is provided in the URL
if (!isset($_GET['buyer_id'])) {
    die("Buyer ID is missing.");
}

// Fetch the buyer details based on buyer_id
$buyer_id = $_GET['buyer_id'];
$sql = "SELECT 
            gr.buyer_id,
            gr.category_id,
            gr.price_per_kg,
            u.first_name AS buyer_name,
            gc.category_name
        FROM 
            garbageratings gr
        JOIN 
            users u ON gr.buyer_id = u.user_id
        JOIN 
            garbagecategory gc ON gr.category_id = gc.category_id
        WHERE 
            gr.buyer_id = $buyer_id";
$result = $con->query($sql);

// Check if the query was successful
if (!$result) {
    die("Database query failed: " . $con->error);
}

// Fetch the buyer details
$buyer = $result->fetch_assoc();

// Close the database connection
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Buyer - RecyclOX Marketplace</title>
    <link rel="stylesheet" href="./asset/css/view_ad.css"> <!-- Reuse the same CSS as view_ad.php -->
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
                    <a href="./controller/logout_function.php">Logout</a>
                <?php else: ?>
                    <!-- Display login button if not logged in -->
                    <a href="./login_register.php" class="btn-1">Login</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Buyer Details -->
    <div class="container">
        <main>
            <h1>Buyer Details</h1>
            <?php if ($buyer): ?>
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

                    <h2><?php echo htmlspecialchars($buyer['buyer_name']); ?></h2>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($buyer['category_name']); ?></p>
                    <p><strong>Price per kg:</strong> <?php echo htmlspecialchars($buyer['price_per_kg']); ?></p>

                    <!-- Add Make Deal and Cancel buttons -->
                    <div class="ad-actions">
                        <a href="./controller/market/make_deal_buyer.php?buyer_id=<?php echo $buyer['buyer_id']; ?>" class="btn make-deal">Make Deal</a>
                        <a href="./market.php" class="btn cancel">Cancel</a>
                    </div>
                </div>
            <?php else: ?>
                <p>Buyer not found.</p>
            <?php endif; ?>
        </main>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2023 Marketplace. All rights reserved.</p>
    </footer>
</body>
</html>