<?php
require_once('../config/db_connection.php');

// Get the category ID from the URL
$categoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($categoryId <= 0) {
    // If the category ID is invalid, redirect to the dashboard
    header("Location: ../admin_dashboard.php");
    exit();
}

// Fetch the category details
$query = "SELECT * FROM GarbageCategory WHERE category_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param('i', $categoryId);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

if (!$category) {
    // If the category is not found, redirect to the dashboard
    header("Location: ../admin_dashboard.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the new category name from the form
    $categoryName = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';

    // Validate the input
    if (empty($categoryName)) {
        $error = "Category name is required.";
    } else {
        // Prepare the SQL query to update the category name
        $updateQuery = "UPDATE GarbageCategory SET category_name = ? WHERE category_id = ?";
        $updateStmt = $con->prepare($updateQuery);

        // Check if the statement was prepared successfully
        if (!$updateStmt) {
            $error = "Error preparing statement: " . $con->error;
        } else {
            // Bind the parameters and execute the query
            $updateStmt->bind_param('si', $categoryName, $categoryId);
            if ($updateStmt->execute()) {
                // Redirect back to the dashboard with a success message
                header("Location: ../admin_dashboard.php?success=1");
                exit();
            } else {
                $error = "Error updating category: " . $updateStmt->error;
            }

            // Close the statement
            $updateStmt->close();
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
    <title>Edit Category</title>
    <style>
        .error { color: red; }
    </style>
</head>
<body>
    <h2>Edit Category</h2>

    <!-- Display error message if any -->
    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <!-- Edit Form -->
    <form method="POST" action="">
        <input type="hidden" name="category_id" value="<?= $category['category_id']; ?>">
        <label for="category_name">Category Name:</label>
        <input type="text" id="category_name" name="category_name" value="<?= htmlspecialchars($category['category_name']); ?>" required>
        <button type="submit">Update Category</button>
    </form>

    <!-- Cancel Button -->
    <br>
    <a href="../admin_dashboard.php">Cancel</a>
</body>
</html>