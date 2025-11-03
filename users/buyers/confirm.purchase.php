<?php
session_start();
include_once(__DIR__ . "/../../config/db.connection.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header('Location: ../../auth/login.php');
    exit;
}

$buyerId = $_SESSION['user_id'];
$carId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

// Get car details
$carQuery = "SELECT c.*, u.name as seller_name, u.email as seller_email, u.user_id as seller_id 
             FROM cars c 
             JOIN users u ON c.seller_id = u.user_id 
             WHERE c.car_id = ? AND c.status = 'approved'";

$stmt = $conn->prepare($carQuery);
$stmt->bind_param("i", $carId);
$stmt->execute();
$car = $stmt->get_result()->fetch_assoc();

if (!$car) {
    die("Car not found or not available for purchase.");
}

// Handle purchase confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_purchase'])) {
    $confirmationCode = strtoupper(substr(md5(uniqid()), 0, 8));
    $purchasePrice = (float)$_POST['purchase_price'];
    
    // Create purchase confirmation
    $stmt = $conn->prepare("INSERT INTO purchase_confirmations (buyer_id, seller_id, car_id, purchase_price, confirmation_code) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiids", $buyerId, $car['seller_id'], $carId, $purchasePrice, $confirmationCode);
    
    if ($stmt->execute()) {
        // Send confirmation message to seller
        $messageText = "Purchase Confirmation: Buyer has confirmed purchase of {$car['make']} {$car['model']} for $" . number_format($purchasePrice) . ". Confirmation Code: {$confirmationCode}. Please confirm the sale.";
        
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, car_id, message_text) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $buyerId, $car['seller_id'], $carId, $messageText);
        $stmt->execute();
        
        $message = "Purchase confirmation sent! Confirmation Code: {$confirmationCode}. The seller will be notified and can confirm the sale.";
    } else {
        $error = "Failed to create purchase confirmation.";
    }
}

// Send email notification function
function sendPurchaseNotification($sellerEmail, $sellerName, $carDetails, $confirmationCode, $purchasePrice) {
    $subject = "Purchase Confirmation - Car Management System";
    $message = "
    <html>
    <head>
        <title>Purchase Confirmation</title>
    </head>
    <body>
        <h2>Purchase Confirmation</h2>
        <p>Hello {$sellerName},</p>
        <p>A buyer has confirmed their purchase of your car:</p>
        
        <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
            <h3>{$carDetails['make']} {$carDetails['model']} ({$carDetails['year']})</h3>
            <p><strong>Purchase Price:</strong> $" . number_format($purchasePrice) . "</p>
            <p><strong>Confirmation Code:</strong> {$confirmationCode}</p>
        </div>
        
        <p>Please log in to your account to confirm this sale and complete the transaction.</p>
        
        <p>Best regards,<br>Car Management System Team</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@carmanagement.com" . "\r\n";
    
    return mail($sellerEmail, $subject, $message, $headers);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Purchase - Car Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .car-details {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .car-image {
            height: 300px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .car-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }
        .car-price {
            font-size: 32px;
            font-weight: bold;
            color: #007BFF;
            margin-bottom: 20px;
        }
        .car-specs {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .spec-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        .spec-label {
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
        }
        .spec-value {
            color: #333;
            font-size: 16px;
        }
        .seller-info {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .purchase-form {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .price-note {
            background: #fff3cd;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
        }
        button {
            background: #28a745;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background: #1e7e34;
        }
        .message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
        <h1>Confirm Purchase</h1>
        <div class="back-link">
            <a href="browse_cars.php">← Back to Browse Cars</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

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
            <div style="margin-top: 20px;">
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($car['description'])); ?></p>
            </div>
        <?php endif; ?>
        
        <div class="seller-info">
            <h3>Seller Information</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($car['seller_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($car['seller_email']); ?></p>
        </div>
    </div>

    <div class="purchase-form">
        <h2>Confirm Your Purchase</h2>
        <div class="price-note">
            <strong>Note:</strong> You can negotiate the final price with the seller. The listed price is the asking price.
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="purchase_price">Final Purchase Price ($)</label>
                <input type="number" name="purchase_price" id="purchase_price" 
                       value="<?php echo $car['price']; ?>" 
                       min="1" step="0.01" required>
            </div>
            
            <button type="submit" name="confirm_purchase" onclick="return confirm('Are you sure you want to confirm this purchase? This will notify the seller.')">
                Confirm Purchase
            </button>
        </form>
    </div>
</div>

</body>
</html>
