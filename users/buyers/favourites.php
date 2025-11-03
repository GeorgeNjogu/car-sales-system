<?php
session_start();
include_once(__DIR__ . "/../../config/db.connection.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header('Location: ../../auth/login.php');
    exit;
}

$buyerId = $_SESSION['user_id'];

// Handle add/remove favorite
if (isset($_GET['add'])) {
    $carId = (int)$_GET['add'];
    
    // Check if already favorited
    $check = $conn->prepare("SELECT * FROM favorites WHERE buyer_id = ? AND car_id = ?");
    $check->bind_param("ii", $buyerId, $carId);
    $check->execute();
    
    if ($check->get_result()->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO favorites (buyer_id, car_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $buyerId, $carId);
        $stmt->execute();
    }
    header("Location: my_favorites.php");
    exit;
}

if (isset($_GET['remove'])) {
    $carId = (int)$_GET['remove'];
    $stmt = $conn->prepare("DELETE FROM favorites WHERE buyer_id = ? AND car_id = ?");
    $stmt->bind_param("ii", $buyerId, $carId);
    $stmt->execute();
    header("Location: my_favorites.php");
    exit;
}

// Get user's favorites
$query = "SELECT c.*, u.name as seller_name, f.created_at as favorited_at 
          FROM favorites f 
          JOIN cars c ON f.car_id = c.car_id 
          JOIN users u ON c.seller_id = u.user_id 
          WHERE f.buyer_id = ? AND c.status = 'approved'
          ORDER BY f.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $buyerId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites - Car Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
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
        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .car-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.2s;
        }
        .car-card:hover {
            transform: translateY(-5px);
        }
        .car-image {
            height: 200px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
        .car-info {
            padding: 20px;
        }
        .car-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .car-price {
            font-size: 24px;
            font-weight: bold;
            color: #007BFF;
            margin-bottom: 10px;
        }
        .car-details {
            color: #666;
            margin-bottom: 15px;
        }
        .car-details div {
            margin-bottom: 5px;
        }
        .favorited-date {
            font-size: 12px;
            color: #999;
            margin-bottom: 15px;
        }
        .car-actions {
            display: flex;
            gap: 10px;
        }
        .car-actions a {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }
        .view-btn {
            background: #007BFF;
            color: white;
        }
        .view-btn:hover {
            background: #0056b3;
        }
        .remove-btn {
            background: #dc3545;
            color: white;
        }
        .remove-btn:hover {
            background: #c82333;
        }
        .contact-btn {
            background: #28a745;
            color: white;
        }
        .contact-btn:hover {
            background: #1e7e34;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .empty-state h3 {
            color: #666;
            margin-bottom: 15px;
        }
        .empty-state p {
            color: #999;
            margin-bottom: 20px;
        }
        .empty-state a {
            background: #007BFF;
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
        }
        .empty-state a:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>My Favorite Cars</h1>
        <div class="nav-links">
            <a href="browse_cars.php">Browse Cars</a>
            <a href="my_favorites.php">My Favorites</a>
            <a href="my_messages.php">Messages</a>
            <a href="../security_settings.php">Security Settings</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>

    <?php if ($result->num_rows == 0): ?>
        <div class="empty-state">
            <h3>No Favorite Cars Yet</h3>
            <p>You haven't added any cars to your favorites. Start browsing to find cars you love!</p>
            <a href="browse_cars.php">Browse Cars</a>
        </div>
    <?php else: ?>
        <div class="favorites-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="car-card">
                    <div class="car-image">
                        <span>No Image Available</span>
                    </div>
                    <div class="car-info">
                        <div class="car-title"><?php echo htmlspecialchars($row['make'] . ' ' . $row['model']); ?></div>
                        <div class="car-price">$<?php echo number_format($row['price']); ?></div>
                        <div class="car-details">
                            <div><strong>Year:</strong> <?php echo $row['year']; ?></div>
                            <div><strong>Mileage:</strong> <?php echo $row['mileage'] ? number_format($row['mileage']) . ' km' : 'N/A'; ?></div>
                            <div><strong>Fuel:</strong> <?php echo ucfirst($row['fuel_type']); ?></div>
                            <div><strong>Transmission:</strong> <?php echo ucfirst($row['transmission']); ?></div>
                            <div><strong>Condition:</strong> <?php echo ucfirst($row['car_condition']); ?></div>
                            <div><strong>Seller:</strong> <?php echo htmlspecialchars($row['seller_name']); ?></div>
                        </div>
                        <div class="favorited-date">
                            Added to favorites: <?php echo date('M j, Y', strtotime($row['favorited_at'])); ?>
                        </div>
                        <div class="car-actions">
                            <a href="view.car.php?id=<?php echo $row['car_id']; ?>" class="view-btn">View Details</a>
                            <a href="contact_seller.php?id=<?php echo $row['car_id']; ?>" class="contact-btn">Contact Seller</a>
                            <a href="my_favorites.php?remove=<?php echo $row['car_id']; ?>" class="remove-btn" onclick="return confirm('Remove from favorites?')">Remove</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
