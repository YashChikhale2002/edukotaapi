<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include the configuration file
include_once '../config.php'; // Ensure this file sets up the $pdo variable

$exam_id = $_GET['exam_id'];
$user_id = $_GET['user_id'];

if (!$exam_id || !$user_id) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit;
}

try {
    // Fetch user answers and questions
    $stmt = $pdo->prepare("
        SELECT q.question_id, q.question_text, q.correct_option, a.selected_option, a.marked_for_review 
        FROM answers a 
        JOIN questions q ON a.question_id = q.question_id 
        WHERE a.exam_id = ? AND a.user_id = ?
    ");
    $stmt->execute([$exam_id, $user_id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $report = [
        "questions" => [],
        "correct" => 0,
        "incorrect" => 0,
        "markedForReview" => 0,
    ];

    foreach ($result as $row) {
        $isCorrect = $row['selected_option'] == $row['correct_option'];
        $report["questions"][] = [
            "question_id" => $row["question_id"],
            "question_text" => $row["question_text"],
            "correct_option" => $row["correct_option"],
            "selected_option" => $row["selected_option"],
            "marked_for_review" => $row["marked_for_review"],
            "is_correct" => $isCorrect
        ];
        if ($isCorrect) {
            $report["correct"]++;
        } else {
            $report["incorrect"]++;
        }
        if ($row["marked_for_review"]) {
            $report["markedForReview"]++;
        }
    }

    echo json_encode(["success" => true, "data" => $report]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
