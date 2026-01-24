<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config.php';

if (!$pdo) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// ✅ GET POST DATA
$data = json_decode(file_get_contents("php://input"));

// ✅ EXTRACT FIELD (support both old and new field names)
$questionId = isset($data->questionId) ? $data->questionId : (isset($data->qid) ? $data->qid : null);

// Validate required field
if (!$questionId) {
    echo json_encode(['status' => 'error', 'message' => 'Question ID is required']);
    exit();
}

try {
    // Set eid to NULL to remove question from exam (don't delete the question)
    $sql = "UPDATE question SET eid = NULL WHERE qid = :questionId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':questionId', $questionId, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'success' => true, // ✅ Added for compatibility
            'message' => 'Question removed successfully',
            'qid' => $questionId
        ]);
    } else {
        echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Failed to remove question']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$pdo = null;
?>
