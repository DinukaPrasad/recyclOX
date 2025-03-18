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
    header("Location: ../../view_buyer.php?buyer_id=" . $_GET['buyer_id']);
    exit();
}

// Check if buyer_id is provided in the URL
if (!isset($_GET['buyer_id'])) {
    $_SESSION['make_deal_error'] = "Buyer ID is missing.";
    header("Location: market.php");
    exit();
}

// Get buyer_id and user_id
$buyer_id = $_GET['buyer_id'];
$user_id = $_SESSION['user_id'];

// Fetch the buyer details to get the buyer's user_id
$sql = "SELECT u.user_id, u.first_name 
        FROM users u
        WHERE u.user_id = $buyer_id";
$result = $con->query($sql);

if (!$result || $result->num_rows === 0) {
    $_SESSION['make_deal_error'] = "Buyer not found.";
    header("Location: market.php");
    exit();
}

$buyer = $result->fetch_assoc();
$buyer_name = $buyer['first_name']; // Name of the buyer

// Fetch the name of the user making the deal
$user_sql = "SELECT first_name FROM users WHERE user_id = $user_id";
$user_result = $con->query($user_sql);

if (!$user_result || $user_result->num_rows === 0) {
    $_SESSION['make_deal_error'] = "User not found.";
    header("Location: ../../view_buyer.php?buyer_id=" . $buyer_id);
    exit();
}

$user = $user_result->fetch_assoc();
$user_name = $user['first_name']; // Name of the user making the deal

// Prepare the notification message
$message = $user_name . " would like to make a deal with you.";

// Insert the notification into the database
$insert_sql = "INSERT INTO notifications (user_id, message, created_at) 
               VALUES ($buyer_id, '$message', NOW())";

if ($con->query($insert_sql)) {
    // Notification sent successfully
    $_SESSION['make_deal_success'] = "Deal request sent successfully to " . $buyer_name . "!";
} else {
    // Handle database error
    $_SESSION['make_deal_error'] = "Error sending deal request: " . $con->error;
}

// Close the database connection
$con->close();

// Redirect back to the buyer details page
header("Location: ../../view_buyer.php?buyer_id=" . $buyer_id);
exit();
?>