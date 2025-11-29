<?php
/**
 * Update Admin Password
 * Generate and update with a VALID bcrypt hash
 */

require_once 'src/config/database.php';

$password = 'password123';

// Generate a fresh, valid hash
$newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

echo "=== Updating Admin Password ===<br><br>";
echo "Password: <strong>$password</strong><br>";
echo "Generated Hash: <strong>$newHash</strong><br><br>";

// Verify the hash is valid before updating
$isHashValid = password_verify($password, $newHash);
echo "Hash Verification: " . ($isHashValid ? "✅ VALID" : "❌ INVALID") . "<br><br>";

if (!$isHashValid) {
    echo "❌ Hash verification failed! This should not happen.<br>";
    exit;
}

$username = 'admin';

// Update admin password
$query = "UPDATE admins SET password = ? WHERE username = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo "❌ Prepare failed: " . $conn->error;
    exit;
}

$stmt->bind_param("ss", $newHash, $username);

if ($stmt->execute()) {
    echo "✅ Admin password updated successfully!<br><br>";
    echo "Username: <strong>admin</strong><br>";
    echo "Password: <strong>password123</strong><br>";
    echo "New Hash in Database: <strong>$newHash</strong><br><br>";
    echo "You can now login with these credentials.";
} else {
    echo "❌ Error updating password: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
