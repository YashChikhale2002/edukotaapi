<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$data = json_decode(file_get_contents("php://input"));

// Check if all required fields are provided
if (!$data || 
    !isset($data->username) || 
    !isset($data->password) || 
    !isset($data->parentNumber) || 
    !isset($data->contactNo) || 
    !isset($data->address) || 
    !isset($data->userClass) || 
    !isset($data->schoolName) || 
    !isset($data->course) || 
    !isset($data->dateOfExam)) {
    echo json_encode(["message" => "Invalid input"]);
    exit;
}

$username = $data->username;
$contactNo = $data->contactNo;
$password = password_hash($data->password, PASSWORD_DEFAULT);
$parentNumber = $data->parentNumber;
$address = $data->address;
$userClass = $data->userClass;
$schoolName = $data->schoolName;
$course = $data->course;
$dateOfExam = $data->dateOfExam;

// Check if the user already exists (based on username or contact number)
$sql_check = "SELECT COUNT(*) FROM users WHERE username = ? OR contact_no = ?";
$stmt_check = $pdo->prepare($sql_check);
$stmt_check->execute([$username, $contactNo]);
$user_exists = $stmt_check->fetchColumn();

if ($user_exists > 0) {
    echo json_encode(["message" => "User already registered with this username or contact number"]);
    exit;
}

// Generate a unique user ID starting with "EDU"
$user_id = uniqid("EDU");

// Prepare SQL query to insert the new user
$sql = "INSERT INTO users (id, username, password, parent_number, contact_no, address, class, school_name, course, date_of_exam, role) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'student')";
$stmt = $pdo->prepare($sql);

try {
    if ($stmt->execute([$user_id, $username, $password, $parentNumber, $contactNo, $address, $userClass, $schoolName, $course, $dateOfExam])) {
        echo json_encode(["message" => "User registered successfully", "user_id" => $user_id, "username" => $username]);
    } else {
        echo json_encode(["message" => "Failed to register user"]);
    }
} catch (PDOException $e) {
    // Check if it's a duplicate entry error (SQLSTATE 23000)
    if ($e->getCode() == 23000) {
        echo json_encode(["message" => "Duplicate entry: The user ID or contact number already exists. Please try again."]);
    } else {
        echo json_encode(["message" => "Database error: " . $e->getMessage()]);
    }
}
?>
