<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $exam_id = $_GET['exam_id'];

    $sql = "SELECT id, question_text, option_a, option_b, option_c, option_d FROM questions WHERE exam_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$exam_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($questions) {
        echo json_encode($questions);
    } else {
        echo json_encode(["message" => "No questions found for this exam"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}
?>
