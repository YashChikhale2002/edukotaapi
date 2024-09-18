<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include the configuration file
include_once '../config.php';

// Check if $pdo is defined
if (!$pdo) {
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

try {
    $query = "SELECT insid as id, title as name FROM instructions";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $insts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($insts);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
}

$pdo = null; // Close the connection
?>
