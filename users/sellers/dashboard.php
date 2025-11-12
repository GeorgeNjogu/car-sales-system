<?php
include_once __DIR__ . '/../../includes/header.php';
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../../auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 60px auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #333; }
        a { display: block; margin: 12px 0; color: #007BFF; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <h2>Welcome, Seller!</h2>
    <p>Use the links below to manage your cars:</p>
    <a href="https://www.autoevolution.com/cars/?utm_source=chatgpt.com" target="_blank">My Cars</a>
    <a href="add_car.php">Add New Car</a>
    <a href="../../auth/login.php">Login</a>
    <a href="../../auth/logout.php">Logout</a>
</div>
<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
