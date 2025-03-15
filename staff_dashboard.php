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

// staff add error
$errors = $_SESSION['staff_error'] ?? '';

$userName = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./asset/css/admin_dash.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h2>RecyclOX admin dashboard</h2>
            </div>
            <ul class="nav-links">
                <li><a href="#" data-target="dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="#" data-target="deals"><i class="fas fa-calendar"></i> Deals</a></li>
                <li><a href="#" data-target="advertisements"><i class="fas fa-users"></i> Advertisements</a></li>
                <li><a href="#" data-target="users"><i class="fas fa-user-md"></i> Users</a></li>
                <li><a href="#" data-target="garbageCat"><i class="fas fa-file"></i> Garbage Category</a></li>
                <li><a href="#" data-target="schedule"><i class="fas fa-file"></i> Garbage Collecting schedule</a></li>
                <li><a href="#" data-target="ratings"><i class="fas fa-file"></i> Garbage Ratings</a></li>
                <li><a href="#" data-target="userRatings"><i class="fas fa-file"></i> Users Ratings</a></li>
                <li><a href="#" data-target="notifications"><i class="fas fa-file"></i> notifications</a></li>
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
                                        <td><?php echo htmlspecialchars($deal['deal_id']); ?></td>
                                        <td><?php echo htmlspecialchars($deal['buyer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($deal['ad_id']); ?></td>
                                        <td><?php echo htmlspecialchars($deal['seller_name']); ?></td>
                                        <td><?php echo htmlspecialchars($deal['deal_price']); ?></td>
                                        <td><?php echo htmlspecialchars($deal['created_at']); ?></td>
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

                <!-- Forms Container -->
                <div class="forms-container">
                    <!-- Add New Doctor Form -->
                    <div class="add-doctor">
                        <h3>demo-component</h3>
                        

                            
                    </div>

                    <!-- Add New Staff Member Form -->
                    <div class="add-staff">
                        <h3>Add New Staff Member</h3>
                        <?php if (isset($errors)): ?>
                            <p><?php echo $errors; ?></p>
                        <?php endif; ?>
                        <form method="POST" action="./controller/staff_add_function.php">
                            <input type="text" id="staff-first-name" name="first_name" placeholder="First Name:" required>
                            <input type="text" id="staff-last-name" name="last_name" placeholder="Last Name:" required>
                            <input type="email" id="staff-email" name="email" placeholder="Email:" required>
                            <input type="password" id="staff-password" name="password" placeholder="Password:" required>
                            <input type="text" id="staff-address" name="address" placeholder="Address:" required>
                            <input type="text" id="staff-phone" name="phone_number" placeholder="Phone Number:" required>
                            <select name="role" id="staff-role" required>
                                <option value="">-- select role --</option>
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                            </select>

                            <button type="submit">Add Staff</button>
                        </form>
                    </div>
                </div>
            </div>

                <div id="deals-content" class="content-section">
                    <h2>Deals</h2>
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
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
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
                                            <td>
                                                <a href="./controller/edit_deal.php?id=<?= $deal['deal_id']; ?>">Edit</a> |
                                                <a href="./controller/delete_deal.php?id=<?= $deal['deal_id']; ?>" onclick="return confirm('Are you sure you want to delete this deal?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8">No deals found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="advertisements-content" class="content-section">
                    <h2></h2>
                    <table>
                        
                    </table>
                </div>

                <div id="users-content" class="content-section">
                    <h2></h2>
                    
                </div>

                <div id="garbageCat-content" class="content-section">
                    <h2>
                        
                    </h2>
                    
                </div>

                <div id="schedule-content" class="content-section">

                </div>

                <div id="ratings-content" class="content-section">

                </div>

                <div id="userRatings-content" class="content-section">

                </div>

                <div id="notifications-content" class="content-section">

                </div>
        </div>
    </div>

    <script src="./asset/js/admin_dash.js"></script>
</body>
</html>