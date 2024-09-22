<?php
require 'config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Decode the incoming JSON data
$data = json_decode(file_get_contents("php://input"));

$username = $data->username;
$password = $data->password;

// Fetch the user information based on the username
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    // If the password is correct, return all user information
    echo json_encode([
        "message" => "Login successful",
        "user" => $user  // Return all user information
    ]);
} else {
    // If login fails, return an error message
    echo json_encode([
        "message" => "Invalid username or password"
    ]);
}
?>
