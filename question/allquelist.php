<?php
include_once '../config.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$query = "SELECT * FROM question";
$stmt = $pdo->query($query);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($questions);
?>
