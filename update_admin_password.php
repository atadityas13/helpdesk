<?php
/**
 * Update Admin Password - FINAL FIX
 * This script generates a FRESH hash and updates the database
 */

require_once 'src/config/database.php';

// Step 1: Generate fresh hash
$password = 'password123';
$newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

echo "<h1>🔐 Admin Password Update</h1>";
echo "<hr>";

// Step 2: Verify hash works
$isHashValid = password_verify($password, $newHash);

echo "<h2>Hash Generation</h2>";
echo "<p><strong>Password:</strong> <code>$password</code></p>";
echo "<p><strong>Generated Hash:</strong></p>";
echo "<p><code style='background: #f0f0f0; padding: 10px; display: block; word-break: break-all;'>$newHash</code></p>";
echo "<p><strong>Hash Valid:</strong> " . ($isHashValid ? "<span style='color: green;'>✅ YES</span>" : "<span style='color: red;'>❌ NO</span>") . "</p>";

if (!$isHashValid) {
    echo "<p style='color: red;'><strong>ERROR: Generated hash is invalid!</strong></p>";
    exit;
}

// Step 3: Update database
echo "<h2>Database Update</h2>";

$query = "UPDATE admins SET password = ? WHERE username = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo "<p style='color: red;'><strong>ERROR: Prepare failed:</strong> " . $conn->error . "</p>";
    exit;
}

$username = 'admin';
$stmt->bind_param("ss", $newHash, $username);

if ($stmt->execute()) {
    // Verify update
    $verifyQuery = "SELECT password FROM admins WHERE username = ?";
    $verifyStmt = $conn->prepare($verifyQuery);
    $verifyStmt->bind_param("s", $username);
    $verifyStmt->execute();
    $result = $verifyStmt->get_result()->fetch_assoc();
    $verifyStmt->close();
    
    $dbHashMatches = ($result['password'] === $newHash);
    $dbHashVerifies = password_verify($password, $result['password']);
    
    echo "<p><strong style='color: green;'>✅ Password updated successfully!</strong></p>";
    echo "<p><strong>Hash in Database matches:</strong> " . ($dbHashMatches ? "✅ YES" : "❌ NO") . "</p>";
    echo "<p><strong>Hash in Database verifies:</strong> " . ($dbHashVerifies ? "✅ YES" : "❌ NO") . "</p>";
    
    echo "<h2>Login Credentials</h2>";
    echo "<p><strong>Username:</strong> <code>admin</code></p>";
    echo "<p><strong>Password:</strong> <code>password123</code></p>";
    
    if ($dbHashMatches && $dbHashVerifies) {
        echo "<p style='background: #efe; padding: 10px; border-radius: 5px; color: green;'>";
        echo "<strong>✅ You can now login!</strong> Go to <a href='login.php'>login page</a>";
        echo "</p>";
    }
} else {
    echo "<p style='color: red;'><strong>ERROR: Execute failed:</strong> " . $stmt->error . "</p>";
}

$stmt->close();
$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
h1, h2 { color: #333; }
code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; }
hr { border: none; border-top: 1px solid #ddd; margin: 20px 0; }
p { line-height: 1.6; }
</style>

