<?php
session_start();
include_once(__DIR__ . "/../../config/db.connection.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header('Location: ../../auth/login.php');
    exit;
}

$buyerId = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $sellerId = (int)$_POST['seller_id'];
    $carId = (int)$_POST['car_id'];
    $messageText = trim($_POST['message_text']);
    
    if (!empty($messageText)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, car_id, message_text) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $buyerId, $sellerId, $carId, $messageText);
        
        if ($stmt->execute()) {
            $message = "Message sent successfully!";
        } else {
            $error = "Failed to send message.";
        }
    } else {
        $error = "Please enter a message.";
    }
}

// Get conversations (messages where user is sender or receiver)
$query = "SELECT DISTINCT 
            CASE 
                WHEN sender_id = ? THEN receiver_id 
                ELSE sender_id 
            END as other_user_id,
            CASE 
                WHEN sender_id = ? THEN 'sent' 
                ELSE 'received' 
            END as direction,
            m.car_id,
            c.make, c.model, c.price,
            u.name as other_user_name, u.email as other_user_email,
            MAX(m.sent_at) as last_message_time,
            COUNT(CASE WHEN m.receiver_id = ? AND m.is_read = 0 THEN 1 END) as unread_count
          FROM messages m
          JOIN users u ON (CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END) = u.user_id
          JOIN cars c ON m.car_id = c.car_id
          WHERE m.sender_id = ? OR m.receiver_id = ?
          GROUP BY other_user_id, m.car_id
          ORDER BY last_message_time DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("iiiiii", $buyerId, $buyerId, $buyerId, $buyerId, $buyerId, $buyerId);
$stmt->execute();
$conversations = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Messages - Car Management System</title>
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
        .conversations-list {
            background: white;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .conversation-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s;
        }
        .conversation-item:hover {
            background: #f8f9fa;
        }
        .conversation-item:last-child {
            border-bottom: none;
        }
        .conversation-info {
            flex: 1;
        }
        .conversation-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .conversation-details {
            color: #666;
            font-size: 14px;
        }
        .conversation-meta {
            text-align: right;
            font-size: 12px;
            color: #999;
        }
        .unread-badge {
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
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
        .contact-btn {
            background: #28a745;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
        }
        .contact-btn:hover {
            background: #1e7e34;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>My Messages</h1>
        <div class="nav-links">
            <a href="browse_cars.php">Browse Cars</a>
            <a href="my_favorites.php">My Favorites</a>
            <a href="my_messages.php">Messages</a>
            <a href="../security_settings.php">Security Settings</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($conversations->num_rows == 0): ?>
        <div class="empty-state">
            <h3>No Messages Yet</h3>
            <p>You haven't started any conversations with sellers yet. Browse cars and contact sellers to get started!</p>
            <a href="browse_cars.php">Browse Cars</a>
        </div>
    <?php else: ?>
        <div class="conversations-list">
            <?php while ($conversation = $conversations->fetch_assoc()): ?>
                <div class="conversation-item">
                    <div class="conversation-info">
                        <div class="conversation-title">
                            <?php echo htmlspecialchars($conversation['other_user_name']); ?>
                            <span style="color: #666;">about</span>
                            <?php echo htmlspecialchars($conversation['make'] . ' ' . $conversation['model']); ?>
                        </div>
                        <div class="conversation-details">
                            Car: <?php echo htmlspecialchars($conversation['make'] . ' ' . $conversation['model']); ?> - 
                            $<?php echo number_format($conversation['price']); ?>
                        </div>
                    </div>
                    <div class="conversation-meta">
                        <div><?php echo date('M j, Y g:i A', strtotime($conversation['last_message_time'])); ?></div>
                        <?php if ($conversation['unread_count'] > 0): ?>
                            <div class="unread-badge"><?php echo $conversation['unread_count']; ?></div>
                        <?php endif; ?>
                        <div style="margin-top: 10px;">
                            <a href="view_conversation.php?seller_id=<?php echo $conversation['other_user_id']; ?>&car_id=<?php echo $conversation['car_id']; ?>" class="contact-btn">View Conversation</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
