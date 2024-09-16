<?php
require_once 'config.php';
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_id = $_POST['exam_id'];
    
    $exam_stmt = $conn->prepare('SELECT id, title, subject, exam_date FROM exams WHERE id = ?');
    $exam_stmt->bind_param('i', $exam_id);
    $exam_stmt->execute();
    $exam_stmt->bind_result($id, $title, $subject, $exam_date);
    $exam_stmt->fetch();
    
    $questions = [];
    
    $question_stmt = $conn->prepare('SELECT id, question_text FROM questions WHERE exam_id = ?');
    $question_stmt->bind_param('i', $exam_id);
    $question_stmt->execute();
    $question_stmt->bind_result($question_id, $question_text);
    
    while ($question_stmt->fetch()) {
        $questions[] = ['id' => $question_id, 'question_text' => $question_text];
    }
    
    echo json_encode(['exam' => ['id' => $id, 'title' => $title, 'subject' => $subject, 'exam_date' => $exam_date], 'questions' => $questions]);
    
    $exam_stmt->close();
    $question_stmt->close();
    $conn->close();
}
?>
