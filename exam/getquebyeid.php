<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include '../config.php';

if (!isset($pdo)) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$eid = isset($_GET['eid']) ? intval($_GET['eid']) : 0;

if ($eid <= 0) {
    echo json_encode(['error' => 'Invalid exam ID']);
    exit;
}

try {
    error_log("✅ getquebyeid.php: Fetching questions for exam ID: $eid");
    
    // ✅ Use FIND_IN_SET for comma-separated exam IDs
    $query = "
        SELECT 
            q.qid,
            q.question,
            q.gid,
            q.mark,
            q.ans as correct_answer,
            COALESCE(g.title, 'General') as group_title
        FROM question q
        LEFT JOIN quegrp g ON q.gid = g.gid
        WHERE FIND_IN_SET(:eid, q.eid) > 0
        ORDER BY g.gid, q.qid ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['eid' => $eid]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("✅ Found " . count($questions) . " questions");
    
    if (count($questions) === 0) {
        error_log("⚠️ No questions found for exam ID: $eid");
        echo json_encode([]);
        exit;
    }
    
    // ✅ Fetch options for each question from qoption table
    foreach ($questions as &$question) {
        $opQuery = "SELECT oid, name, is_correct FROM qoption WHERE qid = :qid ORDER BY oid ASC";
        $opStmt = $pdo->prepare($opQuery);
        $opStmt->execute(['qid' => $question['qid']]);
        $options = $opStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // ✅ Convert data types for JavaScript
        foreach ($options as &$option) {
            $option['oid'] = (int)$option['oid'];
            $option['is_correct'] = (int)$option['is_correct'];
        }
        
        // ✅ CRITICAL: Return options as array, NOT as op1-op4
        $question['options'] = $options;
        
        if (count($options) === 0) {
            error_log("❌ NO OPTIONS for question {$question['qid']}!");
        } else {
            error_log("✅ Question {$question['qid']}: " . count($options) . " options");
        }
    }
    
    error_log("✅ Returning " . count($questions) . " questions with options array format");
    echo json_encode($questions);
    
} catch (PDOException $e) {
    error_log("❌ Database error in getquebyeid.php: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
