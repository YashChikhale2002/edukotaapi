<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include the configuration file
include_once '../config.php'; // Ensure this file sets up the $pdo variable

// Get the exam ID (eid) from the request
$eid = isset($_GET['eid']) ? intval($_GET['eid']) : 0;

// Validate the exam ID
if ($eid <= 0) {    
    echo json_encode(['error' => 'Invalid exam ID']);
    exit;
}

try {
    // Prepare and execute the query with the correct join condition for 'quegrp'
    $stmt = $pdo->prepare("
        SELECT 
            q.qid,
            q.question,
            q.toption,
            q.mark,
            q.ans,
            q.eid,
            q.gid,                      -- Get the gid from the question table
            qg.Title AS group_title,     -- Get the Title from quegrp based on gid
            qo.oid,
            qo.name AS option_name,
            qo.is_correct
        FROM 
            question q
        LEFT JOIN 
            quegrp qg ON q.gid = qg.gid   -- Join quegrp on q.gid = qg.gid
        LEFT JOIN 
            qoption qo ON q.qid = qo.qid
        WHERE 
            q.eid = :eid
    ");
    $stmt->execute(['eid' => $eid]);

    // Fetch the results
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organize results
    $result = [];
    foreach ($questions as $row) {
        $qid = $row['qid'];
        if (!isset($result[$qid])) {
            $result[$qid] = [
                'qid' => $row['qid'],
                'question' => $row['question'],
                'toption' => $row['toption'],
                'mark' => $row['mark'],
                'ans' => $row['ans'],
                'gid' => $row['gid'],              // Now fetches gid based on each question
                'group_title' => $row['group_title'],
                'options' => []
            ];
        }
        if ($row['oid']) {
            $result[$qid]['options'][] = [
                'oid' => $row['oid'],
                'name' => $row['option_name'],
                'is_correct' => $row['is_correct']
            ];
        }
    }

    // Return the organized data
    echo json_encode(array_values($result));
    
} catch (PDOException $e) {
    // Return error message if query fails
    echo json_encode(['error' => 'Failed to fetch content: ' . $e->getMessage()]);
}
?>
