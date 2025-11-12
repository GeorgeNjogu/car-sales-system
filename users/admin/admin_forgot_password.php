<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// admin_forgot_password.php
// Admins can request a password reset by entering their email. A unique code is generated and emailed for reset.

session_start();
include_once(__DIR__ . '/../../config/db.connection.php');
include_once(__DIR__ . '/../../services/TwoFactorAuth.php');

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND role = 'admin'");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $resetCode = bin2hex(random_bytes(4)); // 8-char unique code
        $expires = date('Y-m-d H:i:s', time() + 900); // 15 min expiry
        // Store code in DB
        $stmt2 = $conn->prepare("INSERT INTO password_resets (user_id, code, expires_at) VALUES (?, ?, ?)");
        $stmt2->bind_param('iss', $row['user_id'], $resetCode, $expires);
        $stmt2->execute();
        // Email code
        $twoFA = new TwoFactorAuth();
        $subject = 'Admin Password Reset Code';
        $body = "Your password reset code is: <b>$resetCode</b><br>This code expires in 15 minutes.";
        if ($twoFA->sendOTP($email, $resetCode)) {
            $message = 'A reset code has been sent to your email.';
        } else {
            $error = 'Failed to send reset code. Please try again.';
        }
    } else {
        $error = 'No admin account found with that email.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Forgot Password</title>
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
    <?php if ($message): ?><div class="success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="POST">
        <label for="email">Admin Email:</label><br>
        <input type="email" name="email" id="email" required><br><br>
        <button type="submit">Send Reset Code</button>
    </form>
    <br>
    <a href="../../auth/login.php">Back to Login</a>
</div>
</body>
</html>
