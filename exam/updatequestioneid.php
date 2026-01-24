<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config.php';

if (!$pdo) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// ✅ GET POST DATA
$data = json_decode(file_get_contents("php://input"));

// ✅ EXTRACT FIELDS
$questionId = isset($data->questionId) ? $data->questionId : null;
$examId = isset($data->examId) ? $data->examId : null;

// Validate required field
if (!$questionId) {
    echo json_encode(['status' => 'error', 'message' => 'Question ID is required']);
    exit();
}

// Check if examId is not provided, set it to NULL
if ($examId === '' || $examId === null) {
    $examId = null;
}

try {
    // Update EID for the question
    $sql = "UPDATE question SET eid = :examId WHERE qid = :questionId";
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    if ($examId === null) {
        $stmt->bindValue(':examId', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindParam(':examId', $examId, PDO::PARAM_INT);
    }
    $stmt->bindParam(':questionId', $questionId, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Question updated successfully',
            'qid' => $questionId,
            'eid' => $examId
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update question']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

$pdo = null;
?>
