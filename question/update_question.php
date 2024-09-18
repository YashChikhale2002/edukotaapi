<?php
header('Content-Type: application/json');
include_once '../config.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$qid = $_GET['qid'];
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['question'], $data['group'], $data['marks'], $data['toption'], $data['options'])) {
    echo json_encode(['message' => 'Error: Missing required fields']);
    exit;
}

$question = $data['question'];
$group = $data['group'];
$marks = $data['marks'];
$toption = $data['toption'];
$options = $data['options'];

try {
    $pdo->beginTransaction();

    // Update question
    $stmt = $pdo->prepare("UPDATE question SET question = :question, gid = :group, mark = :marks, toption = :toption WHERE qid = :qid");
    $stmt->execute([
        'question' => $question,
        'group' => $group,
        'marks' => $marks,
        'toption' => $toption,
        'qid' => $qid
    ]);

    // Delete existing options
    $stmt = $pdo->prepare("DELETE FROM qoption WHERE qid = :qid");
    $stmt->execute(['qid' => $qid]);

    // Insert new options
    $correctOid = null;
    $stmt = $pdo->prepare("INSERT INTO qoption (qid, name, is_correct) VALUES (:qid, :name, :is_correct)");

    foreach ($options as $option) {
        $stmt->execute([
            'qid' => $qid,
            'name' => $option['name'],
            'is_correct' => $option['isCorrect']
        ]);

        if ($option['isCorrect']) {
            $correctOid = $pdo->lastInsertId();
        }
    }

    // Update question with the correct option id
    $stmt = $pdo->prepare("UPDATE question SET ans = :ans WHERE qid = :qid");
    $stmt->execute(['ans' => $correctOid, 'qid' => $qid]);

    $pdo->commit();

    echo json_encode(['message' => 'Question updated successfully']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
}
?>