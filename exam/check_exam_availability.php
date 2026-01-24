<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// ✅ Direct database connection (no config file needed)
$host = "localhost";
$db = "techinbo_rcat";  // Your database name
$user = "root";
$pass = "";  // Your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $eid = isset($_GET['eid']) ? $_GET['eid'] : die(json_encode(['success' => false, 'message' => 'No exam ID provided']));

    $query = "SELECT eid, name, estatus, schedule_enabled, start_time, end_time, date
              FROM onlineexam
              WHERE eid = :eid
              LIMIT 1";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":eid", $eid);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $is_available = false;
        $message = "";
        
        // Check if scheduling is enabled
        if ($row['schedule_enabled'] == 1 && !empty($row['start_time']) && !empty($row['end_time'])) {
            $current_time = date('H:i:s');
            $current_date = date('Y-m-d');
            $exam_date = $row['date'];
            
            // Check if exam is on today's date
            if ($current_date == $exam_date) {
                // Check if current time is within the scheduled slot
                if ($current_time >= $row['start_time'] && $current_time <= $row['end_time']) {
                    $is_available = true;
                    $message = "Exam is currently available";
                } else if ($current_time < $row['start_time']) {
                    $is_available = false;
                    $message = "Exam will start at " . date('g:i A', strtotime($row['start_time']));
                } else {
                    $is_available = false;
                    $message = "Exam has ended";
                }
            } else if ($current_date < $exam_date) {
                $is_available = false;
                $message = "Exam is scheduled for " . date('d-M-Y', strtotime($exam_date));
            } else {
                $is_available = false;
                $message = "Exam has ended";
            }
        } else {
            // No scheduling, use estatus
            $is_available = ($row['estatus'] == '1');
            $message = $is_available ? "Exam is available" : "Exam is not published";
        }
        
        echo json_encode(array(
            "success" => true,
            "is_available" => $is_available,
            "message" => $message,
            "exam" => $row
        ));
    } else {
        echo json_encode(array(
            "success" => false,
            "message" => "Exam not found"
        ));
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
