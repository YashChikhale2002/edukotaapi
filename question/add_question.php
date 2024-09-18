<?php

include_once '../config.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Get the posted data
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['question'], $data['group'], $data['marks'], $data['toption'], $data['options'])) {
    echo json_encode(["message" => "Error: Missing required fields"]);
    exit();
}

try {
    // Step 1: Insert question into 'question' table
    $question = $data['question'];
    $group = $data['group']; // Assuming this maps to 'gid'
    $marks = $data['marks']; // Maps to 'mark'
    $toption = $data['toption']; // Maps to 'toption'

    // Insert query for question table
    $insert_question_query = "INSERT INTO question (question, gid, toption, mark) VALUES (:question, :gid, :toption, :mark)";
    $stmt = $pdo->prepare($insert_question_query);
    $stmt->execute([
        ':question' => $question,
        ':gid' => $group,
        ':toption' => $toption,
        ':mark' => $marks
    ]);

    // Step 2: Get the last inserted question ID (qid)
    $qid = $pdo->lastInsertId();

    // Step 3: Insert options into 'qoption' table
    $options = $data['options'];
    $correctOid = null; // Variable to store the correct option's oid

    foreach ($options as $option) {
        $name = $option['name'];
        $isCorrect = $option['isCorrect'] ? 1 : 0; // true or false

        // Insert option into 'qoption' table
        $insert_option_query = "INSERT INTO qoption (qid, name, is_correct) VALUES (:qid, :name, :is_correct)";
        $stmt = $pdo->prepare($insert_option_query);
        $stmt->execute([
            ':qid' => $qid,
            ':name' => $name,
            ':is_correct' => $isCorrect
        ]);

        // Step 4: If this option is correct, store its oid
        if ($isCorrect === 1) {
            $correctOid = $pdo->lastInsertId(); // Get the oid of the correct option
        }
    }

    // Step 5: Update the question table with the correct answer (oid)
    if ($correctOid !== null) {
        $update_question_query = "UPDATE question SET ans = :ans WHERE qid = :qid";
        $stmt = $pdo->prepare($update_question_query);
        $stmt->execute([
            ':ans' => $correctOid,
            ':qid' => $qid
        ]);
    }

    // Final response
    echo json_encode(["message" => "Question and options added successfully", "qid" => $qid]);

} catch (PDOException $e) {
    echo json_encode(["message" => "Database error: " . $e->getMessage()]);
}
?>
