<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost:3303';
$port = 3303;                 // change only if your MySQL uses another port
$username = 'root';           // or your MySQL user
$password = 'Jazz2015';               // your MySQL password
$database = 'car_sales_db';

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
// echo "✅ DB connected";
?>


