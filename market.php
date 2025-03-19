<?php
// Start the session
session_start();

// Include the database connection file
require_once('./config/db_connection.php');

// Fetch all active advertisements with city names and category names
$sql = "SELECT a.*, l.city, gc.category_name 
        FROM advertisements a
        JOIN location l ON a.postal_code = l.postal_code
        JOIN garbagecategory gc ON a.category_id = gc.category_id
        WHERE a.status = 'active'";
$result = $con->query($sql);

// Check if the query was successful
if (!$result) {
    die("Database query failed: " . $con->error);
}

// Fetch advertisements and store them in an array
$advertisements = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $advertisements[] = $row;
    }
}

// Fetch category names from the garbage_category table
$categorySql = "SELECT * FROM garbagecategory";
$categoryResult = $con->query($categorySql);

$categories = [];
if ($categoryResult->num_rows > 0) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch location names from the location table
$locationSql = "SELECT * FROM location";
$locationResult = $con->query($locationSql);

$locations = [];
if ($locationResult->num_rows > 0) {
    while ($row = $locationResult->fetch_assoc()) {
        $locations[] = $row;
    }
}

// Fetch data from garbage_ratings, users, and garbagecategory tables
$sql = "SELECT 
            gr.buyer_id,
            gr.category_id,
            gr.price_per_kg,
            gr.created_at,
            gr.updated_at,
            u.first_name AS buyer_name,
            u.email AS buyer_email,
            gc.category_name
        FROM 
            garbageratings gr
        JOIN 
            users u ON gr.buyer_id = u.user_id
        JOIN 
            garbagecategory gc ON gr.category_id = gc.category_id";

$result = $con->query($sql);

// Check if the query was successful
if (!$result) {
    die("Database query failed: " . $con->error);
}

// Fetch data and store it in an array
$ratings = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ratings[] = $row;
    }
}

// Fetch highest price per category
$highestPriceSql = "SELECT 
                        gc.category_name, 
                        MAX(gr.price_per_kg) AS highest_price
                    FROM 
                        garbageratings gr
                    JOIN 
                        garbagecategory gc ON gr.category_id = gc.category_id
                    GROUP BY 
                        gc.category_name";

$highestPriceResult = $con->query($highestPriceSql);

$highestPrices = [];
if ($highestPriceResult->num_rows > 0) {
    while ($row = $highestPriceResult->fetch_assoc()) {
        $highestPrices[] = $row;
    }
}

// Fetch user's favorite categories if logged in
$favorite_categories = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $favorite_sql = "SELECT category_id FROM UserFavorites WHERE user_id = $user_id";
    $favorite_result = $con->query($favorite_sql);
    if ($favorite_result) {
        $favorite_categories = $favorite_result->fetch_all(MYSQLI_ASSOC);
    }
}

// Filter advertisements based on favorite categories
$favorite_ads = [];
if (!empty($favorite_categories)) {
    $favorite_category_ids = array_column($favorite_categories, 'category_id');
    foreach ($advertisements as $ad) {
        if (in_array($ad['category_id'], $favorite_category_ids)) {
            $favorite_ads[] = $ad;
        }
    }
}

// Close the database connection
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RecyclOX Marketplace</title>
    <link rel="stylesheet" href="./asset/css/market.css">
    <link rel="stylesheet" href="./asset/css/main.css">
    <link rel="stylesheet" href="./asset/css/components.css">
    <script src="./asset/js/filter_ads_market.js" defer></script>
