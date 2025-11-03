<?php
session_start();
include_once(__DIR__ . "/../../config/db.connection.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header('Location: ../../auth/login.php');
    exit;
}

$car_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($car_id <= 0) {
    die("Invalid car ID");
}

$result = $conn->prepare("SELECT c.*, u.name AS seller_name, u.email AS seller_email, u.user_id as seller_id 
                         FROM cars c
                         JOIN users u ON c.seller_id = u.user_id
                         WHERE c.car_id = ? AND c.status = 'approved'");

$result->bind_param("i", $car_id);
$result->execute();
$car = $result->get_result()->fetch_assoc();

if (!$car) {
    die("Car not found or not available");
}

// Check if car is already in favorites
$buyerId = $_SESSION['user_id'];
$favoriteCheck = $conn->prepare("SELECT * FROM favorites WHERE buyer_id = ? AND car_id = ?");
$favoriteCheck->bind_param("ii", $buyerId, $car_id);
$favoriteCheck->execute();
$isFavorited = $favoriteCheck->get_result()->num_rows > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?> - Car Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .nav-links {
            margin-bottom: 20px;
        }
        .nav-links a {
            color: #007BFF;
            text-decoration: none;
            margin-right: 20px;
        }
        .car-details {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .car-image {
            height: 400px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            border-radius: 8px;
            margin-bottom: 30px;
            font-size: 18px;
        }
        .car-title {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .car-price {
            font-size: 36px;
            font-weight: bold;
            color: #007BFF;
            margin-bottom: 30px;
        }
        .car-specs {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .spec-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007BFF;
        }
        .spec-label {
            font-weight: bold;
            color: #666;
            margin-bottom: 8px;
            font-size: 14px;
            text-transform: uppercase;
        }
        .spec-value {
            color: #333;
            font-size: 18px;
        }
        .description {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .description h3 {
            margin-top: 0;
            color: #333;
        }
        .seller-info {
            background: #e7f3ff;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .seller-info h3 {
            margin-top: 0;
            color: #007BFF;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .action-buttons a {
            flex: 1;
            text-align: center;
            padding: 15px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            min-width: 150px;
        }
        .contact-btn {
            background: #28a745;
            color: white;
        }
        .contact-btn:hover {
            background: #1e7e34;
        }
        .favorite-btn {
            background: #ffc107;
            color: #212529;
        }
        .favorite-btn:hover {
            background: #e0a800;
        }
        .favorite-btn.added {
            background: #dc3545;
            color: white;
        }
        .favorite-btn.added:hover {
            background: #c82333;
        }
        .purchase-btn {
            background: #007BFF;
            color: white;
        }
        .purchase-btn:hover {
            background: #0056b3;
        }
        .back-link {
            margin-bottom: 20px;
        }
        .back-link a {
            color: #007BFF;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Car Details</h1>
        <div class="nav-links">
            <a href="browse_cars.php">Browse Cars</a>
            <a href="my_favorites.php">My Favorites</a>
            <a href="my_messages.php">Messages</a>
            <a href="../security_settings.php">Security Settings</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>

    <div class="back-link">
        <a href="browse_cars.php">← Back to Browse Cars</a>
    </div>

    <div class="car-details">
        <div class="car-image">
            <span>No Image Available</span>
        </div>
        
        <div class="car-title"><?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?></div>
        <div class="car-price">$<?php echo number_format($car['price']); ?></div>
        
        <div class="car-specs">
            <div class="spec-item">
                <div class="spec-label">Year</div>
                <div class="spec-value"><?php echo $car['year']; ?></div>
            </div>
            <div class="spec-item">
                <div class="spec-label">Mileage</div>
                <div class="spec-value"><?php echo $car['mileage'] ? number_format($car['mileage']) . ' km' : 'N/A'; ?></div>
            </div>
            <div class="spec-item">
                <div class="spec-label">Fuel Type</div>
                <div class="spec-value"><?php echo ucfirst($car['fuel_type']); ?></div>
            </div>
            <div class="spec-item">
                <div class="spec-label">Transmission</div>
                <div class="spec-value"><?php echo ucfirst($car['transmission']); ?></div>
            </div>
            <div class="spec-item">
                <div class="spec-label">Condition</div>
                <div class="spec-value"><?php echo ucfirst($car['car_condition']); ?></div>
            </div>
            <div class="spec-item">
                <div class="spec-label">Color</div>
                <div class="spec-value"><?php echo ucfirst($car['color']); ?></div>
            </div>
        </div>
        
        <?php if ($car['description']): ?>
            <div class="description">
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($car['description'])); ?></p>
            </div>
        <?php endif; ?>
        
        <div class="seller-info">
            <h3>Seller Information</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($car['seller_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($car['seller_email']); ?></p>
        </div>
        
        <div class="action-buttons">
            <a href="contact_seller.php?id=<?php echo $car['car_id']; ?>" class="contact-btn">
                Contact Seller
            </a>
            
            <?php if ($isFavorited): ?>
                <a href="my_favorites.php?remove=<?php echo $car['car_id']; ?>" class="favorite-btn added" onclick="return confirm('Remove from favorites?')">
                    Remove from Favorites
                </a>
            <?php else: ?>
                <a href="my_favorites.php?add=<?php echo $car['car_id']; ?>" class="favorite-btn">
                    Add to Favorites
                </a>
            <?php endif; ?>
            
            <a href="confirm_purchase.php?id=<?php echo $car['car_id']; ?>" class="purchase-btn">
                Confirm Purchase
            </a>
        </div>
    </div>
</div>

</body>
</html>
