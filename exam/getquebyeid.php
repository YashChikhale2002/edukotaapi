<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config.php';

if (!$pdo) {
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

$eid = isset($_GET['eid']) ? $_GET['eid'] : null;

if (!$eid) {
    echo json_encode(['error' => 'Invalid exam ID']);
    exit;
}

try {
    $query = "SELECT q.qid, q.question, q.gid, q.eid, q.mark, g.Title as group_name 
              FROM question q 
              LEFT JOIN quegrp g ON q.gid = g.gid 
              WHERE q.eid = :eid 
              ORDER BY q.qid ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['eid' => $eid]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($questions);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Failed to fetch questions: ' . $e->getMessage()]);
}

$pdo = null;
?>
