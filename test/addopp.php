<?php
// store_question.php

header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"), true); // Decode incoming JSON data

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection details
$host = 'localhost';
$db = 'edukotaexam';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db";

try {
    // Create a new PDO instance
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Check if the question field is set and not empty
    if (isset($data['question']) && !empty($data['question'])) {
        $question = $data['question'];

        // Prepare the SQL statement to insert the question into the database



        $stmt = $pdo->prepare("INSERT INTO addopp (question) VALUES (:question)");
        $stmt->bindParam(':question', $question);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(["message" => "Question saved successfully"]);
        } else {
            echo json_encode(["message" => "Failed to save question"]);
        }
    } else {
        echo json_encode(["message" => "No question data provided"]);
    }
} catch (PDOException $e) {
    echo json_encode(["message" => "Database error: " . $e->getMessage()]);
}
