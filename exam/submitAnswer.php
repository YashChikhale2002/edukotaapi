<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include_once '../config.php'; // Include your database configuration

$data = json_decode(file_get_contents("php://input"));

$user_id = $data->user_id;
$exam_id = $data->exam_id;
$question_id = $data->question_id;
$selected_option = $data->selected_option;
$marked_for_review = $data->marked_for_review ? 1 : 0;

try {
    $stmt = $pdo->prepare("
        INSERT INTO user_answers (uid, eid, qid, selected_option, marked_for_review)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        selected_option = VALUES(selected_option),
        marked_for_review = VALUES(marked_for_review)
    ");
    $stmt->execute([$user_id, $exam_id, $question_id, $selected_option, $marked_for_review]);

    echo json_encode(['message' => 'Answer saved successfully']);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
