<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include the configuration file
include_once '../config.php';

// Check if 'start_date' and 'end_date' are passed as query parameters
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];

    try {
        // Prepare the query to fetch users based on the date range
        $query = "SELECT * FROM `users` WHERE `examdate` BETWEEN :start_date AND :end_date";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();

        // Fetch the results
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the users in JSON format
        echo json_encode($users);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Please provide both start_date and end_date']);
}
?>
