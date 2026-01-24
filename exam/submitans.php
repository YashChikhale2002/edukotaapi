<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['uid']) && isset($data['eid']) && isset($data['answers'])) {
        $user_id = (int)$data['uid'];
        $exam_id = (int)$data['eid'];
        $answers = $data['answers'];
        
        error_log("📝 Submitting exam: uid=$user_id, eid=$exam_id, answers=" . count($answers));
        
        try {
            $pdo->beginTransaction();
            
            // ✅ Use 'user_answers' WITH underscore (check your DB structure)
            $deleteStmt = $pdo->prepare("DELETE FROM user_answers WHERE uid = :uid AND eid = :eid");
            $deleteStmt->execute([':uid' => $user_id, ':eid' => $exam_id]);
            error_log("🗑️ Deleted old answers");
            
            $total_correct = 0;
            $total_answered = 0;
            $total_marked = 0;
            
            // ✅ Use WITH underscores
            $insertStmt = $pdo->prepare("
                INSERT INTO user_answers (uid, eid, qid, selected_option, marked_for_review, is_correct, submission_time)
                VALUES (:uid, :eid, :qid, :selected_option, :marked_for_review, :is_correct, NOW())
            ");
            
            foreach ($answers as $answer) {
                $question_id = (int)$answer['question_id'];
                $selected_option_id = isset($answer['selected_option']) ? (int)$answer['selected_option'] : 0;
                $marked_for_review = isset($answer['marked_for_review']) && $answer['marked_for_review'] ? 1 : 0;
                
                // Get correct answer
                $checkStmt = $pdo->prepare("SELECT ans FROM question WHERE qid = :qid");
                $checkStmt->execute([':qid' => $question_id]);
                $question = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
                $is_correct = 0;
                if ($selected_option_id > 0) {
                    $total_answered++;
                    if ($marked_for_review) {
                        $total_marked++;
                    }
                    
                    // Compare option IDs
                    if ($question && (int)$question['ans'] === $selected_option_id) {
                        $is_correct = 1;
                        $total_correct++;
                        error_log("✅ Q$question_id: CORRECT");
                    } else {
                        error_log("❌ Q$question_id: WRONG");
                    }
                }
                
                // Insert answer
                $insertStmt->execute([
                    ':uid' => $user_id,
                    ':eid' => $exam_id,
                    ':qid' => $question_id,
                    ':selected_option' => $selected_option_id,
                    ':marked_for_review' => $marked_for_review,
                    ':is_correct' => $is_correct
                ]);
            }
            
            // Count total questions
            $countStmt = $pdo->prepare("
                SELECT COUNT(DISTINCT q.qid) as total
                FROM question q
                WHERE FIND_IN_SET(:eid, q.eid) > 0
            ");
            $countStmt->execute([':eid' => $exam_id]);
            $totalQuestions = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Calculate score
            $score = $totalQuestions > 0 ? round(($total_correct / $totalQuestions) * 100, 2) : 0;
            
            // Delete existing report
            $deleteReportStmt = $pdo->prepare("DELETE FROM user_reports WHERE uid = :uid AND eid = :eid");
            $deleteReportStmt->execute([':uid' => $user_id, ':eid' => $exam_id]);
            
            // Insert new report
            $reportStmt = $pdo->prepare("
                INSERT INTO user_reports (uid, eid, total_questions, total_answered, total_correct, total_marked_for_review, score, report_generated_time)
                VALUES (:uid, :eid, :total_questions, :total_answered, :total_correct, :total_marked_for_review, :score, NOW())
            ");
            
            $reportStmt->execute([
                ':uid' => $user_id,
                ':eid' => $exam_id,
                ':total_questions' => $totalQuestions,
                ':total_answered' => $total_answered,
                ':total_correct' => $total_correct,
                ':total_marked_for_review' => $total_marked,
                ':score' => $score
            ]);
            
            // Update user exam status
            $updateUserStmt = $pdo->prepare("UPDATE users SET estatus = 1, examdate = CURDATE() WHERE uid = :uid");
            $updateUserStmt->execute([':uid' => $user_id]);
            
            $pdo->commit();
            
            error_log("✅ Exam submitted successfully!");
            echo json_encode([
                'success' => true,
                'message' => 'Exam submitted successfully!',
                'total_questions' => $totalQuestions,
                'total_answered' => $total_answered,
                'total_correct' => $total_correct,
                'score' => $score
            ]);
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("❌ Database error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Missing required fields'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. Only POST is allowed.'
    ]);
}
?>
