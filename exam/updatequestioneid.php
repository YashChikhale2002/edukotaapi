<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include the configuration file
include '../config.php'; // Adjust the path as needed

// Retrieve POST data
$input = json_decode(file_get_contents('php://input'), true);
$questionId = $input['qid'];
$examId = $input['eid'];

// Validate inputs
if (empty($questionId)) {
    echo json_encode(['status' => 'error', 'message' => 'Question ID is required']);
    exit();
}

// Check if examId is not provided, set it to NULL
if ($examId === '') {
    $examId = null; // Set to NULL if empty string
}

try {
    // Update EID for the question
    $sql = "UPDATE question SET eid = :examId WHERE qid = :questionId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':examId', $examId, $examId === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindParam(':questionId', $questionId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Question updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update question']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

$pdo = null;
?>
