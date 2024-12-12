<?php
include_once '../config.php'; // Include your database configuration

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

// Get the 'uid' parameter from the URL
$uid = isset($_GET['uid']) ? $_GET['uid'] : null;

if (!$uid) {
    echo json_encode(['error' => 'User ID not provided']);
    exit;
}

try {
    // SQL query to fetch the detailed result for the given user ID
    $sql = "
        SELECT 
            u.name AS student_name, 
            u.contact_number,  -- Assuming 'contact_number' is the column for user's contact number
            q.question, 
            qo.name AS selected_option, 
            qo.is_correct, 
            g.title AS question_group,
            q.mark
        FROM 
            user_answers ua
            INNER JOIN users u ON ua.uid = u.uid
            INNER JOIN question q ON ua.qid = q.qid
            INNER JOIN qoption qo ON ua.selected_option = qo.oid
            INNER JOIN quegrp g ON q.gid = g.gid
        WHERE 
            ua.uid = :uid
        ORDER BY 
            g.gid, q.qid;
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $uid]);

    // Fetch the results as an associative array
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        // Prepare summary
        $summary = [];
        foreach ($results as $result) {
            $groupTitle = $result['question_group'];
            if (!isset($summary[$groupTitle])) {
                $summary[$groupTitle] = [
                    'correct' => 0,
                    'incorrect' => 0,
                    'total' => 0
                ];
            }

            // Increment total questions
            $summary[$groupTitle]['total']++;

            // Count correct and incorrect answers
            if ($result['is_correct']) {
                $summary[$groupTitle]['correct']++;
            } else {
                $summary[$groupTitle]['incorrect']++;
            }
        }

        // Add student name and contact to the response
        $response = [
            'results' => $results,
            'summary' => $summary,
            'student_name' => $results[0]['student_name'], // Assuming all results belong to the same user
            'contact_number' => $results[0]['contact_number']
        ];

        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'No results found for this user']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
}
?>
