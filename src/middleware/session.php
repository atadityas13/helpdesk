<?php
/**
 * Session Management with Timeout
 * Helpdesk MTsN 11 Majalengka
 */

require_once __DIR__ . '/../../config/.env.php';

// Start session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Initialize session dengan timeout check
 */
function initSession() {
    // Check jika session sudah timeout
    if (isset($_SESSION['last_activity'])) {
        $inactiveTime = time() - $_SESSION['last_activity'];
        
        if ($inactiveTime > SESSION_TIMEOUT) {
            // Session timeout - destroy session
            session_destroy();
            return false;
        }
    }
    
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Require admin login - dengan session timeout check
 */
function requireAdminLogin() {
    if (!initSession()) {
        // Session expired
        header('Location: ../login.php?expired=1');
        exit;
    }
    
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
    header('Location: ../login.php?logged_out=1');
    exit;
}

/**
 * Get remaining session time (dalam detik)
 */
function getSessionRemainingTime() {
    if (!isset($_SESSION['last_activity'])) {
        return SESSION_TIMEOUT;
    }
    
    $elapsed = time() - $_SESSION['last_activity'];
    $remaining = SESSION_TIMEOUT - $elapsed;
    
    return max(0, $remaining);
}

/**
 * Check apakah admin sedang login
 */
function isAdminLoggedIn() {
    if (!initSession()) {
        return false;
    }
    
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

?>
