<?php
// $host = 'localhost';
// $db = 'edukotaexam';
// $user = 'root';
// $pass = '';


$host = 'localhost';
$db = 'u267553827_edukotaexam';
$user = 'u267553827_edukotaexam';
$pass = 'Abhi@9860';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $db :" . $e->getMessage());
}
?>
