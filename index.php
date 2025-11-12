
<?php
session_start();
// If user is already logged in, redirect to their dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: users/admin/manage_users.php");
            exit;
        case 'seller':
            header("Location: users/sellers/my_cars.php");
            exit;
        case 'buyer':
            header("Location: users/buyers/browse_cars.php");
            exit;
    }
}
include_once __DIR__ . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
            width: 350px;
        }
        h1 {
            margin-bottom: 10px;
        }
        p {
            color: #666;
        }
        a {
            display: block;
            text-decoration: none;
            color: white;
            background: #007BFF;
            margin: 10px 0;
            padding: 10px;
            border-radius: 8px;
        }
        a:hover {
            background: #0056b3;
        }
        footer {
            position: absolute;
            bottom: 20px;
            font-size: 12px;
            color: #aaa;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Welcome to Car Management System</h1>
    <p>Please login or register to continue</p>

<a href="auth/login.php">Login</a>
<a href="auth/register.php">Register</a>
</div>


<?php include_once __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
