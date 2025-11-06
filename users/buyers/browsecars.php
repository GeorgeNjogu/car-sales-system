<?php
// Show all errors for debugging
session_start();
include_once(__DIR__ . "/../../config/db.connection.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header('Location: ../../auth/login.php');
    exit;
}

// Get filter parameters
$make = isset($_GET['make']) ? $_GET['make'] : '';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 999999;
$minYear = isset($_GET['min_year']) ? (int)$_GET['min_year'] : 1900;
$maxYear = isset($_GET['max_year']) ? (int)$_GET['max_year'] : date('Y') + 1;
$fuelType = isset($_GET['fuel_type']) ? $_GET['fuel_type'] : '';
$transmission = isset($_GET['transmission']) ? $_GET['transmission'] : '';
$condition = isset($_GET['condition']) ? $_GET['condition'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query with filters
$whereConditions = ["c.status = 'approved'"];
$params = [];
$types = '';

if (!empty($make)) {
    $whereConditions[] = "make LIKE ?";
    $params[] = "%$make%";
    $types .= 's';
}

if ($minPrice > 0) {
    $whereConditions[] = "price >= ?";
    $params[] = $minPrice;
    $types .= 'd';
}

if ($maxPrice < 999999) {
    $whereConditions[] = "price <= ?";
    $params[] = $maxPrice;
    $types .= 'd';
}

if ($minYear > 1900) {
    $whereConditions[] = "year >= ?";
    $params[] = $minYear;
    $types .= 'i';
}

if ($maxYear < date('Y') + 1) {
    $whereConditions[] = "year <= ?";
    $params[] = $maxYear;
    $types .= 'i';
}

if (!empty($fuelType)) {
    $whereConditions[] = "fuel_type = ?";
    $params[] = $fuelType;
    $types .= 's';
}

if (!empty($transmission)) {
    $whereConditions[] = "transmission = ?";
    $params[] = $transmission;
    $types .= 's';
}

if (!empty($condition)) {
    $whereConditions[] = "car_condition = ?";
    $params[] = $condition;
    $types .= 's';
}

if (!empty($search)) {
    $whereConditions[] = "(make LIKE ? OR model LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'sss';
}

$whereClause = implode(' AND ', $whereConditions);
$query = "SELECT c.*, u.name as seller_name FROM cars c 
          JOIN users u ON c.seller_id = u.user_id 
          WHERE $whereClause 
          ORDER BY c.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get unique makes for filter dropdown
$makesResult = $conn->query("SELECT DISTINCT make FROM cars WHERE status = 'approved' ORDER BY make");
$makes = [];
while ($row = $makesResult->fetch_assoc()) {
    $makes[] = $row['make'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Cars - Car Management System</title>
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
        .filters {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .filter-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .filter-group input, .filter-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
        }
        .search-box {
            flex: 2;
            min-width: 300px;
        }
        .search-box input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        button {
            background: #007BFF;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .clear-btn {
            background: #6c757d;
        }
        .clear-btn:hover {
            background: #545b62;
        }
        .cars-grid {
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
        .favorite-btn {
            background: #28a745;
            color: white;
        }
        .favorite-btn:hover {
            background: #1e7e34;
        }
        .results-count {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .nav-links {
            margin-bottom: 20px;
        }
        .nav-links a {
            color: #007BFF;
            text-decoration: none;
            margin-right: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Browse Available Cars</h1>
        <div class="nav-links">
            <a href="browse_cars.php">Browse Cars</a>
            <a href="my_favorites.php">My Favorites</a>
            <a href="my_messages.php">Messages</a>
            <a href="../security_settings.php">Security Settings</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>

    <div class="filters">
        <form method="GET">
            <div class="filter-row">
                <div class="search-box">
                    <label for="search">Search Cars</label>
                    <input type="text" name="search" placeholder="Search by make, model, or description..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            
            <div class="filter-row">
                <div class="filter-group">
                    <label for="make">Make</label>
                    <select name="make">
                        <option value="">All Makes</option>
                        <?php foreach ($makes as $makeOption): ?>
                            <option value="<?php echo htmlspecialchars($makeOption); ?>" <?php echo $make === $makeOption ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($makeOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="min_price">Min Price</label>
                    <input type="number" name="min_price" placeholder="0" value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>">
                </div>
                
                <div class="filter-group">
                    <label for="max_price">Max Price</label>
                    <input type="number" name="max_price" placeholder="No limit" value="<?php echo $maxPrice < 999999 ? $maxPrice : ''; ?>">
                </div>
            </div>
            
            <div class="filter-row">
                <div class="filter-group">
                    <label for="min_year">Min Year</label>
                    <input type="number" name="min_year" placeholder="1900" value="<?php echo $minYear > 1900 ? $minYear : ''; ?>">
                </div>
                
                <div class="filter-group">
                    <label for="max_year">Max Year</label>
                    <input type="number" name="max_year" placeholder="<?php echo date('Y'); ?>" value="<?php echo $maxYear < date('Y') + 1 ? $maxYear : ''; ?>">
                </div>
                
                <div class="filter-group">
                    <label for="fuel_type">Fuel Type</label>
                    <select name="fuel_type">
                        <option value="">All Types</option>
                        <option value="petrol" <?php echo $fuelType === 'petrol' ? 'selected' : ''; ?>>Petrol</option>
                        <option value="diesel" <?php echo $fuelType === 'diesel' ? 'selected' : ''; ?>>Diesel</option>
                        <option value="electric" <?php echo $fuelType === 'electric' ? 'selected' : ''; ?>>Electric</option>
                        <option value="hybrid" <?php echo $fuelType === 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="transmission">Transmission</label>
                    <select name="transmission">
                        <option value="">All Types</option>
                        <option value="manual" <?php echo $transmission === 'manual' ? 'selected' : ''; ?>>Manual</option>
                        <option value="automatic" <?php echo $transmission === 'automatic' ? 'selected' : ''; ?>>Automatic</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="condition">Condition</label>
                    <select name="condition">
                        <option value="">All Conditions</option>
                        <option value="new" <?php echo $condition === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="used" <?php echo $condition === 'used' ? 'selected' : ''; ?>>Used</option>
                    </select>
                </div>
            </div>
            
            <div class="filter-buttons">
                <button type="submit">Apply Filters</button>
                <a href="browse_cars.php" class="clear-btn" style="text-decoration: none; display: inline-block;">Clear Filters</a>
            </div>
        </form>
    </div>

    <div class="results-count">
        <strong><?php echo $result->num_rows; ?></strong> cars found
    </div>

    <div class="cars-grid">
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
                    <div class="car-actions">
                        <a href="view.car.php?id=<?php echo $row['car_id']; ?>" class="view-btn">View Details</a>
                        <a href="add_favorite.php?id=<?php echo $row['car_id']; ?>" class="favorite-btn">Add to Favorites</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
