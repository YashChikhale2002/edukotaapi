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
    
    // Query with snake_case column names
    $query = "SELECT eid, name, class, insid, estatus, duration, tmarks, date, 
              start_time, end_time, schedule_enabled 
              FROM onlineexam 
              ORDER BY date DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($exams);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
