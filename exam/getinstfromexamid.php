<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include the database connection
include_once '../config.php'; // Ensure this file sets up the $pdo variable

// Get the exam ID from the request
$eid = isset($_GET['eid']) ? intval($_GET['eid']) : 0;

// Validate the exam ID
if ($eid <= 0) {
    echo json_encode(['error' => 'Invalid exam ID']);
    exit;
}

try {
    // Prepare and execute the query
    $stmt = $pdo->prepare('SELECT o.eid, o.name, o.duration, o.tmarks, i.content
    FROM onlineexam o
    JOIN instructions i ON o.insid = i.insid
    WHERE o.eid = :eid');
    $stmt->execute(['eid' => $eid]);

    // Fetch and return the data
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($exam) {
        echo json_encode($exam);
    } else {
        echo json_encode(['error' => 'No content found for the given exam ID']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Failed to fetch content: ' . $e->getMessage()]);
}
?>
