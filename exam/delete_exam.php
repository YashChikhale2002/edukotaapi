<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include the configuration file
include_once '../config.php';

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'DELETE' || $method === 'POST') {
    // Get the exam ID from the request
    $input = json_decode(file_get_contents('php://input'), true);
    $eid = isset($input['eid']) ? intval($input['eid']) : 0;

    // Validate the exam ID
    if ($eid <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid exam ID']);
        exit;
    }

    try {
        // Start transaction
        $pdo->beginTransaction();

        // First, remove exam references from questions (set eid to NULL for questions assigned to this exam)
        $stmt = $pdo->prepare("UPDATE question SET eid = NULL WHERE eid = :eid");
        $stmt->execute(['eid' => $eid]);

        // Delete the exam
        $stmt = $pdo->prepare("DELETE FROM onlineexam WHERE eid = :eid");
        $stmt->execute(['eid' => $eid]);

        // Commit transaction
        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Exam deleted successfully']);
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to delete exam: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$pdo = null;
?>
