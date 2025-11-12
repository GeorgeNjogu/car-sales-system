<?php
include_once __DIR__ . '/../../includes/header.php';
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 60px auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #333; }
        a { display: block; margin: 12px 0; color: #007BFF; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<?php
include_once(__DIR__ . "/../../config/db.connection.php");
$result = $conn->query("SELECT name, email, two_factor_enabled FROM users");
?>
<div class="container">
    <h2>Welcome, Admin!</h2>
    <p>Use the links below to manage the system:</p>
    <a href="manage_users.php">Manage Users</a>
    <a href="../../auth/login.php">Login</a>
    <a href="../../auth/register.php">Register New User</a>
    <a href="../../auth/logout.php">Logout</a>
    <hr>
    <h3>User 2FA Status</h3>
    <table style="width:100%;border-collapse:collapse;">
        <tr style="background:#f3f4f6;"><th style="text-align:left;padding:8px;">Name</th><th style="text-align:left;padding:8px;">Email</th><th style="text-align:left;padding:8px;">2FA Enabled</th></tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td style="padding:8px;"><?= htmlspecialchars($row['name']) ?></td>
            <td style="padding:8px;"><?= htmlspecialchars($row['email']) ?></td>
            <td style="padding:8px; color:<?= $row['two_factor_enabled'] ? '#28a745' : '#dc3545' ?>;">
                <?= $row['two_factor_enabled'] ? 'Yes' : 'No' ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
