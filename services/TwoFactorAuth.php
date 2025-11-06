<?php
// services/TwoFactorAuth.php

require_once __DIR__ . '/../vendor/autoload.php'; // For external libraries if needed

class TwoFactorAuth {
    // Generate a numeric OTP code (default 6 digits)
    public function generateOTP($length = 6) {
        $min = pow(10, $length - 1);
        $max = pow(10, $length) - 1;
        return strval(random_int($min, $max));
    }

    // Store OTP in session or database with expiry (example uses session)
    public function sendOTP($email, $otp) {
        require __DIR__ . '/../config/config.php';
        $subject = 'Your One-Time Password (OTP)';
        $body = "Your OTP code is: $otp<br>This code will expire in 5 minutes.";

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $conf['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $conf['smtp_username'];
            $mail->Password = str_replace(' ', '', $conf['smtp_password']);
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $conf['smtp_port'];
            $mail->setFrom($conf['smtp_username'], $conf['site_name']);
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log('OTP Email Error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    // Verify OTP (example: check against session value and expiry)
    public function verifyOTP($inputOtp) {
        if (!isset($_SESSION['otp_code']) || !isset($_SESSION['otp_expires'])) {
            return false;
        }
        if (time() > $_SESSION['otp_expires']) {
            return false; // Expired
        }
        return hash_equals($_SESSION['otp_code'], $inputOtp);
    }
}
