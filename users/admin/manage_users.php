<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include_once(__DIR__ . "/../../config/db.connection.php");
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}
// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $deleteId);
    $stmt->execute();
    echo "<div style='color: green;'>User deleted successfully.</div>";
}
// Handle user addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    if ($name && $email && $role && $password) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, role, password_hash, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param('sssss', $name, $email, $phone, $role, $passwordHash);
        $stmt->execute();
        echo "<div style='color: green;'>User added successfully.</div>";
    } else {
        echo "<div style='color: red;'>All fields are required to add a user.</div>";
    }
}
$result = $conn->query("SELECT * FROM users");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; }
        .sidebar { width: 220px; background: #222; color: #fff; height: 100vh; position: fixed; top: 0; left: 0; padding: 30px 0; }
        .sidebar h2 { text-align: center; margin-bottom: 30px; }
        .sidebar a { display: block; color: #fff; text-decoration: none; padding: 12px 30px; margin: 8px 0; }
        .sidebar a:hover { background: #007BFF; }
        .main { margin-left: 240px; padding: 40px; }
        table { border-collapse: collapse; width: 100%; background: #fff; }
        th, td { padding: 10px; border: 1px solid #ddd; }
        th { background: #f3f4f6; }
        .form-section { background: #fff; border-radius: 10px; box-shadow: 0 0 10px #ccc; padding: 30px; margin-top: 30px; }
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
    <h2>Manage Users</h2>
    <table>
        <tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Action</th></tr>
        <?php while ($user = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['phone']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td><?= htmlspecialchars($user['status']) ?></td>
            <td><a href="manage_users.php?delete=<?= $user['user_id'] ?>" onclick="return confirm('Delete this user?');">Delete</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <div class="form-section">
        <h3>Add New User</h3>
        <form method="POST" action="">
            Name: <input type="text" name="name" required><br>
            Email: <input type="email" name="email" required><br>
            Phone: <input type="text" name="phone"><br>
            Role: <select name="role" required><option value="buyer">Buyer</option><option value="seller">Seller</option><option value="admin">Admin</option></select><br>
            Password: <input type="password" name="password" required><br>
            <input type="submit" name="add_user" value="Add User">
        </form>
    </div>
</div>
</body>
</html>
