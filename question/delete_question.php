<?php
header('Content-Type: application/json');
include_once '../config.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$qid = $_GET['qid'];

try {
    $pdo->beginTransaction();

    // Delete options related to the question
    $stmt = $pdo->prepare("DELETE FROM qoption WHERE qid = :qid");
    $stmt->execute(['qid' => $qid]);

    // Delete the question
    $stmt = $pdo->prepare("DELETE FROM question WHERE qid = :qid");
    $stmt->execute(['qid' => $qid]);

    $pdo->commit();

    echo json_encode(['message' => 'Question deleted successfully']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
}
?>