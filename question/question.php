<?php
include 'config.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
// Create a question
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    $question = $data->question;
    $gid = $data->gid;
    $eid = $data->eid;
    $toption = $data->toption;
    $mark = $data->mark;
    $upload = $data->upload;

    $stmt = $pdo->prepare("INSERT INTO question (question, gid, eid, toption, mark, upload) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$question, $gid, $eid, $toption, $mark, $upload]);
    $qid = $pdo->lastInsertId();
    
    // Insert options and correct answer
    foreach ($data->options as $option) {
        $stmt = $pdo->prepare("INSERT INTO qoption (qid, name, img) VALUES (?, ?, ?)");
        $stmt->execute([$qid, $option->name, $option->img]);
        $oid = $pdo->lastInsertId();
        
        if ($option->is_correct) {
            $stmt = $pdo->prepare("INSERT INTO queans (qid, oid) VALUES (?, ?)");
            $stmt->execute([$qid, $oid]);
        }
    }
    
    echo json_encode(['status' => 'success']);
}

// Update a question
if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $data = json_decode(file_get_contents("php://input"));
    
    $qid = $data->qid;
    $question = $data->question;
    $gid = $data->gid;
    $eid = $data->eid;
    $toption = $data->toption;
    $mark = $data->mark;
    $upload = $data->upload;
    $options = $data->options;
    
    // Update question details
    $stmt = $pdo->prepare("UPDATE question SET question = ?, gid = ?, eid = ?, toption = ?, mark = ?, upload = ? WHERE qid = ?");
    $stmt->execute([$question, $gid, $eid, $toption, $mark, $upload, $qid]);
    
    // Remove old options and answers
    $stmt = $pdo->prepare("DELETE FROM qoption WHERE qid = ?");
    $stmt->execute([$qid]);
    $stmt = $pdo->prepare("DELETE FROM queans WHERE qid = ?");
    $stmt->execute([$qid]);
    
    // Insert new options and correct answer
    foreach ($options as $option) {
        $stmt = $pdo->prepare("INSERT INTO qoption (qid, name, img) VALUES (?, ?, ?)");
        $stmt->execute([$qid, $option->name, $option->img]);
        $oid = $pdo->lastInsertId();
        
        if ($option->is_correct) {
            $stmt = $pdo->prepare("INSERT INTO queans (qid, oid) VALUES (?, ?)");
            $stmt->execute([$qid, $oid]);
        }
    }
    
    echo json_encode(['status' => 'success']);
}

// Delete a question
if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $data = json_decode(file_get_contents("php://input"));
    
    $qid = $data->qid;
    
    // Remove options and answers
    $stmt = $pdo->prepare("DELETE FROM qoption WHERE qid = ?");
    $stmt->execute([$qid]);
    $stmt = $pdo->prepare("DELETE FROM queans WHERE qid = ?");
    $stmt->execute([$qid]);
    
    // Remove the question
    $stmt = $pdo->prepare("DELETE FROM question WHERE qid = ?");
    $stmt->execute([$qid]);
    
    echo json_encode(['status' => 'success']);
}

// Get questions
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $stmt = $pdo->prepare("SELECT * FROM question");
    $stmt->execute();
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($questions as &$question) {
        $qid = $question['qid'];
        $stmt = $pdo->prepare("SELECT * FROM qoption WHERE qid = ?");
        $stmt->execute([$qid]);
        $question['options'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("SELECT * FROM queans WHERE qid = ?");
        $stmt->execute([$qid]);
        $correctAnswer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $question['correct_option'] = $correctAnswer ? $correctAnswer['oid'] : null;
    }
    
    echo json_encode($questions);
}


?>
