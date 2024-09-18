<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Include the configuration file
include '../config.php'; // Adjust the path as needed

// Retrieve DELETE data
$input = json_decode(file_get_contents('php://input'), true);
$questionId = $input['qid'];

// Validate input
if (empty($questionId)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit();
}

try {
    // Remove the question
    $sql = "DELETE FROM question WHERE qid = :questionId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':questionId', $questionId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Question removed successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to remove question']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

$pdo = null;
?>
