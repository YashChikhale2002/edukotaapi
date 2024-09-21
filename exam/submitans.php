<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include the configuration file
include_once '../config.php';

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!isset($data['exam_id']) || !isset($data['user_id']) || !isset($data['answers']) || !is_array($data['answers'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$exam_id = intval($data['exam_id']);
$user_id = intval($data['user_id']);
$answers = $data['answers'];

try {
    // Start a transaction
    $pdo->beginTransaction();

    // Iterate over the answers and insert each one into the database
    foreach ($answers as $answer) {
        $question_id = intval($answer['question_id']);
        $selected_option = intval($answer['selected_option']);
        $marked_for_review = $answer['marked_for_review'] ? 1 : 0;

        // Fetch the correct option and mark for the question
        $stmt = $pdo->prepare("SELECT ans, mark FROM question WHERE qid = :question_id AND eid = :exam_id");
        $stmt->execute(['question_id' => $question_id, 'exam_id' => $exam_id]);
        $question = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$question) {
            throw new Exception("Question not found for qid: $question_id");
        }

        $correct_option = intval($question['ans']);
        $total_marks = floatval($question['mark']);
        $is_correct = $selected_option === $correct_option ? 1 : 0;
        $marks_awarded = $is_correct ? $total_marks : 0;

        // Insert the answer into the user_exam_submissions table
        $stmt = $pdo->prepare("
            INSERT INTO user_exam_submissions 
            (username, eid, qid, oid, correct_oid, statusold, marks_obtained, status, is_correct, marks_awarded, created_at) 
            VALUES 
            (:username, :exam_id, :question_id, :selected_option, :correct_option, 'old_status', :marks_obtained, :status, :is_correct, :marks_awarded, NOW())
        ");
        $stmt->execute([
            'username' => $user_id, // Assuming user_id is used as the username
            'exam_id' => $exam_id,
            'question_id' => $question_id,
            'selected_option' => $selected_option,
            'correct_option' => $correct_option,
            'marks_obtained' => $total_marks,
            'status' => $marked_for_review ? 'Marked for Review' : 'Answered',
            'is_correct' => $is_correct,
            'marks_awarded' => $marks_awarded
        ]);
    }

    // Commit the transaction
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Answers submitted successfully']);
} catch (Exception $e) {
    // Rollback the transaction if any error occurs
    $pdo->rollBack();
    echo json_encode(['error' => 'Failed to submit answers: ' . $e->getMessage()]);
}
?>
