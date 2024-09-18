<?php
header("Content-Type: application/json");

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include the configuration file
include_once '../config.php';


// Fetch exam by ID
$eid = isset($_GET['eid']) ? (int)$_GET['eid'] : 0;

if ($eid > 0) {
    $sql = "SELECT * FROM onlineexam WHERE eid = :eid";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':eid', $eid, PDO::PARAM_INT);
    
    try {
        $stmt->execute();
        $exam = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($exam) {
            echo json_encode($exam);
        } else {
            echo json_encode(["message" => "Exam not found"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["message" => "Query failed: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["message" => "Invalid exam ID"]);
}

$pdo = null;
?>
