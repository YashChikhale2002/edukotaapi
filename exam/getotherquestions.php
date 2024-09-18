<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include the configuration file
include '../config.php'; // Adjust the path as needed

// Get the exam ID from the request
$eid = isset($_GET['eid']) ? intval($_GET['eid']) : 0;

if ($eid <= 0) {
    echo json_encode(['error' => 'Invalid exam ID']);
    exit;
}

// Prepare and execute the query to fetch other questions
try {
    // Query to fetch questions where `eid` is either NULL or not equal to the provided `eid`
    $stmt = $pdo->prepare('SELECT * FROM question WHERE eid != :eid OR eid IS NULL');
    $stmt->execute(['eid' => $eid]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($questions);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Failed to fetch questions: ' . $e->getMessage()]);
}
?>
    