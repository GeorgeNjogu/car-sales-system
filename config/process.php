<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

$conf = include('config.php');

if ($_POST) {
    // Expecting: buyer_name, buyer_email, car_id, car_model, car_price
    $buyerName = $_POST['buyer_name'] ?? '';
    $buyerEmail = $_POST['buyer_email'] ?? '';
    $carId = $_POST['car_id'] ?? '';
    $carModel = $_POST['car_model'] ?? '';
    $carPrice = $_POST['car_price'] ?? '';

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $conf['smtp.gmail.com'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $conf['lavendagrace8o@gmailcom'];
        $mail->Password   = $conf['ifnsqtpjzilhsaso'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $conf['465'];

        // Recipients - Send to BUYER
        $mail->setFrom($conf['lavendagrace8o@gmailcom'], $conf['site_name']);
        $mail->addAddress($buyerEmail, $buyerName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Car Purchase Confirmation - ' . $conf['site_name'];

        $mail->Body = "
        Dear $buyerName,<br><br>
        Thank you for your purchase!<br><br>
        <strong>Car Details:</strong><br>
        Model: $carModel<br>
        Price: $carPrice<br>
        Car ID: $carId<br><br>
        Our team will contact you soon to finalize the transaction and arrange for delivery or pickup.<br><br>
        Regards,<br>
        " . $conf['site_name'] . " Team
        ";

        $mail->send();
        // Redirect user based on their role after confirmation
        session_start();
        if (isset($_SESSION['role'])) {
            switch ($_SESSION['role']) {
                case 'admin':
                    header('Location: ../users/admin/manage_users.php');
                    exit;
                case 'seller':
                    header('Location: ../users/sellers/my_cars.php');
                    exit;
                case 'buyer':
                    header('Location: ../users/buyers/browse_cars.php');
                    exit;
                default:
                    header('Location: ../dashboard.php');
                    exit;
            }
        } else {
            // If no role, go to dashboard
            header('Location: ../dashboard.php');
            exit;
        }
    } catch (Exception $e) {
        echo "Error: {$mail->ErrorInfo}";
    }
}
?>