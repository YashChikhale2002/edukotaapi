<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config.php';

if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents("php://input"));

// Validate required fields
if (!isset($data->eid)) {
    echo json_encode(['success' => false, 'message' => 'Exam ID is required']);
    exit;
}

$eid = $data->eid;
$name = isset($data->name) ? $data->name : null;
$class = isset($data->class) ? $data->class : null;
$insid = isset($data->insid) ? $data->insid : null;
$estatus = isset($data->estatus) ? $data->estatus : '0';
$duration = isset($data->duration) ? $data->duration : null;
$tmarks = isset($data->tmarks) ? $data->tmarks : null;
$date = isset($data->date) ? $data->date : null;
$start_time = isset($data->start_time) ? $data->start_time : null;
$end_time = isset($data->end_time) ? $data->end_time : null;
$schedule_enabled = isset($data->schedule_enabled) ? $data->schedule_enabled : 0;

try {
    // Update query
    $query = "UPDATE onlineexam 
              SET name = :name, 
                  class = :class, 
                  insid = :insid, 
                  estatus = :estatus, 
                  duration = :duration, 
                  tmarks = :tmarks, 
                  date = :date,
                  start_time = :start_time,
                  end_time = :end_time,
                  schedule_enabled = :schedule_enabled
              WHERE eid = :eid";

    $stmt = $pdo->prepare($query);

    // Bind all parameters
    $stmt->bindParam(':eid', $eid);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':class', $class);
    $stmt->bindParam(':insid', $insid);
    $stmt->bindParam(':estatus', $estatus);
    $stmt->bindParam(':duration', $duration);
    $stmt->bindParam(':tmarks', $tmarks);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':start_time', $start_time);
    $stmt->bindParam(':end_time', $end_time);
    $stmt->bindParam(':schedule_enabled', $schedule_enabled);

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Exam updated successfully.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update exam.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$pdo = null;
?>
