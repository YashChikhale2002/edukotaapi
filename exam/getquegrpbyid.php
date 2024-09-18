<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include the configuration file
include_once '../config.php'; // Ensure this file sets up the $pdo variable

// Get the group ID from the request
$groupId = isset($_GET['gid']) ? intval($_GET['gid']) : 0;

// Validate the group ID
if ($groupId <= 0) {
    echo json_encode(['error' => 'Invalid group ID']);
    exit;
}

try {
    // Prepare and execute the query
    $stmt = $pdo->prepare('SELECT * FROM question WHERE gid = :group_id');
    $stmt->execute(['group_id' => $groupId]);
    
    // Fetch and return the questions
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($questions);
} catch (PDOException $e) {
    // Return error message if query fails
    echo json_encode(['error' => 'Failed to fetch questions']);
}
?>
