<?php
/**
 * Debug Script - Check Admin Credentials
 */

require_once 'src/config/database.php';

// Get admin data
$query = "SELECT id, username, password, email FROM admins WHERE username = 'admin'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    
    echo "=== Admin Data in Database ===<br><br>";
    echo "ID: " . $admin['id'] . "<br>";
    echo "Username: " . $admin['username'] . "<br>";
    echo "Email: " . $admin['email'] . "<br>";
    echo "Password Hash: " . $admin['password'] . "<br><br>";
    
    // Test password verification
    $testPassword = 'password123';
    $isValid = password_verify($testPassword, $admin['password']);
    
    echo "=== Password Verification Test ===<br><br>";
    echo "Testing password: <strong>" . $testPassword . "</strong><br>";
    echo "Hash in database: <strong>" . $admin['password'] . "</strong><br>";
    echo "Result: <strong>" . ($isValid ? "✅ VALID" : "❌ INVALID") . "</strong><br><br>";
    
    // Show expected hash
    $expectedHash = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86.jL5rKlLa';
    echo "=== Expected Hash ===<br><br>";
    echo "Expected: " . $expectedHash . "<br>";
    echo "Actual:   " . $admin['password'] . "<br>";
    echo "Match: <strong>" . ($expectedHash === $admin['password'] ? "✅ YES" : "❌ NO") . "</strong><br><br>";
    
} else {
    echo "❌ Admin user 'admin' not found in database!";
}

$conn->close();
?>
