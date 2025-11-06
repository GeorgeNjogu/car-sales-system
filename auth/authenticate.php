
<?php
session_start();
require_once __DIR__ . '/config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// Simple validation
if ($username === '' || $password === '') {
    header('Location: login.php?error=1');
    exit;
}


// Preferably you should use password_hash() in production.
$sql = "SELECT user_id, username, password, role FROM users WHERE username = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows === 1) {
    $user = $res->fetch_assoc();
    // if your DB password is stored with MD5 (temporary), compare MD5:
    if ($user['password'] === md5($password)) {
        // login ok
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header('Location: dashboard.php');
        exit;
    }
}

// failed
header('Location: login.php?error=1');
exit;
?>
