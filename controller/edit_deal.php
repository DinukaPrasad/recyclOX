<?php
// Include the database connection file
require_once('../config/db_connection.php');

// Check if the ID is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the deal details
    $sql = "SELECT * FROM Deals WHERE deal_id = $id";
    $result = $con->query($sql);

    if ($result->num_rows > 0) {
        $deal = $result->fetch_assoc();
    } else {
        die("Deal not found.");
    }
} else {
    die("Invalid request.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dealPrice = $_POST['deal_price'];
    $dealStatus = $_POST['deal_status'];

    // Update the deal in the database
    $updateSql = "UPDATE Deals SET deal_price = '$dealPrice', deal_status = '$dealStatus' WHERE deal_id = $id";
    if ($con->query($updateSql)) {
        header("Location: ../admin_dashboard.php");
        exit();
    } else {
        die("Error updating deal: " . $con->error);
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
    <title>Edit Deal</title>
</head>
<body>
    <h1>Edit Deal</h1>
    <form method="POST" action="">
        <label for="deal_price">Deal Price:</label>
        <input type="number" id="deal_price" name="deal_price" value="<?= htmlspecialchars($deal['deal_price']); ?>" required><br><br>

        <label for="deal_status">Status:</label>
        <select id="deal_status" name="deal_status" required>
            <option value="pending" <?= $deal['deal_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="completed" <?= $deal['deal_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
            <option value="cancelled" <?= $deal['deal_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            <option value="accepted" <?= $deal['deal_status'] === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
        </select><br><br>

        <button type="submit">Update Deal</button>
    </form>
</body>
</html>