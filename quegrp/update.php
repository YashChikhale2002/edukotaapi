<?php
include_once '../config.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: Content-Type");
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->gid) && !empty($data->Title)) {
    $query = "UPDATE quegrp SET Title = :Title WHERE gid = :gid";
    $stmt = $pdo->prepare($query);

    $stmt->bindParam(':Title', $data->Title);
    $stmt->bindParam(':gid', $data->gid);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Group updated successfully']);
    } else {
        echo json_encode(['message' => 'Group could not be updated']);
    }
} else {
    echo json_encode(['message' => 'Incomplete data']);
}
?>
