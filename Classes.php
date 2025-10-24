<?php
// Classes.php - Main classes for the car sales system
require_once 'classAutoload.php';

class Database {
    private $connection;
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
        $this->connect();
    }
    
    private function connect() {
        try {
            $dsn = "mysql:host={$this->config['db_hostname']};dbname={$this->config['db_name']};charset=utf8mb4";
            $this->connection = new PDO($dsn, $this->config['db_user'], $this->config['db_pass']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

class User {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function createUser($email, $password, $name, $phone) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (email, password, name, phone, created_at) VALUES (?, ?, ?, ?, NOW())");
        return $stmt->execute([$email, $hashedPassword, $name, $phone]);
    }
    
    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}

class TwoFactorAuth {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function generateCode($userId) {
        $code = rand(100000, 999999);
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        $stmt = $this->db->prepare("INSERT INTO two_factor_codes (user_id, code, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $code, $expires]);
        
        return $code;
    }
    
    public function verifyCode($userId, $code) {
        $stmt = $this->db->prepare("SELECT * FROM two_factor_codes WHERE user_id = ? AND code = ? AND expires_at > NOW()");
        $stmt->execute([$userId, $code]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Delete used code
            $stmt = $this->db->prepare("DELETE FROM two_factor_codes WHERE user_id = ? AND code = ?");
            $stmt->execute([$userId, $code]);
            return true;
        }
        return false;
    }
    
    public function sendEmailCode($email, $code) {
        $subject = "Your 2FA Code - AutoDrive Car Sales";
        $message = "Your verification code is: " . $code . "\n\nThis code expires in 10 minutes.";
        $headers = "From: noreply@autodrive.com";
        
        return mail($email, $subject, $message, $headers);
    }
    
    public function sendSMSCode($phone, $code) {
        // For SMS, you would integrate with a service like Twilio
        // This is a placeholder implementation
        error_log("SMS Code for $phone: $code");
        return true;
    }
}
?>
