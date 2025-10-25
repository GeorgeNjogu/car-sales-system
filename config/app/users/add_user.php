<?php
require_once '../../config/database.php';

// Sample data — later we’ll connect this to a form
$username = 'admin';
$password = password_hash('12345', PASSWORD_DEFAULT);
$role = 'admin';

try {
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$username, $password, $role]);
    echo "✅ User added successfully!";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
