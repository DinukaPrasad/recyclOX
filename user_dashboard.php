<?php
session_start();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once './config/db_connection.php';

// Fetch user data from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT first_name, last_name, email, profile_picture FROM users WHERE user_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch user data
$user_id = $_SESSION['user_id']; // Assuming user_id is stored in the session
$sql = "SELECT first_name, last_name, email, phone_number, address, profile_picture FROM Users WHERE user_id = ?";
$stmt = $con->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $con->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc(); // Store user data in $user variable
} else {
    die("User not found.");
}

// Fetch stats (items sold, deals completed, average rating)
$stats = [
    'items_sold' => 0,
    'deals_completed' => 0,
    'average_rating' => 0,
];

// Fetch number of items sold
$sql = "SELECT COUNT(*) AS items_sold FROM Advertisements WHERE seller_id = ? AND status = 'sold'";
$stmt = $con->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $con->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $stats['items_sold'] = $result->fetch_assoc()['items_sold'];
}

// Fetch number of deals completed
$sql = "SELECT COUNT(*) AS deals_completed FROM Deals WHERE buyer_id = ? AND deal_status = 'completed'";
$stmt = $con->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $con->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $stats['deals_completed'] = $result->fetch_assoc()['deals_completed'];
}

// Fetch average rating
$sql = "SELECT AVG(rating) AS average_rating FROM Feedback WHERE to_user_id = ?";
$stmt = $con->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $con->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $stats['average_rating'] = $result->fetch_assoc()['average_rating'];
}

// Fetch ads for the logged-in user
$seller_id = $_SESSION['user_id'];
$sql = "SELECT a.*, c.category_name AS category_name, ci.name_en AS city_name
        FROM Advertisements a
        JOIN GarbageCategory c ON a.category_id = c.category_id
        JOIN cities ci ON a.city_id = ci.id
        WHERE a.seller_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$ads = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch cities
$sql = "SELECT id, name_en FROM cities";
$result = $con->query($sql);
$cities = $result->fetch_all(MYSQLI_ASSOC);

// Fetch all categories
$sql = "SELECT category_id, category_name FROM GarbageCategory";
$result = $con->query($sql);
$categories = $result->fetch_all(MYSQLI_ASSOC);

// Fetch user's favorite category IDs
$sql = "SELECT category_id FROM UserFavorites WHERE user_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$favorite_ids = array_column($result->fetch_all(MYSQLI_ASSOC), 'category_id');

// Fetch favorite categories
$favorite_categories = [];
if (!empty($favorite_ids)) {
    $sql = "SELECT category_id, category_name FROM GarbageCategory WHERE category_id IN (" . implode(',', $favorite_ids) . ")";
    $result = $con->query($sql);
    $favorite_categories = $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch user's rates
$user_rates = [];
$sql = "SELECT category_id, price_per_kg FROM GarbageRatings WHERE buyer_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $user_rates[$row['category_id']] = $row['price_per_kg'];
}

// Fetch feedback for the logged-in user
$sql = "SELECT f.rating, f.comment, f.created_at, u.first_name AS from_user_name
        FROM Feedback f
        JOIN Users u ON f.from_user_id = u.user_id
        WHERE f.to_user_id = ?
        ORDER BY f.created_at DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $feedbacks = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $feedbacks = [];
}

// Fetch notifications for the logged-in user
$sql = "SELECT notification_id, message, status, created_at 
        FROM Notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $notifications = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $notifications = [];
}

