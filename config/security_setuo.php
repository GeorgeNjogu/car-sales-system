<?php
session_start();
include_once(__DIR__ . "/../config/db.connection.php");
include_once(__DIR__ . "/../services/TwoFactorAuth.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$twoFA = new TwoFactorAuth($conn);
$userId = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle 2FA enable/disable
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['enable_2fa'])) {
        if ($twoFA->enable2FA($userId)) {
            $message = "Two-factor authentication has been enabled successfully!";
        } else {
            $error = "Failed to enable two-factor authentication.";
        }
    } elseif (isset($_POST['disable_2fa'])) {
        if ($twoFA->disable2FA($userId)) {
            $message = "Two-factor authentication has been disabled.";
        } else {
            $error = "Failed to disable two-factor authentication.";
        }
    }
}

$is2FAEnabled = $twoFA->is2FAEnabled($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Settings - Car Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
        }
        .security-section {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .security-section h3 {
            margin-top: 0;
            color: #007BFF;
        }
        .status {
            padding: 10px;
            border-radius: 6px;
            margin: 10px 0;
        }
        .status.enabled {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.disabled {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        button {
            background: #007BFF;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background: #0056b3;
        }
        button.danger {
            background: #dc3545;
        }
        button.danger:hover {
            background: #c82333;
        }
        .message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info-box {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .back-link {
            margin-top: 20px;
        }
        .back-link a {
            color: #007BFF;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Security Settings</h1>
    
    <?php if ($message): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="security-section">
        <h3>Two-Factor Authentication (2FA)</h3>
        
        <?php if ($is2FAEnabled): ?>
            <div class="status enabled">
                <strong>✓ Enabled</strong> - Your account is protected with two-factor authentication
            </div>
            <div class="info-box">
                <p><strong>How it works:</strong></p>
                <ul>
                    <li>When you log in, you'll receive a 6-digit code via email</li>
                    <li>Enter this code to complete your login</li>
                    <li>This adds an extra layer of security to your account</li>
                </ul>
            </div>
            <form method="POST" onsubmit="return confirm('Are you sure you want to disable two-factor authentication? This will make your account less secure.')">
                <button type="submit" name="disable_2fa" class="danger">Disable 2FA</button>
            </form>
        <?php else: ?>
            <div class="status disabled">
                <strong>✗ Disabled</strong> - Your account is not protected with two-factor authentication
            </div>
            <div class="info-box">
                <p><strong>Why enable 2FA?</strong></p>
                <ul>
                    <li>Protects your account even if someone knows your password</li>
                    <li>You'll receive a verification code via email when logging in</li>
                    <li>Industry standard for account security</li>
                </ul>
            </div>
            <form method="POST">
                <button type="submit" name="enable_2fa">Enable 2FA</button>
            </form>
        <?php endif; ?>
    </div>
    
    <div class="back-link">
        <a href="javascript:history.back()">← Back to Previous Page</a>
    </div>
</div>

</body>
</html>
