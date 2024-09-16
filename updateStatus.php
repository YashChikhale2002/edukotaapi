<?php
require 'config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$data = json_decode(file_get_contents("php://input"));

$user_id = $data->user_id;
$exam_id = $data->exam_id;
$statuses = $data->statuses; // Example: { "Not Visited": 5, "Marked for Review": 3, "Not Answered": 7, "Answered": 10 }

foreach ($statuses as $status => $count) {
    $sql = "INSERT INTO question_statuses (user_id, exam_id, status, count) VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE count = VALUES(count)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $exam_id, $status, $count]);
}

echo json_encode(["message" => "Status updated successfully"]);
?>