// Fetch deals for the logged-in user (as buyer or seller)
$sql = "SELECT d.deal_id, d.deal_status, d.deal_price, d.created_at, 
               a.description AS ad_description, 
               buyer.first_name AS buyer_name, 
               seller.first_name AS seller_name,
               CASE 
                   WHEN d.buyer_id = ? THEN 'buyer'
                   WHEN a.seller_id = ? THEN 'seller'
               END AS user_role
        FROM Deals d
        JOIN Advertisements a ON d.ad_id = a.ad_id
        JOIN Users buyer ON d.buyer_id = buyer.user_id
        JOIN Users seller ON a.seller_id = seller.user_id
        WHERE d.buyer_id = ? OR a.seller_id = ?
        ORDER BY d.created_at DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("iiii", $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $deals = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $deals = [];
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - RecyclOX</title>
    <link rel="stylesheet" href="user_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <!-- Side Navigation Bar -->
        <div class="side-nav">
            <div class="user-info">
                <img src="<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'images/default-avatar.jpg'; ?>" alt="User Avatar" class="user-avatar">
                <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <ul class="nav-links">
                <li><a href="#my-profile" class="active" onclick="showSection('my-profile')"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="#my-listed-items" onclick="showSection('my-listed-items')"><i class="fas fa-list"></i> My Listed Items</a></li>
                <li><a href="#deals" onclick="showSection('deals')"><i class="fas fa-handshake"></i> Deals</a></li>
                <li><a href="#notifications" onclick="showSection('notifications')"><i class="fas fa-bell"></i> Notifications</a></li>
                <li><a href="#favorites" onclick="showSection('favorites')"><i class="fas fa-heart"></i> Favorites</a></li>
                <li><a href="#my-rates" onclick="showSection('my-rates')"><i class="fas fa-star"></i> My Rates</a></li>
                <li><a href="#reviews-feedbacks" onclick="showSection('reviews-feedbacks')"><i class="fas fa-comments"></i> Reviews & Feedbacks</a></li>
                <li class="logout"><a href="#logout" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- My Profile Section -->
            <section id="my-profile" class="dashboard-section active">
                <h2>My Profile</h2>
                <div class="profile-header">
                    <img src="<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'images/default-avatar.jpg'; ?>" alt="Profile Picture" class="profile-picture">
                    <h3><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h3>
                </div>

                <!-- Stats Cards -->
                <div class="stats-cards">
                    <div class="card">
                        <h4>Items Sold</h4>
                        <p><?php echo $stats['items_sold']; ?></p>
                    </div>
                    <div class="card">
                        <h4>Deals Completed</h4>
                        <p><?php echo $stats['deals_completed']; ?></p>
                    </div>
                    <div class="card">
                        <h4>My Rating</h4>
                        <p><?php echo number_format($stats['average_rating'] ?? 0, 1); ?>/5</p>
                    </div>
                </div>

                <!-- User Details -->
                <div class="profile-details">
                    <div class="profile-field">
                        <label>First Name:</label>
                        <p><?php echo $user['first_name']; ?></p>
                    </div>
                    <div class="profile-field">
                        <label>Last Name:</label>
                        <p><?php echo $user['last_name']; ?></p>
                    </div>
                    <div class="profile-field">
                        <label>Email:</label>
                        <p><?php echo $user['email']; ?></p>
                    </div>
                    <div class="profile-field">
                        <label>Phone Number:</label>
                        <p><?php echo $user['phone_number']; ?></p>
                    </div>
                    <div class="profile-field">
                        <label>Address:</label>
                        <p><?php echo $user['address']; ?></p>
                    </div>
                </div>

                <!-- Edit Profile Button -->
                <button class="edit-profile-btn" onclick="openEditProfileModal()"><i class="fas fa-edit"></i> Edit Profile</button>

                <!-- Change Password Button -->
                <button class="change-password-btn" onclick="openChangePasswordModal()"><i class="fas fa-key"></i> Change Password</button>
            </section>

            <!-- Edit Profile Modal -->
            <div id="editProfileModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeEditProfileModal()">&times;</span>
                    <h2>Edit Profile</h2>
                    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                        <!-- Profile Picture Upload -->
                        <div class="form-group">
                            <label for="profile-picture">Profile Picture:</label>
                            <input type="file" id="profile-picture" name="profile_picture" accept="image/*">
                            <small>Leave blank to keep the current image.</small>
                        </div>

                        <!-- First Name -->
                        <div class="form-group">
                            <label for="first-name">First Name:</label>
                            <input type="text" id="first-name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>

                        <!-- Last Name -->
                        <div class="form-group">
                            <label for="last-name">Last Name:</label>
                            <input type="text" id="last-name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>

                        <!-- Phone Number -->
                        <div class="form-group">
                            <label for="phone-number">Phone Number:</label>
                            <input type="text" id="phone-number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                        </div>

                        <!-- Address -->
                        <div class="form-group">
                            <label for="address">Address:</label>
                            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
                        </div>

                        <!-- Save Changes Button -->
                        <div class="form-actions">
                            <button type="button" class="cancel-btn" onclick="closeEditProfileModal()">Cancel</button>
                            <button type="submit" class="save-btn">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password Modal -->
            <div id="changePasswordModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeChangePasswordModal()">&times;</span>
                    <h2>Change Password</h2>
                    <form action="change_password.php" method="POST">
                        <label for="current-password">Current Password:</label>
                        <input type="password" id="current-password" name="current_password" required>

                        <label for="new-password">New Password:</label>
                        <input type="password" id="new-password" name="new_password" required>

                        <label for="confirm-password">Confirm New Password:</label>
                        <input type="password" id="confirm-password" name="confirm_password" required>

                        <button type="submit">Change Password</button>
                    </form>
                </div>
            </div>

            <!-- Other Sections (Hidden by Default) -->
            <!-- My Listed Items Section -->
            <section id="my-listed-items" class="dashboard-section">
                <h2>My Listed Items</h2>
                <button class="create-ad-btn" onclick="openCreateAdModal()">
                    <i class="fas fa-plus"></i> Create Ad
                </button>

            <!-- Ads Grid -->
            <div class="ads-grid">
                <?php if (!empty($ads)): ?>
                    <?php foreach ($ads as $ad): ?>
                        <div class="ad-card">
                            <div class="ad-image">
                                <img src="images/ads/<?php echo htmlspecialchars($ad['ad_image'] ?? 'default.jpg'); ?>" alt="Ad Image">
                            </div>
                            <div class="ad-details">
                                <h3><?php echo htmlspecialchars($ad['category_name']); ?></h3>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($ad['city_name']); ?></p>
                                <p><strong>Weight:</strong> <?php echo htmlspecialchars($ad['weight']); ?> kg</p>
                                <p><strong>Price:</strong> Rs. <?php echo number_format($ad['price'], 2); ?></p>
                                <p><strong>Status:</strong> <span class="status <?php echo strtolower($ad['status']); ?>"><?php echo htmlspecialchars($ad['status']); ?></span></p>
                                <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($ad['description'])); ?></p>
                            </div>
                            <div class="ad-actions">
                                <button class="edit-btn" onclick="openEditAdModal(
                                    <?php echo $ad['ad_id']; ?>,
                                    '<?php echo htmlspecialchars($ad['category_name']); ?>',
                                    '<?php echo htmlspecialchars($ad['city_name']); ?>',
                                    <?php echo htmlspecialchars($ad['weight']); ?>,
                                    <?php echo htmlspecialchars($ad['price']); ?>,
                                    '<?php echo htmlspecialchars($ad['status']); ?>',
                                    '<?php echo htmlspecialchars($ad['description']); ?>',
                                    '<?php echo htmlspecialchars($ad['ad_image']); ?>'
                                )">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="delete-btn" onclick="deleteAd(event, <?php echo $ad['ad_id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-ads-message">No ads listed yet.</p>
                <?php endif; ?>
            </div>

            <!-- Create Ad Modal -->
            <div id="createAdModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeCreateAdModal()">&times;</span>
                    <h2>Create New Ad</h2>
                    <form action="create_ad.php" method="POST" enctype="multipart/form-data">
                        <label for="ad-image">Item Image:</label>
                        <input type="file" id="ad-image" name="ad_image" accept="image/*" required>

                        <label for="ad-category">Category:</label>
                        <select id="ad-category" name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label for="ad-location">Location:</label>
                        <select id="ad-location" name="city_id" required>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo $city['id']; ?>"><?php echo htmlspecialchars($city['name_en']); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label for="ad-weight">Weight (kg):</label>
                        <input type="number" id="ad-weight" name="weight" step="0.01" min="0" required>

                        <label for="ad-price">Price (Rs):</label>
                        <input type="number" id="ad-price" name="price" step="0.01" min="0" required>

                        <label for="ad-description">Description:</label>
                        <textarea id="ad-description" name="description" rows="4" required></textarea>

                        <button type="submit">Post Ad</button>
                    </form>
                </div>
            </div>

            <!-- Edit Ad Modal -->
            <div id="EditAdModal" class="modal">
                <div class="modal-content">
                    <span class="close-btn" onclick="closeEditAdModal()">&times;</span>
                    <h2>Edit Ad</h2>
                    <form id="editAdForm" action="edit_ad.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" id="editAdId" name="ad_id">
                        <div class="form-group">
                            <label for="editCategory">Category</label>
                            <input type="text" id="editCategory" name="category" required>
                        </div>
                        <div class="form-group">
                            <label for="editLocation">Location</label>
                            <input type="text" id="editLocation" name="location" required>
                        </div>
                        <div class="form-group">
                            <label for="editWeight">Weight (kg)</label>
                            <input type="number" id="editWeight" name="weight" required>
                        </div>
                        <div class="form-group">
                            <label for="editPrice">Price (Rs.)</label>
                            <input type="number" id="editPrice" name="price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="editStatus">Status</label>
                            <select id="editStatus" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editDescription">Description</label>
                            <textarea id="editDescription" name="description" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="editImage">Ad Image</label>
                            <input type="file" id="editImage" name="ad_image" accept="image/*">
                            <small>Leave blank to keep the current image.</small>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="cancel-btn" onclick="closeEditAdModal()">Cancel</button>
                            <button type="submit" class="save-btn">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Deals Section -->
            <section id="deals" class="dashboard-section">
                <h2>Deals</h2>
                <div class="deals-grid">
                    <?php if (!empty($deals)): ?>
                        <?php foreach ($deals as $deal): ?>
                            <div class="deal-card">
                                <div class="deal-header">
                                    <h3>Deal #<?php echo $deal['deal_id']; ?></h3>
                                    <span class="status <?php echo strtolower($deal['deal_status']); ?>">
                                        <?php echo ucfirst($deal['deal_status']); ?>
                                    </span>
                                </div>
                                <div class="deal-details">
                                    <p><strong>Ad Title:</strong> <?php echo htmlspecialchars($deal['ad_description']); ?></p>
                                    <p><strong>Deal Price:</strong> Rs. <?php echo number_format($deal['deal_price'], 2); ?></p>
                                    <p><strong>Buyer:</strong> <?php echo htmlspecialchars($deal['buyer_name']); ?></p>
                                    <p><strong>Seller:</strong> <?php echo htmlspecialchars($deal['seller_name']); ?></p>
                                    <p><strong>Created At:</strong> <?php echo date('M d, Y', strtotime($deal['created_at'])); ?></p>
                                </div>
                                <div class="deal-actions">
                                    <?php if ($deal['deal_status'] === 'pending' && $deal['is_seller']): ?>
                                        <button class="accept-btn" onclick="updateDealStatus(<?php echo $deal['deal_id']; ?>, 'accepted')">
                                            Accept Deal
                                        </button>
                                        <button class="cancel-btn" onclick="updateDealStatus(<?php echo $deal['deal_id']; ?>, 'cancelled')">
                                            Cancel Deal
                                        </button>
                                    <?php elseif ($deal['deal_status'] === 'accepted' && $deal['is_buyer']): ?>
                                        <button class="complete-btn" onclick="updateDealStatus(<?php echo $deal['deal_id']; ?>, 'completed')">
                                            Mark as Completed
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No deals available.</p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Notifications Section -->
            <section id="notifications" class="dashboard-section">
                <h2>Notifications</h2>
                <div class="notifications-list">
                    <?php if (!empty($notifications)): ?>
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-item <?php echo $notification['status']; ?>">
                                <div class="notification-message">
                                    <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                </div>
                                <div class="notification-footer">
                                    <span class="status"><?php echo ucfirst($notification['status']); ?></span>
                                    <span class="date"><?php echo date('M d, Y', strtotime($notification['created_at'])); ?></span>
                                    <?php if ($notification['status'] === 'unread'): ?>
                                        <button class="mark-read-btn" onclick="markAsRead(<?php echo $notification['notification_id']; ?>)">
                                            Mark as Read
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No notifications available.</p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Favorites Section -->
            <section id="favorites" class="dashboard-section">
                <h2>Favorites</h2>
                <div class="favorites-grid">
                    <?php if (!empty($favorite_categories)): ?>
                        <?php foreach ($favorite_categories as $category): ?>
                            <div class="category-card">
                                <h3><?php echo htmlspecialchars($category['name_en']); ?></h3>
                                <button class="remove-favorite-btn" onclick="removeFavorite(<?php echo $category['category_id']; ?>)">
                                    <i class="fas fa-heart"></i> Remove from Favorites
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No favorites added.</p>
                    <?php endif; ?>
                </div>

                <!-- Add Favorites Button -->
                <button class="add-favorite-btn" onclick="openAddFavoritesModal()">
                    <i class="fas fa-plus"></i> Add Favorites
                </button>
            </section>

            <!-- Add Favorites Modal -->
            <div id="addFavoritesModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeAddFavoritesModal()">&times;</span>
                    <h2>Add Favorites</h2>
                    <div class="categories-list">
                        <?php foreach ($categories as $category): ?>
                            <div class="category-item">
                                <h3><?php echo htmlspecialchars($category['name_en']); ?></h3>
                                <?php if (in_array($category['category_id'], $favorite_ids)): ?>
                                    <button class="remove-favorite-btn" onclick="removeFavorite(<?php echo $category['category_id']; ?>)">
                                        <i class="fas fa-heart"></i> Remove from Favorites
                                    </button>
                                <?php else: ?>
                                    <button class="add-favorite-btn" onclick="addFavorite(<?php echo $category['category_id']; ?>)">
                                        <i class="far fa-heart"></i> Add to Favorites
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- My Rates Section -->
            <section id="my-rates" class="dashboard-section">
                <h2>My Rates</h2>
                <div class="rates-grid">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <div class="rate-card">
                                <h3><?php echo htmlspecialchars($category['name_en'] ?? ''); ?></h3>
                                <form class="rate-form" onsubmit="saveRate(event, <?php echo $category['category_id']; ?>)">
                                    <label for="price-per-kg-<?php echo $category['category_id']; ?>">Price per kg (Rs):</label>
                                    <input type="number" id="price-per-kg-<?php echo $category['category_id']; ?>" 
                                        name="price_per_kg" step="0.01" 
                                        value="<?php echo $user_rates[$category['category_id']] ?? ''; ?>" required>
                                    <button type="submit">Save</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No categories available.</p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Reviews & Feedbacks Section -->
            <section id="reviews-feedbacks" class="dashboard-section">
                <h2>Reviews & Feedbacks</h2>
                <div class="feedback-list">
                    <?php if (!empty($feedbacks)): ?>
                        <?php foreach ($feedbacks as $feedback): ?>
                            <div class="feedback-item">
                                <div class="feedback-header">
                                    <span class="rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $feedback['rating']): ?>
                                                <i class="fas fa-star"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </span>
                                    <span class="from-user">From: <?php echo htmlspecialchars($feedback['from_user_name']); ?></span>
                                    <span class="date"><?php echo date('M d, Y', strtotime($feedback['created_at'])); ?></span>
                                </div>
                                <div class="feedback-comment">
                                    <p><?php echo htmlspecialchars($feedback['comment']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No reviews or feedbacks received yet.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <script src="user_dashboard.js"></script>
</body>
</html>