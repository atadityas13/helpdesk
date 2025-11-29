<?php
/**
 * Authentication Middleware
 * Helpdesk MTsN 11 Majalengka
 */

session_start();

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Require admin to be logged in
 * If not, redirect to login
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
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
    $stmt->bind_param("s", $username);
    $stmt->execute();
    
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result && password_verify($password, $result['password'])) {
        $_SESSION['admin_id'] = $result['id'];
        $_SESSION['admin_username'] = $result['username'];
        return true;
    }
    
    return false;
}
?>
