<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=car_sales", "root", "");
    echo " Connected successfully to car_sales database!";
} catch (PDOException $e) {
    echo " Database connection failed: " . $e->getMessage();
}
?>
