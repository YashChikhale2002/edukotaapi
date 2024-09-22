<?php 
header('Content-Type: application/json');
include_once '../config.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$userId = $_GET['userId'];

try {
    $stmt = $pdo->prepare("SELECT uid, username, name FROM users WHERE username = :userId");
    $stmt->execute(['userId' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($user);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
