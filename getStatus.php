<?php
require 'config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

$user_id = $_GET['user_id'];
$exam_id = $_GET['exam_id'];

$sql = "SELECT status, count FROM question_statuses WHERE user_id = ? AND exam_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $exam_id]);
$statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($statuses);
?>
