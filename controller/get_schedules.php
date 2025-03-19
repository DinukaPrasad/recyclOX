<?php
header('Content-Type: application/json');
require_once('../config/db_connection.php'); // Adjust the path as needed

if (isset($_GET['city']) && !empty($_GET['city'])) {
    $city = trim($_GET['city']);

    try {
        // Fetch schedules for the selected city
        $sql = "SELECT * FROM GarbageSchedule 
                WHERE postal_code IN (SELECT postal_code FROM Location WHERE city = ?)";
        $stmt = $con->prepare($sql);

        if (!$stmt) {
            throw new Exception("Database error: " . $con->error);
        }

        $stmt->bind_param('s', $city);
        $stmt->execute();
        $result = $stmt->get_result();

        $schedules = [];
        while ($row = $result->fetch_assoc()) {
            $schedules[] = $row;
        }

        // Return the schedules as JSON
        echo json_encode(['status' => 'success', 'data' => $schedules]);
    } catch (Exception $e) {
        // Handle any errors
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    } finally {
        // Close the statement and connection
        if (isset($stmt)) {
            $stmt->close();
        }
        $con->close();
    }
} else {
    // Return an error if the 'city' parameter is missing
    echo json_encode(['status' => 'error', 'message' => 'City parameter is required']);
}
?>