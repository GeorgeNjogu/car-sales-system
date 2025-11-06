<?php
// 2FA Setup Script - Run this once to add 2FA support to existing database
include_once("db.connection.php");


// Add 2FA columns to users table only if they don't exist
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'two_factor_secret'");
if ($result && $result->num_rows == 0) {
    $sql = "ALTER TABLE users 
            ADD COLUMN two_factor_secret VARCHAR(32) DEFAULT NULL,
            ADD COLUMN two_factor_enabled BOOLEAN DEFAULT FALSE,
            ADD COLUMN backup_codes TEXT DEFAULT NULL";
    if ($conn->query($sql) === TRUE) {
        echo "2FA columns added successfully to users table.<br>";
    } else {
        echo "Error adding 2FA columns: " . $conn->error . "<br>";
    }
} else {
    echo "2FA columns already exist in users table.<br>";
}

// Create 2FA sessions table for temporary storage
$sql = "CREATE TABLE IF NOT EXISTS two_factor_sessions (
    session_id VARCHAR(64) PRIMARY KEY,
    user_id INT NOT NULL,
    temp_secret VARCHAR(32) NOT NULL,
    qr_code_data TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME DEFAULT (CURRENT_TIMESTAMP + INTERVAL 10 MINUTE),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "2FA sessions table created successfully.<br>";
} else {
    echo "Error creating 2FA sessions table: " . $conn->error . "<br>";
}

// Create purchase confirmations table
$sql = "CREATE TABLE IF NOT EXISTS purchase_confirmations (
    confirmation_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    car_id INT NOT NULL,
    purchase_price DECIMAL(10,2) NOT NULL,
    confirmation_code VARCHAR(10) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    confirmed_at DATETIME NULL,
    FOREIGN KEY (buyer_id) REFERENCES users(user_id),
    FOREIGN KEY (seller_id) REFERENCES users(user_id),
    FOREIGN KEY (car_id) REFERENCES cars(car_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Purchase confirmations table created successfully.<br>";
} else {
    echo "Error creating purchase confirmations table: " . $conn->error . "<br>";
}

$conn->close();
echo "2FA setup completed successfully!";
?>
