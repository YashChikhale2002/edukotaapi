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

try {
    // ✅ Get optional parameters
    $uid = isset($_GET['uid']) ? intval($_GET['uid']) : null;
    $eid = isset($_GET['eid']) ? intval($_GET['eid']) : null;
    
    error_log("✅ getUserResults.php called with uid=$uid, eid=$eid");
    
    // ✅ Build dynamic query - REMOVED u.email column
    $sql = "
        SELECT 
            ur.report_id,
            ur.uid,
            ur.eid,
            ur.total_questions,
            ur.total_answered,
            ur.total_correct,
            ur.total_marked_for_review,
            ur.score,
            ur.report_generated_time,
            u.username,
            u.name,
            u.parents_number,
            oe.name AS exam_name,
            oe.class AS exam_class,
            oe.date AS exam_date,
            oe.duration,
            oe.tmarks
        FROM user_reports ur
        JOIN users u ON ur.uid = u.uid
        JOIN onlineexam oe ON ur.eid = oe.eid
        WHERE 1=1
    ";
    
    $params = [];
    
    // ✅ Add filters if provided
    if ($uid) {
        $sql .= " AND ur.uid = :uid";
        $params['uid'] = $uid;
    }
    
    if ($eid) {
        $sql .= " AND ur.eid = :eid";
        $params['eid'] = $eid;
    }
    
    $sql .= " ORDER BY ur.report_generated_time DESC";
    
    error_log("✅ Executing query: $sql");
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("✅ Found " . count($results) . " results");
    
    // ✅ Always return array (even if empty)
    echo json_encode($results);
    
} catch (PDOException $e) {
    error_log("❌ Database error: " . $e->getMessage());
    echo json_encode([
        'error' => 'Failed to fetch user results',
        'message' => $e->getMessage()
    ]);
}
?>
