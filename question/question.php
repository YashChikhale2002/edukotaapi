<?php
include_once '../config.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
$data = json_decode(file_get_contents("php://input"));

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    // Add new option
    if (!empty($data->qid) && !empty($data->name)) {
        $query = "INSERT INTO qoption (qid, name) VALUES (:qid, :name)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':qid', $data->qid);
        $stmt->bindParam(':name', $data->name);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Option added successfully']);
        } else {
            echo json_encode(['message' => 'Failed to add option']);
        }
    } else {
        echo json_encode(['message' => 'Incomplete data']);
    }
} elseif ($method == 'PUT') {
    // Update option
    if (!empty($data->oid) && !empty($data->name)) {
        $query = "UPDATE qoption SET name = :name WHERE oid = :oid";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':oid', $data->oid);
        $stmt->bindParam(':name', $data->name);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Option updated successfully']);
        } else {
            echo json_encode(['message' => 'Failed to update option']);
        }
    } else {
        echo json_encode(['message' => 'Incomplete data']);
    }
} elseif ($method == 'DELETE') {
    // Delete option
    if (!empty($data->oid)) {
        $query = "DELETE FROM qoption WHERE oid = :oid";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':oid', $data->oid);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Option deleted successfully']);
        } else {
            echo json_encode(['message' => 'Failed to delete option']);
        }
    } else {
        echo json_encode(['message' => 'Incomplete data']);
    }
} else {
    echo json_encode(['message' => 'Invalid request method']);
}
?>
