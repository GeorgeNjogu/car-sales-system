<?php
// admin_reset_password.php
// Admins enter their email and the unique code to reset their password
error_reporting(E_ALL);
ini_set('display_errors', 1);
// ...existing code...

session_start();
include_once(__DIR__ . '/../../config/db.connection.php');

// Require admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['code'], $_POST['new_password'])) {
    $email = trim($_POST['email']);
    $code = trim($_POST['code']);
    $newPassword = $_POST['new_password'];
    if (strlen($newPassword) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Find admin user
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND role = 'admin'");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            // Check code
            $stmt2 = $conn->prepare("SELECT * FROM password_resets WHERE user_id = ? AND code = ? AND expires_at > NOW() ORDER BY expires_at DESC LIMIT 1");
            $stmt2->bind_param('is', $row['user_id'], $code);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            if ($res2->fetch_assoc()) {
                // Update password
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt3 = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                $stmt3->bind_param('si', $hash, $row['user_id']);
                $stmt3->execute();
                // Invalidate code
                $conn->query("DELETE FROM password_resets WHERE user_id = " . $row['user_id']);
                $message = 'Password reset successful. You can now <a href=\"../../auth/login.php\">login</a>.';
            } else {
                $error = 'Invalid or expired code.';
            }
        } else {
            $error = 'No admin account found with that email.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Reset Password</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; }
        .container { max-width: 400px; margin: 60px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
        .error { color: #dc3545; margin-bottom: 10px; }
        .success { color: #28a745; margin-bottom: 10px; }
    </style> 
</head>
<body>
<div class="container">
    <h2>Admin Password Reset</h2>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($message): ?><div class="success"><?= $message ?></div><?php endif; ?>
    <form method="POST">
        <label for="email">Admin Email:</label><br>
        <input type="email" name="email" id="email" required><br><br>
        <label for="code">Reset Code:</label><br>
        <input type="text" name="code" id="code" required><br><br>
        <label for="new_password">New Password:</label><br>
        <input type="password" name="new_password" id="new_password" required><br><br>
        <button type="submit">Reset Password</button>
    </form>
    <br>
    <a href="../../auth/login.php">Back to Login</a>
</div>
</body>
</html>
