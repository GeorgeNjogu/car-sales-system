<?php
session_start();
include_once("../config/db.connection.php");
include_once(__DIR__ . '/../services/TwoFactorAuth.php');

$twoFA = new TwoFactorAuth();
$error = '';
$showOTPForm = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        // Initial login
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Send OTP and show OTP form
            $otp = $twoFA->generateOTP();
            $_SESSION['otp_code'] = $otp;
            $_SESSION['otp_expires'] = time() + 300; // 5 minutes expiry
            $_SESSION['temp_user_id'] = $user['user_id'];
            $_SESSION['temp_role'] = $user['role'];
            if ($twoFA->sendOTP($user['email'], $otp)) {
                $showOTPForm = true;
            } else {
                $error = "Failed to send verification code. Please try again.";
            }
        } else {
            $error = "Invalid credentials.";
        }
    } elseif (isset($_POST['otp_code'])) {
        // OTP verification
        $otpCode = $_POST['otp_code'];
        if ($twoFA->verifyOTP($otpCode)) {
            // OTP verified, complete login
            $_SESSION['user_id'] = $_SESSION['temp_user_id'];
            $_SESSION['role'] = $_SESSION['temp_role'];
            // Clean up temp session
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_role']);
            unset($_SESSION['otp_code']);
            unset($_SESSION['otp_expires']);
            redirectUser($_SESSION['role']);
        } else {
            $error = "Invalid or expired verification code.";
            $showOTPForm = true;
        }
    }
}

function redirectUser($role) {
    switch ($role) {
        case 'admin':
            header("Location: ../users/admin/manage_users.php");
            break;
        case 'seller':
            header("Location: ../users/sellers/my_cars.php");
            break;
        case 'buyer':
            header("Location: ../users/buyers/browse_cars.php");
            break;
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Car Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 350px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        input, button {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
        }
        button {
            background: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0056b3;
        }
        .error {
            color: #dc3545;
            text-align: center;
            margin-bottom: 15px;
        }
        .success {
            color: #28a745;
            text-align: center;
            margin-bottom: 15px;
        }
        .otp-info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
        .back-link a {
            color: #007BFF;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($showOTPForm): ?>
        <h2>Enter Verification Code</h2>
        <div class="otp-info">
            <p>We've sent a 6-digit verification code to your email address.</p>
            <p>Please check your email and enter the code below.</p>
        </div>
        
        <form method="POST">
            <input type="text" name="otp_code" placeholder="Enter 6-digit code" maxlength="6" required><br>
            <button type="submit">Verify Code</button>
        </form>
        
        <div class="back-link">
            <a href="login.php">Back to Login</a>
        </div>
    <?php else: ?>
        <h2>Login</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
        
        <div class="back-link">
            <a href="../index.php">Back to Home</a> | 
            <a href="register.php">Register</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
