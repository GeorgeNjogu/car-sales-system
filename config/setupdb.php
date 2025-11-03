<?php
$servername = "localhost";
$username = "root"; 
$password = "1234";     
$dbname = "carsales";

// Create connection
$conn = new mysqli($servername, $username, $password, null, 3307);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo 'Connected to MySQL on port 3307<br>';
// Create Database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

$conn->select_db($dbname);

// USERS TABLE
$conn->query("CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'seller', 'buyer') NOT NULL DEFAULT 'buyer',
    status ENUM('active', 'suspended') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");
if ($conn->error) {
    die("Error creating users table: " . $conn->error);
}

// CARS TABLE
$conn->query("CREATE TABLE IF NOT EXISTS cars (
    car_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    make VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    mileage INT,
    transmission ENUM('manual','automatic'),
    fuel_type ENUM('petrol','diesel','electric','hybrid'),
    color VARCHAR(30),
    car_condition ENUM('new','used') DEFAULT 'used',
    description TEXT,
    status ENUM('pending','approved','sold','rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE
)");
if ($conn->error) {
    die("Error creating cars table: " . $conn->error);
}

// CAR IMAGES TABLE
$conn->query("CREATE TABLE IF NOT EXISTS car_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(car_id) ON DELETE CASCADE
)");
if ($conn->error) {
    die("Error creating car_images table: " . $conn->error);
}

// FAVORITES TABLE
$conn->query("CREATE TABLE IF NOT EXISTS favorites (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    car_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(user_id),
    FOREIGN KEY (car_id) REFERENCES cars(car_id)
)");
if ($conn->error) {
    die("Error creating favorites table: " . $conn->error);
}

// MESSAGES TABLE
$conn->query("CREATE TABLE IF NOT EXISTS messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    car_id INT,
    message_text TEXT NOT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (sender_id) REFERENCES users(user_id),
    FOREIGN KEY (receiver_id) REFERENCES users(user_id),
    FOREIGN KEY (car_id) REFERENCES cars(car_id)
)");
if ($conn->error)   {
    die("Error creating messages table: " . $conn->error);
}

echo "All tables created successfully.<br>";

// Create default admin
$adminCheck = $conn->query("SELECT * FROM users WHERE role='admin' LIMIT 1");
if ($adminCheck->num_rows == 0) {
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (name, email, password_hash, role)
                  VALUES ('System Admin', 'admin@cms.com', '$password', 'admin')");
    echo "Default admin created (email: admin@cms.com, password: admin123).<br>";
}

$conn->close();
?>
