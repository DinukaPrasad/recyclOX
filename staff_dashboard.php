<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ./login_register.php");
    exit();
}

require_once('./config/db_connection.php');

// Fetch total user count
$userCountQuery = $con->query("SELECT COUNT(*) AS total_users FROM Users");
$userCount = $userCountQuery->fetch_assoc()['total_users'];

// Fetch total pending deals count
$pendingDealsQuery = $con->query("SELECT COUNT(*) AS pending_deals FROM Deals WHERE deal_status = 'pending'");
$pendingDealsCount = $pendingDealsQuery->fetch_assoc()['pending_deals'];

// Fetch total advertisement count
$advertisementCountQuery = $con->query("SELECT COUNT(*) AS total_ads FROM Advertisements");
$advertisementCount = $advertisementCountQuery->fetch_assoc()['total_ads'];

// Fetch all pending deals with buyer and seller details
$pendingDealsQuery = $con->query("
    SELECT 
        d.deal_id, 
        d.deal_price, 
        d.created_at, 
        b.first_name AS buyer_name, 
        s.first_name AS seller_name, 
        a.ad_id
    FROM Deals d
    JOIN Users b ON d.buyer_id = b.user_id
    JOIN Advertisements a ON d.ad_id = a.ad_id
    JOIN Users s ON a.seller_id = s.user_id
    WHERE d.deal_status = 'pending'
");
$pendingDeals = $pendingDealsQuery->fetch_all(MYSQLI_ASSOC);

// Fetch all deals from the database
$dealsQuery = $con->query("
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
");
$allDeals = $dealsQuery->fetch_all(MYSQLI_ASSOC);

// Fetch advertisement details
$advertisementsQuery = $con->query("
    SELECT 
        a.ad_id, 
        a.seller_id, 
        gc.category_name,
        a.weight, 
        a.description, 
        a.status, 
        a.created_at, 
        a.updated_at, 
        u.first_name AS seller_name
    FROM Advertisements a
    JOIN Users u ON a.seller_id = u.user_id
    JOIN GarbageCategory gc ON a.category_id = gc.category_id
");
$advertisements = $advertisementsQuery->fetch_all(MYSQLI_ASSOC);

// Fetch users for the current page
$usersQuery = $con->query("
    SELECT 
        user_id,
        first_name,
        last_name,
        email,
        address,
        phone_number,
        role,
        status,
        created_at
    FROM users
");
$users = $usersQuery->fetch_all(MYSQLI_ASSOC);

// Fetch all garbage categories
$garbageCategoriesQuery = $con->query("
    SELECT 
        category_id,
        category_name,
        created_at,
        updated_at
    FROM garbagecategory
");
$garbageCategories = $garbageCategoriesQuery->fetch_all(MYSQLI_ASSOC);

// Fetch all garbage schedules
$schedulesQuery = $con->query("
    SELECT 
        schedule_id,
        location,
        collection_date,
        collection_time
    FROM garbagecollectionschedule
");
$schedules = $schedulesQuery->fetch_all(MYSQLI_ASSOC);

// Fetch all garbage ratings with buyer name and garbage category name
$garbageRatingsQuery = $con->query("
    SELECT 
        gr.buyer_id,
        u.first_name AS buyer_name,  -- Fetch buyer name
        gr.category_id,
        gc.category_name,           -- Fetch garbage category name
        gr.price_per_kg,
        gr.created_at,
        gr.updated_at
    FROM GarbageRatings gr
    JOIN Users u ON gr.buyer_id = u.user_id  -- Join Users table
    JOIN GarbageCategory gc ON gr.category_id = gc.category_id  -- Join GarbageCategory table
");
$garbageRatings = $garbageRatingsQuery->fetch_all(MYSQLI_ASSOC);

// Fetch all user ratings (feedback) with user names
$userRatingsQuery = $con->query("
    SELECT 
        f.feedback_id,
        f.deal_id,
        f.from_user_id,
        fu.first_name AS from_user_name,  -- Fetch from user name
        f.to_user_id,
        tu.first_name AS to_user_name,    -- Fetch to user name
        f.rating,
        f.comment,
        f.created_at
    FROM Feedback f
    JOIN Users fu ON f.from_user_id = fu.user_id  -- Join Users table for from_user
    JOIN Users tu ON f.to_user_id = tu.user_id    -- Join Users table for to_user
");
$userRatings = $userRatingsQuery->fetch_all(MYSQLI_ASSOC);

// Fetch all notifications with user names
$notificationsQuery = $con->query("
    SELECT 
        n.notification_id,
        n.user_id,
        u.first_name AS user_name,  
        n.message,
        n.status,
        n.created_at
    FROM Notifications n
    JOIN Users u ON n.user_id = u.user_id  -- Join Users table
");
$notifications = $notificationsQuery->fetch_all(MYSQLI_ASSOC);


// Staff add error
$errors = $_SESSION['staff_error'] ?? '';

$userName = $_SESSION['name'];

$_SESSION['role'] = "staff";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./asset/css/admin_dash.css">
    <link rel="stylesheet" href="./asset/css/searchbar.css">

    <script>
        // Pass the logged-in user's role to JavaScript
        const loggedUserRole = "<?php echo $_SESSION['role']; ?>";
        console.log("Logged User Role (PHP to JS):", loggedUserRole); // Debugging
    </script>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h2>RecyclOX Staff Dashboard</h2>
            </div>
            <ul class="nav-links">
                <li><a href="#" data-target="dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="#" data-target="deals"><i class="fas fa-calendar"></i> Deals</a></li>
                <li><a href="#" data-target="advertisements"><i class="fas fa-users"></i> Advertisements</a></li>
                <li><a href="#" data-target="users"><i class="fas fa-user-md"></i> Users</a></li>
                <li><a href="#" data-target="garbageCat"><i class="fas fa-file"></i> Garbage Category</a></li>
                <li><a href="#" data-target="schedule"><i class="fas fa-file"></i> Garbage Collecting Schedule</a></li>
                <li><a href="#" data-target="ratings"><i class="fas fa-file"></i> Garbage Ratings</a></li>
                <li><a href="#" data-target="userRatings"><i class="fas fa-file"></i> User Feedbacks</a></li>
                <li><a href="#" data-target="notifications"><i class="fas fa-file"></i> Notifications</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header>
                <h1>Welcome, <?php echo $userName; ?></h1>
                <div class="search-bar">
                    <a href="./controller/logout_function.php" class="btn">Logout</a>
                </div>
            </header>

            <!-- Metrics -->
            <div class="metrics">
                <div class="metric-card">
                    <h2><?php echo $userCount; ?></h2>
                    <p>Total Users</p>
                </div>
                <div class="metric-card">
                    <h2><?php echo $pendingDealsCount; ?></h2>
                    <p>Pending Deals</p>
                </div>
                <div class="metric-card">
                    <h2><?php echo $advertisementCount; ?></h2>
                    <p>Total Advertisements</p>
                </div>
            </div>

            <div id="dashboard-content" class="content-section active">
                <h2>Dashboard Overview</h2>

                <!-- Pending Deals Table -->
                <div class="pending-appointments">
                    <h3>Pending Deals</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Deal ID</th>
                                <th>Buyer Name</th>
                                <th>Ad ID</th>
                                <th>Seller Name</th>
                                <th>Deal Price ($)</th>
                                <th>Date / Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pendingDeals)): ?>
                                <?php foreach ($pendingDeals as $deal): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($deal['deal_id']); ?></td>
                                        <td><?= htmlspecialchars($deal['buyer_name']); ?></td>
                                        <td><?= htmlspecialchars($deal['ad_id']); ?></td>
                                        <td><?= htmlspecialchars($deal['seller_name']); ?></td>
                                        <td><?= htmlspecialchars($deal['deal_price']); ?></td>
                                        <td><?= htmlspecialchars($deal['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No pending deals found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Deals Content -->
            <div id="deals-content" class="content-section">
                <h2>Deals</h2>
                <!-- Search and Filters -->
                <div class="filters">
                    <input type="text" id="searchInput" placeholder="Search by Buyer or Seller Name..." onkeyup="filterDeals()">
                    <select id="statusFilter" onchange="filterDeals()">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="accepted">Accepted</option>
                    </select>
                    <select id="priceFilter" onchange="filterDeals()">
                        <option value="">All Prices</option>
                        <option value="0-100">$0 - $100</option>
                        <option value="100-500">$100 - $500</option>
                        <option value="500-1000">$500 - $1000</option>
                    </select>
                </div>

                <!-- Deals Table -->
                <div class="all-deals">
                    <h3>All Deals</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Deal ID</th>
                                <th>Buyer Name</th>
                                <th>Seller Name</th>
                                <th>Ad ID</th>
                                <th>Deal Price ($)</th>
                                <th>Created At</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="dealsTableBody">
                            <?php if (!empty($allDeals)): ?>
                                <?php foreach ($allDeals as $deal): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($deal['deal_id']); ?></td>
                                        <td><?= htmlspecialchars($deal['buyer_name']); ?></td>
                                        <td><?= htmlspecialchars($deal['seller_name']); ?></td>
                                        <td><?= htmlspecialchars($deal['ad_id']); ?></td>
                                        <td><?= htmlspecialchars($deal['deal_price']); ?></td>
                                        <td><?= htmlspecialchars($deal['created_at']); ?></td>
                                        <td><?= htmlspecialchars($deal['deal_status']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">No deals found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Advertisements Content -->
            <div id="advertisements-content" class="content-section">
                <h2>Advertisements</h2>
                <!-- Search and Filters -->
                <div class="filters">
                    <input type="text" id="adSearchInput" placeholder="Search by Seller Name..." onkeyup="filterAdvertisements()">
                    <select id="statusFilterAd" onchange="filterAdvertisements()">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="sold">Sold</option>
                        <option value="expired">Expired</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <select id="categoryFilterAd" onchange="filterAdvertisements()">
                        <option value="">All Categories</option>
                        <?php
                        // Fetch all categories for the dropdown
                        $categoriesQuery = $con->query("SELECT category_name FROM GarbageCategory");
                        while ($category = $categoriesQuery->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($category['category_name']) . "'>" . htmlspecialchars($category['category_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Advertisements Table -->
                <div class="all-advertisements">
                    <h3>All Advertisements</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Ad ID</th>
                                <th>Seller Name</th>
                                <th>Category Name</th>
                                <th>Weight (kg)</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                            </tr>
                        </thead>
                        <tbody id="advertisementsTableBody">
                            <?php if (!empty($advertisements)): ?>
                                <?php foreach ($advertisements as $ad): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($ad['ad_id']); ?></td>
                                        <td><?= htmlspecialchars($ad['seller_name']); ?></td>
                                        <td><?= htmlspecialchars($ad['category_name']); ?></td>
                                        <td><?= htmlspecialchars($ad['weight']); ?></td>

                                        <td><?= htmlspecialchars($ad['description']); ?></td>
                                        <td><?= htmlspecialchars($ad['status']); ?></td>
                                        <td><?= htmlspecialchars($ad['created_at']); ?></td>
                                        <td><?= htmlspecialchars($ad['updated_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10">No advertisements found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Users Content -->
            <div id="users-content" class="content-section">
                <h2>Users Details</h2>
                <!-- Search and Filters -->
                <div class="filters">
                    <input type="text" id="userSearchInput" placeholder="Search by Name, Address or Email..." onkeyup="filterUsers()">
                    <select id="roleFilter" onchange="filterUsers()">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                        <option value="staff">Staff</option>
                    </select>
                    <select id="statusFilterUser" onchange="filterUsers()">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>

                <!-- Users Table -->
                <div class="all-users">
                    <h3>All Users</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Phone Number</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['user_id']); ?></td>
                                        <td><?= htmlspecialchars($user['first_name']); ?></td>
                                        <td><?= htmlspecialchars($user['last_name']); ?></td>
                                        <td><?= htmlspecialchars($user['email']); ?></td>
                                        <td><?= htmlspecialchars($user['address']); ?></td>
                                        <td><?= htmlspecialchars($user['phone_number']); ?></td>
                                        <td><?= htmlspecialchars($user['role']); ?></td>
                                        <td><?= htmlspecialchars($user['status']); ?></td>
                                        <td><?= htmlspecialchars($user['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- garbageCat Content -->
            <div id="garbageCat-content" class="content-section">
                <h2>Garbage Categories</h2>

                <!-- Add New Category Form -->
                <div class="add-new-cat">
                    <h3>Add New Category</h3>
                    <form method="POST" action="./controller/add_garbage_cat.php">
                        <input type="text" id="category-name" name="category_name" placeholder="Enter Category Name" required>
                        <button type="submit">Add Category</button>
                    </form>
                </div>

            <!-- All Garbage Categories Table -->
                <div class="all-garbage-categories">
                    <h3>All Garbage Categories</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Category ID</th>
                                <th>Category Name</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($garbageCategories)): ?>
                                <?php foreach ($garbageCategories as $category): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($category['category_id']); ?></td>
                                        <td><?= htmlspecialchars($category['category_name']); ?></td>
                                        <td><?= htmlspecialchars($category['created_at']); ?></td>
                                        <td><?= htmlspecialchars($category['updated_at']); ?></td>
                                        <td>
                                            <a href="./controller/edit_category.php?id=<?= $category['category_id']; ?>">Edit</a> |
                                            <a href="./controller/delete_category.php?id=<?= $category['category_id']; ?>" onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No garbage categories found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>    

            <div id="schedule-content" class="content-section">
                <h2>Garbage Collection Schedules</h2>
                <div class="all-schedules">
                    <h3>All Schedules</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Schedule ID</th>
                                <th>Location</th>
                                <th>Collection Date</th>
                                <th>Collection Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($schedules)): ?>
                                <?php foreach ($schedules as $schedule): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($schedule['schedule_id']); ?></td>
                                        <td><?= htmlspecialchars($schedule['location']); ?></td>
                                        <td><?= htmlspecialchars($schedule['collection_date']); ?></td>
                                        <td><?= htmlspecialchars($schedule['collection_time']); ?></td>
                                        <td>
                                            <a href="./controller/edit_schedule.php?id=<?= $schedule['schedule_id']; ?>">Edit</a> |
                                            <a href="./controller/delete_schedule.php?id=<?= $schedule['schedule_id']; ?>" onclick="return confirm('Are you sure you want to delete this schedule?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">No schedules found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Garbage Ratings Content -->            
            <div id="ratings-content" class="content-section">
                <h2>Garbage Ratings</h2>
                <!-- Search, Dropdown, and Sort Button -->
                <div class="filters">
                    <!-- Search Bar -->
                    <input type="text" id="ratingSearchInput" placeholder="Search by Buyer or Category..." onkeyup="filterGarbageRatings()">
                    
                    <!-- Dropdown for Categories -->
                    <select id="categoryFilter" onchange="filterGarbageRatings()">
                        <option value="">All Categories</option>
                        <?php
                        // Fetch all categories for the dropdown
                        $categoriesQuery = $con->query("SELECT category_name FROM GarbageCategory");
                        while ($category = $categoriesQuery->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($category['category_name']) . "'>" . htmlspecialchars($category['category_name']) . "</option>";
                        }
                        ?>
                    </select>
                    
                    <!-- Sort Button -->
                    <button onclick="sortGarbageRatings()">Sort by Price (High to Low)</button>
                </div>

                <!-- Garbage Ratings Table -->
                <div class="all-garbage-ratings">
                    <h3>All Garbage Ratings</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Buyer Name</th>
                                <th>Garbage Category</th>
                                <th>Price Per Kg ($)</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                            </tr>
                        </thead>
                        <tbody id="garbageRatingsTableBody">
                            <?php if (!empty($garbageRatings)): ?>
                                <?php foreach ($garbageRatings as $rating): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($rating['buyer_name']); ?></td>
                                        <td><?= htmlspecialchars($rating['category_name']); ?></td>
                                        <td><?= htmlspecialchars($rating['price_per_kg']); ?></td>
                                        <td><?= htmlspecialchars($rating['created_at']); ?></td>
                                        <td><?= htmlspecialchars($rating['updated_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No garbage ratings found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- User Ratings Content -->
            <div id="userRatings-content" class="content-section">
                <h2>User Ratings</h2>
                <!-- Search and Filters -->
                <div class="filters">
                    <input type="text" id="userRatingSearchInput" placeholder="Search by User Name..." onkeyup="filterUserRatings()">
                    <select id="ratingFilter" onchange="filterUserRatings()">
                        <option value="">All Ratings</option>
                        <option value="1">1 Star</option>
                        <option value="2">2 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="5">5 Stars</option>
                    </select>
                </div>

                <!-- User Ratings Table -->
                <div class="all-user-ratings">
                    <h3>All User Ratings</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Feedback ID</th>
                                <th>Deal ID</th>
                                <th>From User (Seller)</th>
                                <th>To User (Buyer)</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Created At</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="userRatingsTableBody">
                            <?php if (!empty($userRatings)): ?>
                                <?php foreach ($userRatings as $rating): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($rating['feedback_id']); ?></td>
                                        <td><?= htmlspecialchars($rating['deal_id']); ?></td>
                                        <td><?= htmlspecialchars($rating['from_user_name']); ?></td>
                                        <td><?= htmlspecialchars($rating['to_user_name']); ?></td>
                                        <td><?= htmlspecialchars($rating['rating']); ?></td>
                                        <td><?= htmlspecialchars($rating['comment']); ?></td>
                                        <td><?= htmlspecialchars($rating['created_at']); ?></td>
                                        <td>
                                            <a href="./controller/delete_user_rating.php?id=<?= $rating['feedback_id']; ?>" onclick="return confirm('Are you sure you want to delete this rating?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8">No user ratings found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notifications Content -->
            <div id="notifications-content" class="content-section">
                <!-- Notifications Table -->
                <div class="all-notifications">
                    <h3>All Notifications</h3>

                    <!-- Search and Filters -->
                    <div class="filters">
                        <input type="text" id="notificationSearchInput" placeholder="Search by User Name..." onkeyup="filterNotifications()">
                        <select id="statusFilterNotification" onchange="filterNotifications()">
                            <option value="">All Statuses</option>
                            <option value="unread">Unread</option>
                            <option value="read">Read</option>
                        </select>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Notification ID</th>
                                <th>User Name</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="notificationsTableBody">
                            <?php if (!empty($notifications)): ?>
                                <?php foreach ($notifications as $notification): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($notification['notification_id']); ?></td>
                                        <td><?= htmlspecialchars($notification['user_name']); ?></td>
                                        <td><?= htmlspecialchars($notification['message']); ?></td>
                                        <td><?= htmlspecialchars($notification['status']); ?></td>
                                        <td><?= htmlspecialchars($notification['created_at']); ?></td>
                                        <td>
                                            <a href="./controller/delete_notification.php?id=<?= $notification['notification_id']; ?>" onclick="return confirm('Are you sure you want to delete this notification?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No notifications found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

             
      
    <script src="./asset/js/admin_dash.js"></script>
    <script src="./asset/js/deal_search.js"></script>
    <script src="./asset/js/adds_search.js"></script>
    <script src="./asset/js/user_search_staff.js"></script>
    <script src="./asset/js/garbage_rating_search.js"></script>
    <script src="./asset/js/feedback_search.js"></script>
    <script src="./asset/js/msg_search.js"></script>

</body>
</html>