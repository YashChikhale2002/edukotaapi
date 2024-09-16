<?php
include_once '../config.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
$query = "SELECT * FROM quegrp";
$stmt = $pdo->prepare($query);
$stmt->execute();

$groups = $stmt->fetchAll();

echo json_encode($groups);
?>
