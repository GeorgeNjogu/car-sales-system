<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "car_user";      // the username you created
$pass = "Jazz2015#";   // the password you set
$db   = "car_sales_db";  // your database name

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
} else {
    // Optional success message
    // echo "✅ Connected successfully to car_sales_db";
}
?>
