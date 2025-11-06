<?php
// Ensure no output before this tag! No whitespace, no BOM.
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
// Unset all session variables
$_SESSION = array();
// Destroy the session if active
if (session_status() === PHP_SESSION_ACTIVE) {
	session_destroy();
}
// Redirect to login or home page
header("Location: login.php");
exit;
