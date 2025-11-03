<?php
session_start();
include_once(__DIR__ . "/../../config/db.connection.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login page if not logged in or not admin
    header('Location: ../../auth/login.php');
    exit;
}

$result = $conn->query("SELECT * FROM users");

echo "<h2>Manage Users</h2>";
while ($user = $result->fetch_assoc()) {
    echo "{$user['name']} ({$user['role']}) - {$user['status']} ";
    if ($user['status'] == 'active') {
        echo "<a href='suspend_user.php?id={$user['user_id']}'>Suspend</a>";
    } else {
        echo "<a href='activate_user.php?id={$user['user_id']}'>Activate</a>";
    }
    echo "<hr>";
}
?>
