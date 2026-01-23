<?php

include_once '../config.php'; // Include your database configuration
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Get the raw POST data    
$input = json_decode(file_get_contents('php://input'), true);

// Validate the 'uid' field
if (!isset($input['uid']) || empty($input['uid'])) {
    echo json_encode(["success" => false, "error" => "Missing or invalid 'uid' field"]);
    exit;
}

$uid = $input['uid']; // Get 'uid' from the JSON payload

try {
    // Check if the user exists and fetch their estatus
    $stmt = $pdo->prepare("SELECT estatus FROM users WHERE uid = :uid");
    $stmt->execute([':uid' => $uid]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["success" => false, "error" => "User not found", "estatus" => "1"]);
        exit;
        }

    if ($user['estatus'] != 0) {
        echo json_encode(["success" => false, "error" => "User has already taken the exam", "estatus" => $user['estatus']]);
        exit;
    }

    // If the user passes all checks
    echo json_encode(["success" => true, "message" => "User is eligible to take the exam", "estatus" => $user['estatus']]);

} catch (PDOException $e) {
    // Handle database-related errors
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
