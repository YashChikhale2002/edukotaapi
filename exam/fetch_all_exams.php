<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include the configuration file
include_once '../config.php';

// Prepare and execute the query
try {
    $query = "SELECT * FROM onlineexam";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($exams);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
}

// Close the connection (handled automatically by PDO)
?>
    