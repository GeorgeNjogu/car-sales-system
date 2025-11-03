<?php
session_start();
include_once(__DIR__ . "/../../config/db.connection.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../../auth/login.php');
    exit;
}

$seller_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM cars WHERE seller_id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>My Cars</h2>";
while ($row = $result->fetch_assoc()) {
    echo "<div>";
    echo "<strong>{$row['make']} {$row['model']}</strong> - {$row['price']}<br>";
    echo "Status: {$row['status']}<br>";
    echo "<a href='edit_car.php?id={$row['car_id']}'>Edit</a> | ";
    echo "<a href='delete_car.php?id={$row['car_id']}'>Delete</a>";
    echo "</div><hr>";
}
?>
