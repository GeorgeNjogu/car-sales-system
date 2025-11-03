<?php
session_start();
include_once(__DIR__ . "/../../config/db.connection.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $seller_id = $_SESSION['user_id'];
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $price = $_POST['price'];
    $mileage = $_POST['mileage'];
    $transmission = $_POST['transmission'];
    $fuel_type = $_POST['fuel_type'];
    $color = $_POST['color'];
    $condition = $_POST['car_condition'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO cars (seller_id, make, model, year, price, mileage, transmission, fuel_type, color, car_condition, description)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issidisssss", $seller_id, $make, $model, $year, $price, $mileage, $transmission, $fuel_type, $color, $condition, $description);

    if ($stmt->execute()) {
        echo "Car added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<form method="POST">
    <h2>Add Car</h2>
    <input name="make" placeholder="Make" required><br>
    <input name="model" placeholder="Model" required><br>
    <input name="year" placeholder="Year" required><br>
    <input name="price" placeholder="Price" required><br>
    <input name="mileage" placeholder="Mileage"><br>
    <select name="transmission">
        <option value="manual">Manual</option>
        <option value="automatic">Automatic</option>
    </select><br>
    <select name="fuel_type">
        <option value="petrol">Petrol</option>
        <option value="diesel">Diesel</option>
        <option value="electric">Electric</option>
        <option value="hybrid">Hybrid</option>
    </select><br>
    <input name="color" placeholder="Color"><br>
    <select name="car_condition">
        <option value="new">New</option>
        <option value="used">Used</option>
    </select><br>
    <textarea name="description" placeholder="Description"></textarea><br>
    <button type="submit">Add Car</button>
</form>
