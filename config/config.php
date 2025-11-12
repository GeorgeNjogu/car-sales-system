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
$conf['db_name'] = $_ENV['DB_NAME'] ?? "carsales";

// Email configuration - Use environment variables for security

$conf['smtp_host'] = "smtp.gmail.com";
$conf['smtp_username'] = "lavendagrace80@gmail.com";
$conf['smtp_password'] = "ifnsqtpjzilhsaso"; //replace with pass. when testing this
$conf['smtp_port'] = 465;


return $conf;
?>
