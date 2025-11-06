<?php
require_once __DIR__ . '/../Classes.php';
require_once 'config.php';

$conf = include('config.php');
$db = new Database($conf);
$connection = $db->getConnection();

if ($_POST && isset($_POST['verification_code'])) {
    $twoFA = new TwoFactorAuth($connection);
    $userId = $_POST['user_id'];
    $code = $_POST['verification_code'];
    
    if ($twoFA->verifyCode($userId, $code)) {
        // 2FA successful, process the car purchase
        $customerName = $_POST['customer_name'];
        $customerEmail = $_POST['customer_email'];
        $customerPhone = $_POST['customer_phone'];
        $carModel = $_POST['car_model'];
        $purchaseDate = $_POST['purchase_date'];
        
        // Insert purchase record
        $stmt = $connection->prepare("INSERT INTO car_purchases (customer_name, customer_email, customer_phone, car_model, purchase_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$customerName, $customerEmail, $customerPhone, $carModel, $purchaseDate]);
        
        // Send confirmation email
        $subject = "Purchase Confirmation - AutoDrive Car Sales";
        $message = "
        Dear $customerName,
        
        Thank you for choosing AutoDrive Car Sales!
        
        Your car purchase has been successfully recorded and verified.
        
        Purchase Details:
        - Car Model: $carModel
        - Purchase Date: $purchaseDate
        - Contact Phone: $customerPhone
        
        Our sales team will contact you soon to finalize the delivery or pickup details.
        
        Best regards,
        AutoDrive Car Sales Team
        ";
        
        $headers = "From: sales@autodrive.com";
        mail($customerEmail, $subject, $message, $headers);
        
        echo '<div style="background: #d4edda; color: #155724; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: center;">';
        echo '<h2>✅ Purchase Confirmed!</h2>';
        echo '<p>Your car purchase has been successfully verified and recorded.</p>';
        echo '<p>A confirmation email has been sent to: ' . htmlspecialchars($customerEmail) . '</p>';
        echo '<p><a href="index.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Return to Home</a></p>';
        echo '</div>';
        
    } else {
        echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;">';
        echo '<h3>❌ Verification Failed</h3>';
        echo '<p>Invalid or expired verification code. Please try again.</p>';
        echo '<p><a href="index.php" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Return to Home</a></p>';
        echo '</div>';
    }
} else {
    echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;">';
    echo '<h3>Error</h3>';
    echo '<p>Invalid request. Please try again.</p>';
    echo '<p><a href="index.php" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Return to Home</a></p>';
    echo '</div>';
}
?>
