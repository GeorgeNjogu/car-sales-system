<?php
$host = "localhost";
$user = "root";      // the username you created
$pass = "";   // the password you set
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
