<?php
include 'config.php';
header('Access-Control-Allow-Origin: *'); //add this CORS header to enable any domain to send HTTP requests to these endpoints:

session_start();
$user_id = $_SESSION['user_id'];
$exam_id = $_POST['exam_id'];
$answers = $_POST['answers'];

$total_questions = count($answers);
$correct_answers = 0;
$wrong_answers = 0;
$skipped_questions = 0;

foreach ($answers as $question_id => $selected_option) {
    $sql = "SELECT correct_option FROM questions WHERE id = :question_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['question_id' => $question_id]);
    $correct_option = $stmt->fetchColumn();

    if ($selected_option === 'skipped') {
        $skipped_questions++;
    } elseif ($selected_option === $correct_option) {
        $correct_answers++;
    } else {
        $wrong_answers++;
    }

    $sql = "REPLACE INTO answers (user_id, question_id, selected_option) VALUES (:user_id, :question_id, :selected_option)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user_id' => $user_id,
        'question_id' => $question_id,
        'selected_option' => $selected_option
    ]);
}

$sql = "REPLACE INTO exam_reports (user_id, exam_id, total_questions, correct_answers, wrong_answers, skipped_questions) 
        VALUES (:user_id, :exam_id, :total_questions, :correct_answers, :wrong_answers, :skipped_questions)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'user_id' => $user_id,
    'exam_id' => $exam_id,
    'total_questions' => $total_questions,
    'correct_answers' => $correct_answers,
    'wrong_answers' => $wrong_answers,
    'skipped_questions' => $skipped_questions
]);

echo json_encode(['status' => 'success']);
?>
