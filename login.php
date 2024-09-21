<?php
require 'config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$data = json_decode(file_get_contents("php://input"));

$username = $data->username;
$password = $data->password;

$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    // Return the role along with the success message
    echo json_encode([
        "message" => "Login successful",
        "user" => [
            "username" => $user['username'],
            "role" => $user['role']  // Assuming you have a 'role' column in your database
        ]
    ]);
} else {
    echo json_encode(["message" => "Invalid username or password"]);
}
?>
