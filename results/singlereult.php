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

$uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;

if (!$uid) {
    echo json_encode(['error' => 'User ID not provided']);
    exit;
}

try {
    error_log("✅ Fetching detailed results for UID: $uid");
    
    // ✅ Check if user exists - FIXED: using parents_number instead of contact_number
    $userCheckQuery = "SELECT uid, name, parents_number FROM users WHERE uid = :uid";
    $userStmt = $pdo->prepare($userCheckQuery);
    $userStmt->execute(['uid' => $uid]);
    $userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userInfo) {
        echo json_encode(['error' => 'User not found with ID: ' . $uid]);
        exit;
    }
    
    error_log("✅ User found: " . $userInfo['name']);
    
    // ✅ Check if user has submitted answers
    $countQuery = "SELECT COUNT(*) as total FROM user_answers WHERE uid = :uid";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute(['uid' => $uid]);
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($countResult['total'] == 0) {
        echo json_encode([
            'error' => 'No exam submissions found for user: ' . $userInfo['name'],
            'user_exists' => true,
            'student_name' => $userInfo['name'],
            'uid' => $uid,
            'message' => 'This user has not attempted any exam yet.'
        ]);
        exit;
    }
    
    error_log("✅ Found {$countResult['total']} answers");
    
    // ✅ Fetch detailed results - FIXED: using parents_number
    $sql = "
        SELECT 
            u.name AS student_name,
            u.parents_number,
            q.question,
            q.qid,
            q.ans as correct_answer_id,
            ua.selected_option as selected_option_id,
            qo_selected.name as selected_option_name,
            qo_correct.name as correct_answer_name,
            ua.is_correct,
            COALESCE(g.title, 'General') AS question_group,
            q.mark,
            ua.marked_for_review,
            ua.submission_time
        FROM user_answers ua
        INNER JOIN users u ON ua.uid = u.uid
        INNER JOIN question q ON ua.qid = q.qid
        LEFT JOIN qoption qo_selected ON ua.selected_option = qo_selected.oid
        LEFT JOIN qoption qo_correct ON q.ans = qo_correct.oid
        LEFT JOIN quegrp g ON q.gid = g.gid
        WHERE ua.uid = :uid
        ORDER BY g.gid, q.qid
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $uid]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($results && count($results) > 0) {
        error_log("✅ Found " . count($results) . " detailed results");
        
        $summary = [];
        $totalCorrect = 0;
        $totalIncorrect = 0;
        $totalNotAnswered = 0;
        
        foreach ($results as &$result) {
            $groupTitle = $result['question_group'];
            
            if (!isset($summary[$groupTitle])) {
                $summary[$groupTitle] = [
                    'correct' => 0,
                    'incorrect' => 0,
                    'not_answered' => 0,
                    'total' => 0
                ];
            }
            
            $summary[$groupTitle]['total']++;
            
            $selectedOptionId = intval($result['selected_option_id']);
            $isCorrect = intval($result['is_correct']);
            
            if ($selectedOptionId == 0) {
                // Not answered
                $summary[$groupTitle]['not_answered']++;
                $totalNotAnswered++;
                $result['selected_option'] = 'Not Answered';
            } else {
                // Answered
                $result['selected_option'] = $result['selected_option_name'];
                
                if ($isCorrect == 1) {
                    // Correct
                    $summary[$groupTitle]['correct']++;
                    $totalCorrect++;
                } else {
                    // Wrong
                    $summary[$groupTitle]['incorrect']++;
                    $totalIncorrect++;
                }
            }
            
            // Set correct answer display
            $result['correct_answer'] = $result['correct_answer_name'] ?: 'N/A';
        }
        
        $response = [
            'success' => true,
            'results' => $results,
            'summary' => $summary,
            'student_name' => $results[0]['student_name'],
            'parents_number' => $results[0]['parents_number'],  // ✅ FIXED
            'total_questions' => count($results),
            'total_correct' => $totalCorrect,
            'total_incorrect' => $totalIncorrect,
            'total_not_answered' => $totalNotAnswered
        ];
        
        error_log("✅ Sending response with " . count($results) . " results");
        echo json_encode($response);
    } else {
        error_log("❌ No detailed results found");
        echo json_encode([
            'error' => 'No detailed results found',
            'user_name' => $userInfo['name']
        ]);
    }
    
} catch (PDOException $e) {
    error_log("❌ Database error: " . $e->getMessage());
    echo json_encode([
        'error' => 'Database query failed',
        'message' => $e->getMessage()
    ]);
}
?>
