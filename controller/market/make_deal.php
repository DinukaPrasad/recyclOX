<?php
// Start the session
session_start();

// Include the database connection file
require_once('../../config/db_connection.php');

// Initialize the session error variable
$_SESSION['make_deal_error'] = null;

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['make_deal_error'] = "You must be logged in to make a deal.";
    header("Location: view_ad.php?ad_id=" . $_GET['ad_id']);
    exit();
}

// Check if ad_id is provided in the URL
if (!isset($_GET['ad_id'])) {
    $_SESSION['make_deal_error'] = "Advertisement ID is missing.";
    header("Location: market.php");
    exit();
}

// Get ad_id and user_id
$ad_id = $_GET['ad_id'];
$user_id = $_SESSION['user_id'];

// Fetch the advertisement details to get the owner's user_id
$sql = "SELECT a.*, u.user_id AS owner_id 
        FROM advertisements a
        JOIN users u ON a.seller_id = u.user_id
        WHERE a.ad_id = $ad_id";
$result = $con->query($sql);

if (!$result || $result->num_rows === 0) {
    $_SESSION['make_deal_error'] = "Advertisement not found.";
    header("Location: market.php");
    exit();
}

$ad = $result->fetch_assoc();
$owner_id = $ad['owner_id']; // Owner of the advertisement

// Fetch the name of the user making the deal
$user_sql = "SELECT first_name FROM users WHERE user_id = $user_id";
$user_result = $con->query($user_sql);

if (!$user_result || $user_result->num_rows === 0) {
    $_SESSION['make_deal_error'] = "User not found.";
    header("Location: ../../view_ad.php?ad_id=" . $ad_id);
    exit();
}

$user = $user_result->fetch_assoc();
$user_name = $user['first_name']; // Name of the user making the deal

// Prepare the notification message
$message = $user_name . " would like to make a deal with you for the advertisement: " . $ad['description'];

// Insert the notification into the database
$insert_sql = "INSERT INTO notifications (user_id, message, created_at) 
               VALUES ($owner_id, '$message', NOW())";

if ($con->query($insert_sql)) {
    // Notification sent successfully
    $_SESSION['make_deal_success'] = "Deal request sent successfully!";
} else {
    // Handle database error
    $_SESSION['make_deal_error'] = "Error sending deal request: " . $con->error;
}

// Close the database connection
$con->close();

// Redirect back to the advertisement details page
header("Location: ../../view_ad.php?ad_id=" . $ad_id);
exit();
?>