<?php
// Site configuration - Car Sales Management System
$conf['site_name'] = "AutoDrive Car Sales";
$conf['site_email'] = "sales@autodrive.com";
$conf['site_url'] = "http://localhost/autodrive";

// Site language
$conf['language'] = "en";

// Database configuration - Use environment variables for security
$conf['db_type'] = "pdo";
$conf['db_hostname'] = $_ENV['DB_HOST'] ?? "localhost";
$conf['db_user'] = $_ENV['DB_USER'] ?? "root";
$conf['db_pass'] = $_ENV['DB_PASS'] ?? "1234";
$conf['db_name'] = $_ENV['DB_NAME'] ?? "car_sales";

// Email configuration - Use environment variables for security
$conf['smtp_host'] = $_ENV['SMTP_HOST'] ?? "smtp.gmail.com";
$conf['smtp_username'] = $_ENV['SMTP_USER'] ?? "lavendagrace80@gmail.com";
$conf['smtp_password'] = $_ENV['SMTP_PASS'] ?? "";
$conf['smtp_port'] = $_ENV['SMTP_PORT'] ?? 465;

return $conf;
?>
