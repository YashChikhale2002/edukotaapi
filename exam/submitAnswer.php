<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../config.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $exam_id = $input['exam_id'];
    $user_id = $input['user_id'];
    $answers = $input['answers'];

    try {
        $pdo->beginTransaction();

        // Insert answers into the user_answers table
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

        // Calculate total correct answers by comparing user selection with the correct option
        $correctStmt = $pdo->prepare("
            SELECT 
                COUNT(*) AS total_correct
            FROM 
                user_answers ua
            JOIN 
                qoption qo ON ua.qid = qo.qid AND ua.selected_option = qo.oid
            WHERE 
                ua.eid = :exam_id AND ua.uid = :user_id AND qo.is_correct = 1
        ");
        $correctStmt->execute([
            ':exam_id' => $exam_id,
            ':user_id' => $user_id
        ]);
        $total_correct = $correctStmt->fetchColumn();

        // Calculate total answered and total marked for review
        $total_questions = count($answers);
        $total_answered = count(array_filter($answers, fn($ans) => $ans['selected_option'] !== null));
        $total_marked_for_review = count(array_filter($answers, fn($ans) => $ans['marked_for_review']));

        // Calculate the score (assuming each correct answer is worth 4 marks)
        $score = ($total_correct / $total_questions) * 120; // Adjust mark calculation as needed

        // Insert or update the user report with the correct count and score
        $stmt = $pdo->prepare("
            INSERT INTO user_reports (uid, eid, total_questions, total_answered, total_correct, total_marked_for_review, score, report_generated_time) 
            VALUES (:uid, :eid, :total_questions, :total_answered, :total_correct, :total_marked_for_review, :score, NOW())
            ON DUPLICATE KEY UPDATE
                total_questions = :total_questions,
                total_answered = :total_answered,
                total_correct = :total_correct,
                total_marked_for_review = :total_marked_for_review,
                score = :score,
                report_generated_time = NOW()
        ");
        $stmt->execute([
            ':uid' => $user_id,
            ':eid' => $exam_id,
            ':total_questions' => $total_questions,
            ':total_answered' => $total_answered,
            ':total_correct' => $total_correct,
            ':total_marked_for_review' => $total_marked_for_review,
            ':score' => $score,
        ]);

        // Commit the transaction
        $pdo->commit();
        echo json_encode(["success" => true, "total_correct" => $total_correct, "score" => $score]);

    } catch (PDOException $e) {
        // Roll back the transaction on error
        $pdo->rollBack();
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}
?>
