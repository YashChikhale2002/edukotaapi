<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config.php';

if (!$pdo) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$eid = isset($_GET['eid']) ? intval($_GET['eid']) : null;

if (!$eid) {
    echo json_encode(['error' => 'Invalid exam ID']);
    exit;
}

try {
    // ✅ Check if question table has op1-op4 columns
    $checkOp1 = $pdo->query("SHOW COLUMNS FROM question LIKE 'op1'")->fetch();
    
    if ($checkOp1) {
        // ✅ SCHEMA 1: Question table has op1, op2, op3, op4 columns
        $query = "SELECT 
                    q.qid, 
                    q.question, 
                    q.op1,
                    q.op2,
                    q.op3,
                    q.op4,
                    q.answer,
                    q.gid, 
                    q.eid, 
                    q.mark,
                    COALESCE(g.Title, 'General') as group_name,
                    COALESCE(g.Title, 'General') as group_title
                  FROM question q
                  LEFT JOIN quegrp g ON q.gid = g.gid
                  WHERE q.eid = :eid
                  ORDER BY q.qid ASC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute(['eid' => $eid]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } else {
        // ✅ SCHEMA 2: Question table uses separate qoption table
        $query = "SELECT 
                    q.qid, 
                    q.question, 
                    q.gid, 
                    q.eid, 
                    q.mark,
                    q.ans,
                    COALESCE(g.Title, 'General') as group_name,
                    COALESCE(g.Title, 'General') as group_title
                  FROM question q
                  LEFT JOIN quegrp g ON q.gid = g.gid
                  WHERE q.eid = :eid
                  ORDER BY q.qid ASC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute(['eid' => $eid]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // ✅ Fetch options for each question from qoption table
        foreach ($questions as &$question) {
            $opQuery = "SELECT oid, name, is_correct FROM qoption WHERE qid = :qid ORDER BY oid ASC";
            $opStmt = $pdo->prepare($opQuery);
            $opStmt->execute(['qid' => $question['qid']]);
            $options = $opStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Map to op1-op4 format
            $question['op1'] = isset($options[0]) ? $options[0]['name'] : '';
            $question['op2'] = isset($options[1]) ? $options[1]['name'] : '';
            $question['op3'] = isset($options[2]) ? $options[2]['name'] : '';
            $question['op4'] = isset($options[3]) ? $options[3]['name'] : '';
            $question['answer'] = $question['ans'];
        }
    }
    
    error_log("✅ getquebyeid.php: Returning " . count($questions) . " questions for exam ID " . $eid);
    
    // ✅ Always return an array (even if empty)
    echo json_encode($questions);
    
} catch (PDOException $e) {
    error_log("❌ Database error in getquebyeid.php: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$pdo = null;
?>
