<?php
include_once("../config/db_connect.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Car Sales Management System</title>
</head>
<body>
  <h1>Welcome to Car Sales Management System</h1>
  <?php
  if ($conn) {
      echo "<p>✅ Database connection successful!</p>";
  } else {
      echo "<p>❌ Database connection failed!</p>";
  }
  ?>
</body>
</html>

