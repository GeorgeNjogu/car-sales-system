<?php
// Classes.php - Database, User and TwoFactorAuth classes
// Save this file as Classes.php (backup your old file first!)

/**
 * Optional: If you use composer and vendor/autoload.php is in project root,
 * you can require it here so PHPMailer and other libs load automatically.
 * But we also do a conditional require inside sendEmailCode() if needed.
 */
// if (file_exists(__DIR__ . '/vendor/autoload.php')) {
//     require_once __DIR__ . '/vendor/autoload.php';
// }

class Database {
    private $connection;
    private $config;

    public function __construct($config) {
        // Accept either nested config ['db'=>...] or flat config used previously.
        if (isset($config['db']) && is_array($config['db'])) {
            $this->config = $config['db'];
        } else {
            // fallback: config uses flat keys like db_hostname, db_user etc.
            $this->config = [
                'host' => $config['db_hostname'] ?? ($config['host'] ?? 'localhost'),
                'port' => $config['db_port'] ?? ($config['port'] ?? 3306),
                'name' => $config['db_name'] ?? ($config['name'] ?? ''),
                'user' => $config['db_user'] ?? ($config['user'] ?? 'root'),
                'pass' => $config['db_pass'] ?? ($config['pass'] ?? ''),
            ];
        }
        $this->connect();
    }

    private function connect() {
        try {
            $host = $this->config['host'] ?? '127.0.0.1';
            $port = $this->config['port'] ?? 3306;
            $dbname = $this->config['name'] ?? '';
            $user = $this->config['user'] ?? 'root';
            $pass = $this->config['pass'] ?? '';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            $this->connection = new PDO($dsn, $user, $pass);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            // In development show message; in production handle gracefully.
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

    public function createUser($email, $password, $name = '', $phone = '') {
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

// TwoFactorAuth class with PHPMailer-based sendEmailCode()
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
            $del = $this->db->prepare("DELETE FROM two_factor_codes WHERE user_id = ? AND code = ?");
            $del->execute([$userId, $code]);
            return true;
        }
        return false;
    }

    /**
     * Send 2FA code via email using PHPMailer.
     * It looks for config.php in the same folder as this Classes.php file.
     */
    public function sendEmailCode($email, $code) {
        // Load config - try a few common locations
        $conf = null;
        $pathsToTry = [
            __DIR__ . '/config.php',        // same folder as Classes.php
            __DIR__ . '/../config.php',     // parent folder
            __DIR__ . '/../../config.php'   // two levels up
        ];
        foreach ($pathsToTry as $p) {
            if (file_exists($p)) {
                $conf = include($p);
                break;
            }
        }

        // Fallback defaults if config not found
        $smtpHost = $conf['smtp_host'] ?? ($conf['smtp']['host'] ?? 'smtp.gmail.com');
        $smtpUser = $conf['smtp_username'] ?? ($conf['smtp']['user'] ?? '');
        $smtpPass = $conf['smtp_password'] ?? ($conf['smtp']['pass'] ?? '');
        $smtpPort = $conf['smtp_port'] ?? ($conf['smtp']['port'] ?? 465);
        $siteName = $conf['site_name'] ?? 'AutoDrive';

        // Ensure PHPMailer is available via composer if possible
        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            require_once __DIR__ . '/vendor/autoload.php';
        } else if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            require_once __DIR__ . '/../vendor/autoload.php';
        } // otherwise will try to use mail() fallback below

        // Use PHPMailer if class exists
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            try {
                // import classes
                // (use statements are optional here since we reference with fully-qualified names)
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = $smtpHost;
                $mail->SMTPAuth = true;
                $mail->Username = $smtpUser;
                $mail->Password = $smtpPass;
                // If your SMTP does not support SMTPS use 'tls' and port 587:
                // $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = $smtpPort;

                $mail->setFrom($smtpUser ?: 'noreply@' . ($_SERVER['SERVER_NAME'] ?? 'localhost'), $siteName);
                $mail->addAddress($email);
                $mail->isHTML(false);
                $mail->Subject = "Your {$siteName} verification code";
                $mail->Body = "Your verification code is: {$code}\nThis code expires in 10 minutes.";

                $mail->send();
                return true;
            } catch (Exception $e) {
                error_log("PHPMailer error: " . (isset($mail) ? $mail->ErrorInfo : $e->getMessage()));
                // fallback to PHP mail() if PHPMailer fails
            }
        }

        // Fallback: use PHP mail() — unreliable on many systems but better than nothing
        $subject = "Your {$siteName} verification code";
        $message = "Your verification code is: {$code}\nThis code expires in 10 minutes.";
        $headers = "From: noreply@" . ($_SERVER['SERVER_NAME'] ?? 'localhost');

        // Return boolean to indicate success or failure
        return @mail($email, $subject, $message, $headers);
    }

    public function sendSMSCode($phone, $code) {
        // Placeholder - integrate Twilio or other SMS provider
        error_log("SMS Code for $phone: $code");
        return true;
    }
}
