<?php
$servername = "localhost";
$port = 3307; // change if needed
$username = "root";
$password = "1234";
$dbname = "carsales";

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
