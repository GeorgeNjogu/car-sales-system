<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
$conf = include(__DIR__ . '/config/config.php');


if ($_POST) {
    $customerName = $_POST['customer_name'];
    $customerEmail = $_POST['customer_email'];
    $customerPhone = $_POST['customer_phone'];
    $carModel = $_POST['car_model'];
    $purchaseDate = $_POST['purchase_date'];
    
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $conf['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $conf['smtp_username'];
        $mail->Password   = $conf['smtp_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $conf['smtp_port'];

        // Email details
        $mail->setFrom($conf['smtp_username'], $conf['site_name']);
        $mail->addAddress($customerEmail, $customerName);
        $mail->isHTML(true);
        $mail->Subject = 'Purchase Confirmation - ' . $conf['site_name'];

        // Email body content
        $mail->Body = "
        Dear $customerName,<br><br>
        Thank you for choosing " . $conf['site_name'] . "!<br><br>
        Your car purchase has been successfully recorded.<br><br>
        <strong>Purchase Details:</strong><br>
        Car Model: $carModel<br>
        Purchase Date: $purchaseDate<br>
        Contact Phone: $customerPhone<br><br>
        Our sales team will contact you soon to finalize the delivery or pickup details.<br><br>
        Best regards,<br>
        " . $conf['site_name'] . " Team
        ";

        $mail->send();
        echo 'Purchase confirmation sent to ' . $customerEmail;
        
    } catch (Exception $e) {
        echo "Error: {$mail->ErrorInfo}";
    }
}
?>
