<?php
/**
 * Generate Valid Password Hash
 * For password: password123
 */

$password = 'password123';

echo "=== Generating valid hashes for password: password123 ===<br><br>";

// Generate multiple hashes to ensure one works
for ($i = 0; $i < 5; $i++) {
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    
    // Verify setiap hash yang di-generate
    $isValid = password_verify($password, $hash);
    
    echo "Hash #" . ($i+1) . ": " . $hash . "<br>";
    echo "Valid: " . ($isValid ? "✅ YES" : "❌ NO") . "<br>";
    echo "Use this hash in database: <code>" . $hash . "</code><br><br>";
}
?>
