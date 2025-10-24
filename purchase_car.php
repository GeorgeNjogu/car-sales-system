<?php
require_once 'Classes.php';
require_once 'config.php';

$conf = include('config.php');
$db = new Database($conf);
$connection = $db->getConnection();

// Create tables if they don't exist
$connection->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

$connection->exec("
    CREATE TABLE IF NOT EXISTS two_factor_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        code VARCHAR(6) NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )
");

$connection->exec("
    CREATE TABLE IF NOT EXISTS car_purchases (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(255) NOT NULL,
        customer_email VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(20),
        car_model VARCHAR(255) NOT NULL,
        purchase_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

if ($_POST) {
    $user = new User($connection);
    $twoFA = new TwoFactorAuth($connection);
    
    $customerName = $_POST['customer_name'];
    $customerEmail = $_POST['customer_email'];
    $customerPhone = $_POST['customer_phone'];
    $carModel = $_POST['car_model'];
    $purchaseDate = $_POST['purchase_date'];
    
    // Check if user exists, if not create one
    $existingUser = $user->getUserByEmail($customerEmail);
    if (!$existingUser) {
        // Create new user with temporary password
        $tempPassword = bin2hex(random_bytes(8));
        $user->createUser($customerEmail, $tempPassword, $customerName, $customerPhone);
        $existingUser = $user->getUserByEmail($customerEmail);
    }
    
    // Generate 2FA code
    $code = $twoFA->generateCode($existingUser['id']);
    
    // Send 2FA code via email
    if ($twoFA->sendEmailCode($customerEmail, $code)) {
        echo '<div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;">';
        echo '<h3>2FA Verification Required</h3>';
        echo '<p>A verification code has been sent to your email: ' . htmlspecialchars($customerEmail) . '</p>';
        echo '<form method="POST" action="verify_2fa.php">';
        echo '<input type="hidden" name="user_id" value="' . $existingUser['id'] . '">';
        echo '<input type="hidden" name="customer_name" value="' . htmlspecialchars($customerName) . '">';
        echo '<input type="hidden" name="customer_email" value="' . htmlspecialchars($customerEmail) . '">';
        echo '<input type="hidden" name="customer_phone" value="' . htmlspecialchars($customerPhone) . '">';
        echo '<input type="hidden" name="car_model" value="' . htmlspecialchars($carModel) . '">';
        echo '<input type="hidden" name="purchase_date" value="' . htmlspecialchars($purchaseDate) . '">';
        echo '<input type="text" name="verification_code" placeholder="Enter 6-digit code" maxlength="6" required style="width: 200px; padding: 10px; margin: 10px 0;">';
        echo '<br><button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Verify Code</button>';
        echo '</form>';
        echo '</div>';
    } else {
        echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;">';
        echo '<h3>Error</h3>';
        echo '<p>Failed to send verification code. Please try again.</p>';
        echo '</div>';
    }
}
?>
