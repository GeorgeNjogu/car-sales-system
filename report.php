<?php
session_start();
if (!isset($_SESSION['role'])) {
    header('Location: auth/login.php');
    exit;
}
require_once __DIR__ . '/config/db.connection.php';
// Example: count of purchases per car model
$sql = "SELECT model, COUNT(*) as sales_count FROM cars WHERE status = 'sold' GROUP BY model";
$result = $conn->query($sql);
$labels = [];
$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['model'];
        $data[] = (int)$row['sales_count'];
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 0; }
        .container { max-width: 700px; margin: 60px auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #333; }
    </style>
</head>
<body>
<div class="container">
    <h2>Car Sales Report</h2>
    <canvas id="salesChart" width="600" height="350"></canvas>
</div>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Number of Sales',
                data: <?php echo json_encode($data); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
</body>
</html>
