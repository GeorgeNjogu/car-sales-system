<?php
$host = 'localhost:3303';
$port = 3303;
$user = 'root'; // change if your MySQL user is different
$pass = 'Jazz2015#';     // enter your MySQL password if you have one
$dbname = 'car_sales_db'; // we’ll create this next

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful!";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>



