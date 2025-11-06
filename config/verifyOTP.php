<?php
session_start();
include_once(__DIR__ . '/db.connection.php');
include_once(__DIR__ . '/../services/TwoFactorAuth.php');

$error = '';
$message = '';

if (!isset($_SESSION['user_id']) || !isset($_GET['session_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$sessionId = $_GET['session_id'];
$twoFA = new TwoFactorAuth($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp']);
    // Use otp_codes table for verification
    $stmt = $conn->prepare("SELECT otp_id, otp_code, expires_at, used FROM otp_codes WHERE user_id = ? AND used = 0 ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if ($row['otp_code'] === $otp && strtotime($row['expires_at']) > time()) {
            // Mark OTP as used
            $update = $conn->prepare("UPDATE otp_codes SET used = 1 WHERE otp_id = ?");
            $update->bind_param('i', $row['otp_id']);
            $update->execute();
            $message = '2FA verification successful! Redirecting to your dashboard...';
            $role = $_SESSION['role'];
            echo "<script>setTimeout(function(){ window.location.href = '../users/" . $role . "s/dashboard.php'; }, 2000);</script>";
        } else {
            $error = 'Invalid or expired OTP. Please try again.';
        }
    } else {
        $error = 'No valid OTP found. Please request a new code.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP - 2FA</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 0; }
        .container { max-width: 400px; margin: 60px auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #333; }
        input, button { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        button { background: #007BFF; color: white; border: none; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
        .error { color: #dc3545; text-align: center; margin-bottom: 15px; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 6px; }
        .success { color: #28a745; text-align: center; margin-bottom: 15px; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px; }
    </style>
</head>
<body>
<div class="container">
    <h2>2FA Verification</h2>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($message): ?>
        <div class="success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="POST">
        <label for="otp">Enter the OTP sent to your email:</label>
        <input type="text" name="otp" id="otp" maxlength="6" required autofocus>
        <button type="submit">Verify</button>
    </form>
    <form method="POST" style="margin-top:10px;">
        <input type="hidden" name="resend_otp" value="1">
        <button type="submit">Resend OTP</button>
    </form>
    <?php
    // Handle resend OTP
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_otp'])) {
        $sessionId = $twoFA->sendOTP($userId);
        echo '<div class="success">A new OTP has been sent to your email.</div>';
    }
    ?>
</div>
</body>
</html>
