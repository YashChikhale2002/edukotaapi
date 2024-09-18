<?php
header('Content-Type: application/json');
include_once '../config.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$pid = $_GET['qid'];

try {
    $stmt = $pdo->prepare("SELECT * FROM question WHERE qid = :qid");
    $stmt->execute(['qid' => $pid]);
    $question = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch options for the question
    $stmtOptions = $pdo->prepare("SELECT * FROM qoption WHERE qid = :qid");
    $stmtOptions->execute(['qid' => $pid]);
    $options = $stmtOptions->fetchAll(PDO::FETCH_ASSOC);

    $question['options'] = $options;
    echo json_encode($question);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
