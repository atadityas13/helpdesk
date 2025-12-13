<?php
/**
 * Authentication Middleware
 * Helpdesk MTsN 11 Majalengka
 */

require_once __DIR__ . '/session.php';

/**
 * Verify admin password
 */
function verifyAdminPassword($conn, $username, $password) {
    $query = "SELECT id, username, password FROM admins WHERE username = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Database prepare failed: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("s", $username);
    
    if (!$stmt->execute()) {
        error_log("Database execute failed: " . $stmt->error);
        return false;
    }
    
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$result) {
        // User not found - log tanpa expose username
        error_log("Login attempt failed: User not found");
        return false;
    }
    
    // Verify password dengan bcrypt
    $isValid = password_verify($password, $result['password']);
    
    if ($isValid) {
        $_SESSION['admin_id'] = $result['id'];
        $_SESSION['admin_username'] = $result['username'];
        return true;
    }
    
    // Password invalid - log tanpa expose details
    error_log("Login attempt failed: Invalid password");
    return false;
}
?>
