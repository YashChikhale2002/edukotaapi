<?php
include_once '../config.php'; // Include your database configuration
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

try {
    $stmt = $pdo->prepare("
        SELECT 
            ur.report_id,
            u.uid,
            u.username,
            u.name,
            u.parents_number,
            ur.eid,
            ur.total_questions,
            ur.total_answered,
            ur.total_correct,
            ur.total_marked_for_review,
            ur.score,
            ur.report_generated_time,
            oe.name AS exam_name,
            oe.class AS exam_class,
            oe.insid,
            oe.estatus,
            oe.duration,
            oe.tmarks,
            oe.date AS exam_date
        FROM 
            user_reports ur
        JOIN 
            onlineexam oe ON ur.eid = oe.eid
        JOIN 
            users u ON ur.uid = u.uid
    ");
    
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
