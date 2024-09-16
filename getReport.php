<?php
require 'config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

$exam_id = $_GET['exam_id'];
$user_id = $_GET['user_id'];

$sql = "SELECT COUNT(*) as total_questions,
               SUM(selected_option = correct_option) as correct_answers,
               SUM(selected_option IS NOT NULL AND selected_option != correct_option) as wrong_answers,
               SUM(selected_option IS NULL) as skipped_questions
        FROM questions q
        LEFT JOIN answers a ON q.id = a.question_id
        WHERE q.exam_id = ? AND a.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$exam_id, $user_id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($report);
?>
