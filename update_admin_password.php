<?php
/**
 * Update Admin Password
 * Run this script once to update the password
 */

require_once 'src/config/database.php';

$newPassword = 'password123';
$newHash = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86.jL5rKlLa';

// Update admin password
$query = "UPDATE admins SET password = ? WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $newHash, 'admin');

if ($stmt->execute()) {
    echo "✅ Admin password updated successfully!<br>";
    echo "Username: <strong>admin</strong><br>";
    echo "Password: <strong>password123</strong><br>";
    echo "<br>You can now login with these credentials.";
} else {
    echo "❌ Error updating password: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
