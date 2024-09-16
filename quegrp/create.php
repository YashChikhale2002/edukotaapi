<?php
include_once '../config.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->Title)) {
    $query = "INSERT INTO quegrp (Title) VALUES (:Title)";
    $stmt = $pdo->prepare($query);

    $stmt->bindParam(':Title', $data->Title);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Group created successfully']);
    } else {
        echo json_encode(['message' => 'Group could not be created']);
    }
} else {
    echo json_encode(['message' => 'Incomplete data']);
}
?>
