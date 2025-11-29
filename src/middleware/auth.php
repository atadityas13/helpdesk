<?php
/**
 * Authentication Middleware
 * Helpdesk MTsN 11 Majalengka
 */

session_start();

/**
 * Require admin to be logged in
 * If not, redirect to login
 */
function requireAdminLogin() {
    if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
        header('Location: ../login.php');
        exit;
    }
}

/**
 * Logout admin
 */
function logoutAdmin() {
    session_destroy();
    header('Location: ../login.php');
    exit;
}

/**
 * Verify admin password
 */
function verifyAdminPassword($conn, $username, $password) {
    $query = "SELECT id, username, password FROM admins WHERE username = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("s", $username);
    
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$result) {
        error_log("User not found: $username");
        return false;
    }
    
    error_log("User found: $username, Hash: " . substr($result['password'], 0, 20) . "...");
    
    $isValid = password_verify($password, $result['password']);
    error_log("Password verify result: " . ($isValid ? "true" : "false"));
    
    if ($isValid) {
        $_SESSION['admin_id'] = $result['id'];
        $_SESSION['admin_username'] = $result['username'];
        return true;
    }
    
    return false;
}
?>
