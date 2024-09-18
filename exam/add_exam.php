<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include the configuration file
include_once '../config.php';

// Decode the JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Check if all required fields are present
if (isset($input['name'], $input['class'], $input['insid'], $input['estatus'], $input['duration'], $input['tmarks'], $input['date'])) {
    $name = $input['name'];
    $class = $input['class'];
    $insid = $input['insid'];
    $estatus = $input['estatus'];
    $duration = $input['duration'];
    $tmarks = $input['tmarks'];
    $date = $input['date'];

    try {
        // Prepare the SQL statement
        $query = "INSERT INTO onlineexam (name, class, insid, estatus, duration, tmarks, date) VALUES (:name, :class, :insid, :estatus, :duration, :tmarks, :date)";
        $stmt = $pdo->prepare($query);

        // Bind parameters
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':class', $class);
        $stmt->bindParam(':insid', $insid);
        $stmt->bindParam(':estatus', $estatus);
        $stmt->bindParam(':duration', $duration);
        $stmt->bindParam(':tmarks', $tmarks);
        $stmt->bindParam(':date', $date);

        // Execute the query
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Exam added successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add exam.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
}

// Close the connection (handled automatically by PDO)
?>
