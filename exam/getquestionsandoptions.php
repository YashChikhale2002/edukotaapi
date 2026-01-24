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
    error_log("✅ Fetching questions for exam ID: $eid");
    
    // Fetch questions
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
    
    // Fetch options for each question
    foreach ($questions as &$question) {
        // ✅ CORRECT: Use 'is_correct' WITH underscore
        $opQuery = "SELECT oid, name, is_correct FROM qoption WHERE qid = :qid ORDER BY oid ASC";
        $opStmt = $pdo->prepare($opQuery);
        $opStmt->execute(['qid' => $question['qid']]);
        $options = $opStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ensure is_correct is integer
        foreach ($options as &$option) {
            $option['is_correct'] = (int)$option['is_correct'];
        }
        
        $question['options'] = $options;
        error_log("Question {$question['qid']}: " . count($options) . " options");
    }
    
    error_log("✅ Returning " . count($questions) . " questions with options");
    echo json_encode($questions);
    
} catch (PDOException $e) {
    error_log("❌ Error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
