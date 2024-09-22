<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../config.php'; // Include your database configuration
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get raw POST data
    $input = json_decode(file_get_contents("php://input"), true);
    error_log(print_r($input, true)); // Log the input for debugging

    // Validate input
    if (isset($input['eid'], $input['uid'], $input['answers'])) {
        $exam_id = $input['eid'];
        $user_id = $input['uid'];
        $answers = $input['answers'];

        // Prepare to insert user answers
        try {
            $pdo->beginTransaction();

            // Loop through each answer and insert it into user_answers
            foreach ($answers as $answer) {
                $stmt = $pdo->prepare("
                    INSERT INTO user_answers (uid, eid, qid, selected_option, marked_for_review, submission_time) 
                    VALUES (:uid, :eid, :qid, :selected_option, :marked_for_review, NOW())
                ");
                $stmt->execute([
                    ':uid' => $user_id,
                    ':eid' => $exam_id,
                    ':qid' => $answer['question_id'],
                    ':selected_option' => $answer['selected_option'],
                    ':marked_for_review' => $answer['marked_for_review'] ? 1 : 0,
                ]);
            }

            // Calculate scores (implement your logic here)
            $total_questions = count($answers);
            $total_answered = count(array_filter($answers, fn($ans) => $ans['selected_option'] !== null));
            $total_correct = 0; // Placeholder, implement your scoring logic
            $total_marked_for_review = count(array_filter($answers, fn($ans) => $ans['marked_for_review']));

            // Insert into user_reports
            $stmt = $pdo->prepare("
                INSERT INTO user_reports (uid, eid, total_questions, total_answered, total_correct, total_marked_for_review, score, report_generated_time) 
                VALUES (:uid, :eid, :total_questions, :total_answered, :total_correct, :total_marked_for_review, :score, NOW())
            ");
            $score = ($total_questions > 0) ? ($total_correct / $total_questions) * 100 : 0; // Calculate score
            $stmt->execute([
                ':uid' => $user_id,
                ':eid' => $exam_id,
                ':total_questions' => $total_questions,
                ':total_answered' => $total_answered,
                ':total_correct' => $total_correct,
                ':total_marked_for_review' => $total_marked_for_review,
                ':score' => $score,
            ]);

            $pdo->commit();
            echo json_encode(["success" => true]);

        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Missing required fields"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>