</head>
<body class="m-body">
    <!-- Navigation Bar -->
    <header>
        <nav class="navbar">
            <div class="logo">RecyclOX <span>Marketplace</span></div>
            <div class="middle-links">
                <a href="#" id="advertisements-link">Advertisements</a>
                <a href="#" id="buyers-link">Buyers & Ratings</a>
                <a href="./index.php">Home</a>
            </div>
            <div class="nav-links">
                <?php if (isset($_SESSION['name'])): ?>
                    <!-- Display username and logout button if logged in -->
                    <a href="./user_dashboard.php">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></a>
                    <a href="./controller/logout_function.php" class="btn-1">Logout</a>
                <?php else: ?>
                    <!-- Display login button if not logged in -->
                    <a href="./login_register.php" class="btn-1">Login</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="container">
        <!-- Advertisements Section -->
        <div id="advertisements-section">
            <!-- Sidebar with Filters -->
            <aside class="sidebar">
                <h3>Filters</h3>
                <form id="filter-form">
                    <!-- Category Filter -->
                    <div class="filter-section">
                        <h4>Category</h4>
                        <?php
                        foreach ($categories as $category) {
                            echo '
                            <label>
                                <input type="checkbox" name="category[]" value="' . $category['category_id'] . '">
                                ' . htmlspecialchars($category['category_name']) . '
                            </label>';
                        }
                        ?>
                    </div>

                    <!-- Location Filter -->
                    <div class="filter-section">
                        <h4>Location</h4>
                        <?php
                        foreach ($locations as $location) {
                            echo '
                            <label>
                                <input type="checkbox" name="postal_code[]" value="' . $location['postal_code'] . '">
                                ' . htmlspecialchars($location['city']) . '
                            </label>';
                        }
                        ?>
                    </div>

                    <!-- Weight Filter -->
                    <div class="filter-section">
                        <h4>Weight (kg)</h4>
                        <label><input type="checkbox" name="weight[]" value="0-50"> 0 - 50 kg</label>
                        <label><input type="checkbox" name="weight[]" value="50-100"> 50 - 100 kg</label>
                        <label><input type="checkbox" name="weight[]" value="100-200"> 100 - 200 kg</label>
                        <label><input type="checkbox" name="weight[]" value="200+"> 200+ kg</label>
                    </div>
                </form>
            </aside>

            <!-- Product Grid -->
            <main>
                <!-- User Favorite Ads Section -->
                <?php if (!empty($favorite_ads) && isset($_SESSION['user_id'])): ?>
                    <div id="favorite-ads">
                        <h2>Your Favorite Ads</h2>
                        <div class="product-grid">
                            <?php foreach ($favorite_ads as $ad): ?>
                                <a href="./view_ad.php?ad_id=<?php echo $ad['ad_id']; ?>" class="product-card">
                                    <h3><?php echo htmlspecialchars($ad['description']); ?></h3>
                                    <p><strong>Category:</strong> <?php echo htmlspecialchars($ad['category_name']); ?></p>
                                    <p><strong>Weight:</strong> <?php echo htmlspecialchars($ad['weight']); ?> kg</p>
                                    <p><strong>Location:</strong> <?php echo htmlspecialchars($ad['city']); ?></p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- All Ads Section -->
                <div id="all-ads">
                    <h2>All Ads</h2>
                    <div class="product-grid">
                        <?php if (empty($advertisements)): ?>
                            <p>No advertisements found.</p>
                        <?php else: ?>
                            <?php foreach ($advertisements as $ad): ?>
                                <a href="./view_ad.php?ad_id=<?php echo $ad['ad_id']; ?>" class="product-card">
                                    <h3><?php echo htmlspecialchars($ad['description']); ?></h3>
                                    <p><strong>Category:</strong> <?php echo htmlspecialchars($ad['category_name']); ?></p>
                                    <p><strong>Weight:</strong> <?php echo htmlspecialchars($ad['weight']); ?> kg</p>
                                    <p><strong>Location:</strong> <?php echo htmlspecialchars($ad['city']); ?></p>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </d>
            </main>
        </div>

        <!-- Buyers Section -->
        <div id="buyers-section" style="display: none;">
            <!-- Sidebar with Filters -->
            <aside class="sidebar">
                <h3>Buyer Filters</h3>
                <form id="buyer-filter-form">
                    <!-- Garbage Category Filter -->
                    <div class="filter-section">
                        <h4>Garbage Category</h4>
                        <?php
                        foreach ($categories as $category) {
                            echo '
                            <label>
                                <input type="checkbox" name="garbage_category[]" value="' . $category['category_id'] . '">
                                ' . htmlspecialchars($category['category_name']) . '
                            </label>';
                        }
                        ?>
                    </div>
                </form>
            </aside>

            <!-- Main Content -->
            <main>
                <!-- Highest Price per Category Section -->
                <div class="highest-price-section">
                    <h2>Highest Price per Category</h2>
                    <div class="buyer-grid">
                        <?php
                        if (empty($highestPrices)) {
                            echo '<p>No highest prices found.</p>';
                        } else {
                            foreach ($highestPrices as $price) {
                                echo '
                                <div class="buyer-card">
                                    <h3>' . htmlspecialchars($price['category_name']) . '</h3>
                                    <p><strong>Highest Price:</strong> ' . htmlspecialchars($price['highest_price']) . ' per kg</p>
                                </div>';
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- All Ratings Section -->
                <div class="all-ratings-section">
                    <h2>All Ratings</h2>
                    <div class="buyer-grid">
                        <?php
                        if (empty($ratings)) {
                            echo '<p>No ratings found.</p>';
                        } else {
                            foreach ($ratings as $rating) {
                                echo '
                                <a href="./view_buyer.php?buyer_id=' . $rating['buyer_id'] . '" class="buyer-card">
                                    <h3>' . htmlspecialchars($rating['buyer_name']) . '</h3>
                                    <p><strong>Category:</strong> ' . htmlspecialchars($rating['category_name']) . '</p>
                                    <p><strong>Price per kg:</strong> ' . htmlspecialchars($rating['price_per_kg']) . '</p>
                                </a>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 Marketplace. All rights reserved.</p>
    </footer>

    <!-- Include JavaScript for live filtering -->
    <script src="./asset/js/filter_buyer_ratings.js"></script>
    <script src="./asset/js/market_toggle.js"></script>
</body>
</html>