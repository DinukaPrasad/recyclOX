<?php
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
    <script src="./asset/js/filter_ads_market.js" defer></script>
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

    <!-- Main Content -->
    <div class="container">
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
            <h1>Welcome to Marketplace</h1>
            <div class="product-grid" id="product-grid">
                <?php
                // Display advertisements
                if (empty($advertisements)) {
                    echo '<p>No advertisements found.</p>';
                } else {
                    foreach ($advertisements as $ad) {
                        echo '
                        <div class="product-card">
                            <h3>' . htmlspecialchars($ad['description']) . '</h3>
                            <p><strong>Category:</strong> ' . htmlspecialchars($ad['category_name']) . '</p>
                            <p><strong>Weight:</strong> ' . htmlspecialchars($ad['weight']) . ' kg</p>
                            <p><strong>Location:</strong> ' . htmlspecialchars($ad['city']) . '</p>
                        </div>';
                    }
                }
                ?>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2023 Marketplace. All rights reserved.</p>
    </footer>
</body>
</html>