<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include '../config.php';

$uid = isset($_GET['uid']) ? intval($_GET['uid']) : 88;

try {
    $query = "
        SELECT 
            ua.uid,
            ua.eid,
            ua.qid,
            ua.selected_option,
            ua.marked_for_review,
            ua.submission_time,
            q.question,
            q.ans as correct_answer
        FROM user_answers ua
        LEFT JOIN question q ON ua.qid = q.qid
        WHERE ua.uid = :uid
        ORDER BY ua.qid
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['uid' => $uid]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stats = [
        'total' => count($results),
        'answered' => 0,
        'not_answered' => 0,
        'correct' => 0,
        'incorrect' => 0
    ];
    
    foreach ($results as $result) {
        $selectedOption = trim($result['selected_option']);
        
        if (!empty($selectedOption) && $selectedOption != '0') {
            $stats['answered']++;
            
            // Check if correct
            if ($result['correct_answer'] == $selectedOption) {
                $stats['correct']++;
            } else {
                $stats['incorrect']++;
            }
        } else {
            $stats['not_answered']++;
        }
    }
    
    echo json_encode([
        'uid' => $uid,
        'statistics' => $stats,
        'answers' => $results,
        'note' => 'selected_option stores OPTION TEXT (A/B/C/D), not option ID'
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
