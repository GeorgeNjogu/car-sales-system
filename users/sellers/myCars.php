<?php
session_start();
include_once(__DIR__ . "/../../config/db.connection.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../../auth/login.php');
    exit;
}

header('Location: https://www.autoevolution.com/cars/?utm_source=chatgpt.com');
exit;
?>
