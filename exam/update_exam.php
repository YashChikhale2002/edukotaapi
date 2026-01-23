<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include the configuration file
include_once '../config.php';

// Get the exam data
$input = json_decode(file_get_contents('php://input'), true);

$eid = isset($input['eid']) ? intval($input['eid']) : 0;
$name = isset($input['name']) ? trim($input['name']) : '';
$class = isset($input['class']) ? trim($input['class']) : '';
$insid = isset($input['insid']) ? trim($input['insid']) : '';
$estatus = isset($input['estatus']) ? trim($input['estatus']) : '0';
$duration = isset($input['duration']) ? trim($input['duration']) : '';
$tmarks = isset($input['tmarks']) ? trim($input['tmarks']) : '';
$date = isset($input['date']) ? trim($input['date']) : '';

// Validate required fields
if ($eid <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid exam ID']);
    exit;
}

if (empty($name) || empty($class) || empty($duration) || empty($tmarks) || empty($date)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    // Update the exam
    $sql = "UPDATE onlineexam SET 
            name = :name, 
            class = :class, 
            insid = :insid, 
            estatus = :estatus, 
            duration = :duration, 
            tmarks = :tmarks, 
            date = :date 
            WHERE eid = :eid";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'class' => $class,
        'insid' => $insid,
        'estatus' => $estatus,
        'duration' => $duration,
        'tmarks' => $tmarks,
        'date' => $date,
        'eid' => $eid
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Exam updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or exam not found']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to update exam: ' . $e->getMessage()]);
}

$pdo = null;
?>
