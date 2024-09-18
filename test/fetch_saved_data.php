<?php


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: Content-Type");


// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "edukotaexam";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch saved data
$sql = "SELECT question FROM testopp"; // Adjust the query based on your table structure

$result = $conn->query($sql);

$response = array();

if ($result->num_rows > 0) {
    // Fetch all rows and store in response array
    while($row = $result->fetch_assoc()) {
        $response[] = $row;
    }
} else {
    $response = array('message' => 'No data found');
}

$conn->close();

// Set content type to JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
