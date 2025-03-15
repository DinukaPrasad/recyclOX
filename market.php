<?php
// Include the database connection file
require_once('./config/db_connection.php');

$sql = "SELECT * FROM advertisements WHERE status = 'active'";
$result = $con->query($sql);

// Check if the query was successful
if (!$result) {
    die("Database query failed: " . $con->error);
}

// Fetch data and store it in an array
$advertisements = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $advertisements[] = $row;
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
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">RecyclOX Marketplace</div>
        <div class="search-bar">
            <form method="GET" action="market.php">
                <input type="text" name="search" placeholder="Search for products..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit">Search</button>
            </form>
        </div>
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
            <form method="GET" action="market.php">
                <div class="filter-section">
                    <h4>Category</h4>
                    <label><input type="checkbox" name="category[]" value="Plastic" <?php echo (isset($_GET['category']) && in_array('Plastic', $_GET['category'])) ? 'checked' : ''; ?>> Plastic</label>
                    <label><input type="checkbox" name="category[]" value="Metal" <?php echo (isset($_GET['category']) && in_array('Metal', $_GET['category'])) ? 'checked' : ''; ?>> Metal</label>
                    <label><input type="checkbox" name="category[]" value="Paper" <?php echo (isset($_GET['category']) && in_array('Paper', $_GET['category'])) ? 'checked' : ''; ?>> Paper</label>
                    <label><input type="checkbox" name="category[]" value="Glass" <?php echo (isset($_GET['category']) && in_array('Glass', $_GET['category'])) ? 'checked' : ''; ?>> Glass</label>
                    <label><input type="checkbox" name="category[]" value="Organic" <?php echo (isset($_GET['category']) && in_array('Organic', $_GET['category'])) ? 'checked' : ''; ?>> Organic</label>
                    <label><input type="checkbox" name="category[]" value="Electronic Waste" <?php echo (isset($_GET['category']) && in_array('Electronic Waste', $_GET['category'])) ? 'checked' : ''; ?>> Electronic Waste</label>
                    <label><input type="checkbox" name="category[]" value="Textiles" <?php echo (isset($_GET['category']) && in_array('Textiles', $_GET['category'])) ? 'checked' : ''; ?>> Textiles</label>
                    <label><input type="checkbox" name="category[]" value="Other" <?php echo (isset($_GET['category']) && in_array('Other', $_GET['category'])) ? 'checked' : ''; ?>> Other</label>
                </div>
                <div class="filter-section">
                    <h4>Price Range</h4>
                    <label><input type="checkbox" name="price[]" value="0-50" <?php echo (isset($_GET['price']) && in_array('0-50', $_GET['price'])) ? 'checked' : ''; ?>> $0 - $50</label>
                    <label><input type="checkbox" name="price[]" value="50-100" <?php echo (isset($_GET['price']) && in_array('50-100', $_GET['price'])) ? 'checked' : ''; ?>> $50 - $100</label>
                    <label><input type="checkbox" name="price[]" value="100-200" <?php echo (isset($_GET['price']) && in_array('100-200', $_GET['price'])) ? 'checked' : ''; ?>> $100 - $200</label>
                    <label><input type="checkbox" name="price[]" value="200+" <?php echo (isset($_GET['price']) && in_array('200+', $_GET['price'])) ? 'checked' : ''; ?>> $200+</label>
                </div>
                <button type="submit">Apply Filters</button>
            </form>
        </aside>

        <!-- Product Grid -->
        <main>
            <h1>Welcome to Marketplace</h1>
            <div class="product-grid">
                <?php
                // Filter advertisements based on search and filters
                $filteredAds = $advertisements;

                // Apply search filter
                if (isset($_GET['search'])) {
                    $search = strtolower($_GET['search']);
                    $filteredAds = array_filter($filteredAds, function($ad) use ($search) {
                        return stripos(strtolower($ad['description']), $search) !== false;
                    });
                }

                // Display filtered advertisements
                if (empty($filteredAds)) {
                    echo '<p>No advertisements found.</p>';
                } else {
                    foreach ($filteredAds as $ad) {
                        echo '
                        <div class="product-card">
                            <h3>' . htmlspecialchars($ad['description']) . '</h3>
                            <p><strong>Category:</strong> ' . htmlspecialchars($ad['category_id']) . '</p>
                            <p><strong>Weight:</strong> ' . htmlspecialchars($ad['weight']) . ' kg</p>
                            <p><strong>Price:</strong> $' . htmlspecialchars($ad['price']) . '</p>
                            <p><strong>Status:</strong> ' . htmlspecialchars($ad['status']) . '</p>
                            <p><strong>Posted On:</strong> ' . htmlspecialchars($ad['created_at']) . '</p>
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