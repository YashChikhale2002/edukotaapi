<?php
include 'config.php';
header('Access-Control-Allow-Origin: *'); //add this CORS header to enable any domain to send HTTP requests to these endpoints:

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $exam_id = $_POST['exam_id'];

    $stmt = $conn->prepare("SELECT * FROM answers WHERE user_id = ? AND question_id IN (SELECT id FROM questions WHERE exam_id = ?)");
    $stmt->execute([$user_id, $exam_id]);
    $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $correct_answers = 0;
    $wrong_answers = 0;
    $skipped_questions = 0;
    $total_questions = count($answers);

    foreach ($answers as $answer) {
        $stmt = $conn->prepare("SELECT correct_option FROM questions WHERE id = ?");
        $stmt->execute([$answer['question_id']]);
        $correct_option = $stmt->fetchColumn();

        if ($answer['selected_option'] == null) {
            $skipped_questions++;
        } elseif ($answer['selected_option'] == $correct_option) {
            $correct_answers++;
        } else {
            $wrong_answers++;
        }
    }

    $stmt = $conn->prepare("INSERT INTO exam_reports (user_id, exam_id, total_questions, correct_answers, wrong_answers, skipped_questions) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $exam_id, $total_questions, $correct_answers, $wrong_answers, $skipped_questions]);

    echo json_encode([
        'total_questions' => $total_questions,
        'correct_answers' => $correct_answers,
        'wrong_answers' => $wrong_answers,
        'skipped_questions' => $skipped_questions
    ]);
}
?>
