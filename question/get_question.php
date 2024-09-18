<?php
header('Content-Type: application/json');
include_once '../config.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");


$qid = $_GET['qid'];
$query = "SELECT * FROM question WHERE qid = :qid";
$stmt = $pdo->prepare($query);
$stmt->execute(['qid' => $qid]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($question);
?>