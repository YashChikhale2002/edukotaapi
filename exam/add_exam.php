<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config.php';

if (!$pdo) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents("php://input"));

// Validate required fields
if (
    isset($data->name) && 
    isset($data->class) && 
    isset($data->insid) && 
    isset($data->duration) && 
    isset($data->tmarks) && 
    isset($data->date)
) {
    $name = $data->name;
    $class = $data->class;
    $insid = $data->insid;
    $estatus = isset($data->estatus) ? $data->estatus : '0';
    $duration = $data->duration;
    $tmarks = $data->tmarks;
    $date = $data->date;
    
    // Handle optional time scheduling fields
    $start_time = isset($data->start_time) ? $data->start_time : null;
    $end_time = isset($data->end_time) ? $data->end_time : null;
    $schedule_enabled = isset($data->schedule_enabled) ? $data->schedule_enabled : 0;

    try {
        // Insert query with all fields
        $query = "INSERT INTO onlineexam 
                  (name, class, insid, estatus, duration, tmarks, date, start_time, end_time, schedule_enabled) 
                  VALUES 
                  (:name, :class, :insid, :estatus, :duration, :tmarks, :date, :start_time, :end_time, :schedule_enabled)";

        $stmt = $pdo->prepare($query);

        // Bind all parameters
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
            $lastId = $pdo->lastInsertId();
            echo json_encode([
                'status' => 'success', 
                'message' => 'Exam added successfully.',
                'eid' => $lastId
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add exam.']);
        }

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
}

$pdo = null;
?>
