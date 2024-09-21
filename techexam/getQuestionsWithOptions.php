<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../config.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Prepare the SQL query
    $sql = "
        SELECT 
            q.qid AS question_id,
            q.question,
            q.gid,
            q.eid,
            q.toption,
            q.mark,
            q.upload,
            o.oid AS option_id,
            o.name AS option_name,
            o.img AS option_image
        FROM 
            question q
        JOIN 
            qoption o ON q.qid = o.qid
    ";

    try {
        // Execute the SQL query
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        // Fetch the results
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the results as JSON
        echo json_encode($results);

    } catch (PDOException $e) {
        // Handle SQL errors
        echo json_encode(["error" => $e->getMessage()]);
    }
} else {
    // Handle invalid request methods
    echo json_encode(["message" => "Invalid request method"]);
}
?>
