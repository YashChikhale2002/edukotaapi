<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include the configuration file
include_once '../config.php';

try {
    // Prepare and execute the query to fetch user details
    $query = "SELECT uid, username, password, role, name, parents_number, contact_number, address, class, school, date_of_exam, course, estatus, examdate FROM users";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    // Fetch the results
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Append the default password to each user
    foreach ($users as &$user) {
        $user['default_password'] = 'EKNag@9860'; // Add the default password field
    }

    // Return the users in JSON format
    echo json_encode($users);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
}
?>
