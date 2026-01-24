<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config.php';

if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode([
        'available' => false, 
        'message' => 'Database connection failed'
    ]);
    exit;
}

$eid = isset($_GET['eid']) ? intval($_GET['eid']) : 0;

if ($eid <= 0) {
    echo json_encode([
        'available' => false, 
        'message' => 'Invalid exam ID'
    ]);
    exit;
}

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ✅ Query onlineexam table
    $query = "SELECT eid, name, class, estatus, duration, tmarks, date, start_time, end_time, schedule_enabled 
              FROM onlineexam 
              WHERE eid = :eid 
              LIMIT 1";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['eid' => $eid]);
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$exam) {
        echo json_encode([
            'available' => false, 
            'message' => 'Exam not found'
        ]);
        exit;
    }
    
    // ✅ Get current date and time in IST (Asia/Kolkata)
    date_default_timezone_set('Asia/Kolkata');
    $currentDateTime = new DateTime();
    $currentDate = $currentDateTime->format('Y-m-d');
    $currentTime = $currentDateTime->format('H:i:s');
    
    $examDate = $exam['date'];
    
    error_log("🕐 Exam {$eid}: Current={$currentDate} {$currentTime}, ExamDate={$examDate}, schedule_enabled={$exam['schedule_enabled']}, estatus={$exam['estatus']}");
    
    // ✅ PRIORITY 1: Check if schedule_enabled = 1 (TIME SLOT CONTROLS AVAILABILITY)
    if ($exam['schedule_enabled'] == 1) {
        error_log("✅ Schedule-based exam (schedule_enabled=1)");
        
        // Check date first
        if ($examDate !== $currentDate) {
            if ($currentDate < $examDate) {
                echo json_encode([
                    'available' => false,
                    'message' => 'This exam is scheduled for ' . date('d-M-Y', strtotime($examDate)),
                    'reason' => 'future_date',
                    'exam_date' => $examDate
                ]);
            } else {
                echo json_encode([
                    'available' => false,
                    'message' => 'This exam has already ended',
                    'reason' => 'past_date'
                ]);
            }
            exit;
        }
        
        // ✅ TIME SLOT VALIDATION
        if (!empty($exam['start_time']) && !empty($exam['end_time'])) {
            $startTime = $exam['start_time'];
            $endTime = $exam['end_time'];
            
            // Convert to HH:MM format
            $currentTimeFormatted = substr($currentTime, 0, 5);
            $startTimeFormatted = substr($startTime, 0, 5);
            $endTimeFormatted = substr($endTime, 0, 5);
            
            error_log("⏰ Time check: Current={$currentTimeFormatted}, Start={$startTimeFormatted}, End={$endTimeFormatted}");
            
            // ✅ Check if BEFORE start time
            if ($currentTimeFormatted < $startTimeFormatted) {
                echo json_encode([
                    'available' => false,
                    'message' => "This exam will be available from {$startTimeFormatted}",
                    'reason' => 'before_start_time',
                    'start_time' => $startTimeFormatted,
                    'current_time' => $currentTimeFormatted
                ]);
                exit;
            }
            
            // ✅ Check if AFTER end time
            if ($currentTimeFormatted > $endTimeFormatted) {
                echo json_encode([
                    'available' => false,
                    'message' => "This exam ended at {$endTimeFormatted}",
                    'reason' => 'after_end_time',
                    'end_time' => $endTimeFormatted,
                    'current_time' => $currentTimeFormatted
                ]);
                exit;
            }
            
            // ✅ WITHIN TIME SLOT - AVAILABLE!
            error_log("✅ Exam {$eid} is AVAILABLE! Within time slot.");
            
            echo json_encode([
                'available' => true,
                'message' => 'Exam is available now',
                'exam' => $exam,
                'current_time' => $currentTimeFormatted,
                'time_slot' => "{$startTimeFormatted} - {$endTimeFormatted}"
            ]);
            exit;
        }
    }
    
    // ✅ PRIORITY 2: If schedule_enabled = 0, use estatus flag
    error_log("📋 Manual publishing mode (schedule_enabled=0 or no time slot)");
    
    // Check if exam is published manually
    if ($exam['estatus'] != '1') {
        echo json_encode([
            'available' => false, 
            'message' => 'This exam is not published yet',
            'reason' => 'not_published'
        ]);
        exit;
    }
    
    // Check date
    if ($examDate !== $currentDate) {
        if ($currentDate < $examDate) {
            echo json_encode([
                'available' => false,
                'message' => 'This exam is scheduled for ' . date('d-M-Y', strtotime($examDate)),
                'reason' => 'future_date'
            ]);
        } else {
            echo json_encode([
                'available' => false,
                'message' => 'This exam has already ended',
                'reason' => 'past_date'
            ]);
        }
        exit;
    }
    
    // ✅ Manual mode - Available if published and date matches
    error_log("✅ Exam {$eid} is AVAILABLE! (Manual publishing mode)");
    
    echo json_encode([
        'available' => true,
        'message' => 'Exam is available',
        'exam' => $exam
    ]);
    
} catch (PDOException $e) {
    error_log("❌ Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'available' => false,
        'message' => 'Server error occurred'
    ]);
}

$pdo = null;
?>
