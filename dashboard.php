<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Car Sales Management - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f9fc;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
        }
        nav {
            background: #343a40;
            padding: 10px;
        }
        nav a {
            color: white;
            margin-right: 20px;
            text-decoration: none;
            font-weight: bold;
        }
        nav a:hover {
            text-decoration: underline;
        }
        .container {
            padding: 20px;
        }
        .welcome {
            font-size: 20px;
            color: #333;
        }
    </style>
</head>
<body>

<header>
    <h1>Car Sales Management System</h1>
</header>

<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="users.php">Users</a>
    <a href="cars.php">Cars</a>
    <a href="customers.php">Customers</a>
    <a href="sales.php">Sales</a>
    <a href="payments.php">Payments</a>
    <a href="logout.php" style="color:red;">Logout</a>
</nav>

<div class="container">
    <p class="welcome">Welcome, <?php echo $_SESSION['username']; ?>!</p>
    <p>Select an option from the menu above to manage system records.</p>
</div>

</body>
</html>

