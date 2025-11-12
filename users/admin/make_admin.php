<?php
// Set the role of a user to 'admin' by email or user_id
// USAGE: Set $targetEmail or $targetUserId below, then run this script once in your browser or terminal

include_once(__DIR__ . '/../../config/db.connection.php');

$targetEmail = 'Lavendagrace80@gmail.com'; // CHANGE THIS to your admin email
$targetUserId = null; // Or set this to the admin's user_id (optional)

if ($targetUserId) {
    $stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE user_id = ?");
    $stmt->bind_param('i', $targetUserId);
    $stmt->execute();
    echo "User with ID $targetUserId is now an admin.";
} elseif ($targetEmail) {
    $stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE email = ?");
    $stmt->bind_param('s', $targetEmail);
    $stmt->execute();
    echo "User with email $targetEmail is now an admin.";
} else {
    echo "Please set either the email or user_id of the user you want to make admin.";
}
?>
