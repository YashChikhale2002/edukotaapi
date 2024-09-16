<?php
require 'config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$data = json_decode(file_get_contents("php://input"));

$user_id = $data->user_id;
$question_id = $data->question_id;
$selected_option = $data->selected_option;

$sql = "INSERT INTO answers (user_id, question_id, selected_option) VALUES (?, ?, ?)";
$stmt = $pdo->prepare($sql);

if ($stmt->execute([$user_id, $question_id, $selected_option])) {
    echo json_encode(["message" => "Answer submitted successfully"]);
} else {
    echo json_encode(["message" => "Failed to submit answer"]);
}
?>
