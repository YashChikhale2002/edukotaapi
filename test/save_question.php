<?php
header("Content-Type: application/json");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: Content-Type");


// Get the raw input data from the request
$data = json_decode(file_get_contents("php://input"), true);

// Check if the 'question' field is present in the request
if (!isset($data['question']) || empty(trim($data['question']))) {
    echo json_encode(["success" => false, "message" => "No question content provided"]);
    exit;
}

$question = $data['question'];

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "edukotaexam";

$conn = new mysqli($host, $user, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}

// Prepare the SQL query
$stmt = $conn->prepare("INSERT INTO testopp (question) VALUES (?)");
$stmt->bind_param("s", $question);

// Execute the query and return response
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Question saved successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to save question"]);
}

$stmt->close();
$conn->close();
?>
