<?php
// ✅ CORS Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// ✅ Prevent HTML error output
@ini_set('display_errors', '0');
@error_reporting(0);

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Check if config exists
if (!file_exists('../config.php')) {
    echo json_encode(['success' => false, 'error' => 'Config file not found']);
    exit;
}

require_once '../config.php';

// ✅ Check database connection
if (!isset($pdo)) {
    echo json_encode(['success' => false, 'error' => 'Database not connected']);
    exit;
}

// ✅ Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Only POST allowed']);
    exit;
}

try {
    // ✅ Get raw input
    $input = file_get_contents('php://input');
    
    if (empty($input)) {
        throw new Exception('No data received');
    }
    
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }
    
    // ✅ Log received data
    error_log("📥 Received: " . print_r($data, true));
    
    // ✅ Validate
    if (!isset($data['eid']) || !isset($data['uid']) || !isset($data['answers'])) {
        throw new Exception('Missing eid, uid, or answers');
    }
    
    $eid = (int)$data['eid'];
    $uid = (int)$data['uid'];
    $answers = $data['answers'];
    
    if ($eid <= 0 || $uid <= 0) {
        throw new Exception('Invalid eid or uid');
    }
    
    if (!is_array($answers)) {
        throw new Exception('Answers must be array');
    }
    
    // ✅ Start transaction
    $pdo->beginTransaction();
    
    // ✅ Clear old answers (USING CORRECT TABLE NAME: user_answers)
    $stmt = $pdo->prepare("DELETE FROM user_answers WHERE uid = ? AND eid = ?");
    $stmt->execute([$uid, $eid]);
    
    error_log("🗑️ Deleted old answers");
    
    // ✅ Prepare insert (USING CORRECT TABLE NAME: user_answers)
    $insertStmt = $pdo->prepare("
        INSERT INTO user_answers (uid, eid, qid, selected_option, marked_for_review, is_correct) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $correct = 0;
    $answered = 0;
    $marked = 0;
    
    // ✅ Process answers
    foreach ($answers as $ans) {
        $qid = (int)$ans['question_id'];
        $option = isset($ans['selected_option']) ? trim($ans['selected_option']) : null;
        $review = !empty($ans['marked_for_review']) ? 1 : 0;
        
        if ($qid <= 0 || empty($option)) {
            continue;
        }
        
        $answered++;
        if ($review) $marked++;
        
        // ✅ Check correct answer
        $checkStmt = $pdo->prepare("SELECT ans FROM question WHERE qid = ?");
        $checkStmt->execute([$qid]);
        $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        $isCorrect = 0;
        if ($row && $row['ans'] === $option) {
            $isCorrect = 1;
            $correct++;
        }
        
        // ✅ Insert answer
        $insertStmt->execute([$uid, $eid, $qid, $option, $review, $isCorrect]);
    }
    
    // ✅ Count total questions
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM question WHERE eid = ?");
    $countStmt->execute([$eid]);
    $total = $countStmt->fetchColumn();
    
    // ✅ Calculate score
    $score = $total > 0 ? round(($correct / $total) * 100, 2) : 0;
    
    error_log("📊 Score: {$correct}/{$total} = {$score}%");
    
    // ✅ Save report (USING CORRECT TABLE NAME: user_reports)
    $reportStmt = $pdo->prepare("
        INSERT INTO user_reports (uid, eid, total_questions, total_answered, total_correct, total_marked_for_review, score) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $reportStmt->execute([$uid, $eid, $total, $answered, $correct, $marked, $score]);
    
    // ✅ Commit
    $pdo->commit();
    
    error_log("✅ Exam submitted successfully!");
    
    // ✅ Success response
    echo json_encode([
        'success' => true,
        'message' => 'Exam submitted successfully!',
        'total_questions' => $total,
        'total_answered' => $answered,
        'total_correct' => $correct,
        'score' => $score
    ]);
    
} catch (Exception $e) {
    // ✅ Rollback
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("❌ Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
