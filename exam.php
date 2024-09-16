<?php
include 'config.php';
header('Access-Control-Allow-Origin: *'); //add this CORS header to enable any domain to send HTTP requests to these endpoints:

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    if ($action == 'getQuestions') {
        $exam_id = $_POST['exam_id'];
        $stmt = $conn->prepare("SELECT * FROM questions WHERE exam_id = ?");
        $stmt->execute([$exam_id]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($questions);
    }

    if ($action == 'submitAnswer') {
        $user_id = $_SESSION['user_id'];
        $question_id = $_POST['question_id'];
        $selected_option = $_POST['selected_option'];

        $stmt = $conn->prepare("INSERT INTO answers (user_id, question_id, selected_option) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $question_id, $selected_option]);
        echo json_encode(['status' => 'success', 'message' => 'Answer submitted']);
    }
}
?>
