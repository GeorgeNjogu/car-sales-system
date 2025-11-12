<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}
include_once(__DIR__ . '/../../config/db.connection.php');

// Quick stats
$userCount = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$buyerCount = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='buyer'")->fetch_assoc()['c'];
$sellerCount = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='seller'")->fetch_assoc()['c'];
$adminCount = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='admin'")->fetch_assoc()['c'];
$resetCount = $conn->query("SELECT COUNT(*) as c FROM password_resets WHERE expires_at > NOW()") ? $conn->query("SELECT COUNT(*) as c FROM password_resets WHERE expires_at > NOW()") ->fetch_assoc()['c'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; }
        .sidebar { width: 220px; background: #222; color: #fff; height: 100vh; position: fixed; top: 0; left: 0; padding: 30px 0; }
        .sidebar h2 { text-align: center; margin-bottom: 30px; }
        .sidebar a { display: block; color: #fff; text-decoration: none; padding: 12px 30px; margin: 8px 0; }
        .sidebar a:hover { background: #007BFF; }
        .main { margin-left: 240px; padding: 40px; }
        .card { background: #fff; border-radius: 10px; box-shadow: 0 0 10px #ccc; padding: 30px; margin-bottom: 30px; }
        .stats { display: flex; gap: 30px; }
        .stat { background: #f3f4f6; border-radius: 8px; padding: 20px; flex: 1; text-align: center; }
        .stat h3 { margin: 0 0 10px 0; color: #007BFF; }
        .stat span { font-size: 2em; font-weight: bold; }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_home.php">Dashboard</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="admin_forgot_password.php">Password Reset</a>
    <a href="../../auth/logout.php">Logout</a>
</div>
<div class="main">
    <div class="card">
        <h2>Welcome, Admin!</h2>
        <p>Use the sidebar to manage users, reset passwords, and view system stats.</p>
    </div>
    <div class="stats">
        <div class="stat"><h3>Total Users</h3><span><?= $userCount ?></span></div>
        <div class="stat"><h3>Buyers</h3><span><?= $buyerCount ?></span></div>
        <div class="stat"><h3>Sellers</h3><span><?= $sellerCount ?></span></div>
        <div class="stat"><h3>Admins</h3><span><?= $adminCount ?></span></div>
        <div class="stat"><h3>Active Reset Codes</h3><span><?= $resetCount ?></span></div>
    </div>
</div>
</body>
</html>
