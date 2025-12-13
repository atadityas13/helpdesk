<?php
/**
 * Session Middleware
 * Manages admin session dengan auto-timeout
 */

define('SESSION_TIMEOUT', defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 3600);

/**
 * Initialize session dengan timeout configuration
 */
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set session cookie parameters
        $cookieOptions = [
            'lifetime' => SESSION_TIMEOUT,
            'path' => '/',
            'secure' => defined('SESSION_SECURE_COOKIE') ? SESSION_SECURE_COOKIE : false,
            'httponly' => true,
            'samesite' => defined('SESSION_SAME_SITE') ? SESSION_SAME_SITE : 'Lax'
        ];
        
        session_set_cookie_params($cookieOptions);
        session_start();
        
        // Initialize timeout
        if (!isset($_SESSION['timeout'])) {
            $_SESSION['timeout'] = time() + SESSION_TIMEOUT;
        }
    }
}

/**
 * Check jika admin logged in
 * @return bool
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

/**
 * Require admin login - redirect jika belum
 */
function requireAdminLogin() {
    initSession();
    
    if (!isAdminLoggedIn()) {
        header('Location: ' . dirname(dirname(__DIR__)) . '/login.php');
        exit;
    }
    
    // Check timeout
    if (time() > $_SESSION['timeout']) {
        session_destroy();
        header('Location: ' . dirname(dirname(__DIR__)) . '/login.php?expired=1');
        exit;
    }
    
    // Refresh timeout
    $_SESSION['timeout'] = time() + SESSION_TIMEOUT;
    $_SESSION['last_activity'] = time();
}

/**
 * Get remaining session time dalam detik
 * @return int
 */
function getSessionRemainingTime() {
    initSession();
    return max(0, $_SESSION['timeout'] - time());
}

/**
 * Get admin ID dari session
 * @return int|null
 */
function getAdminId() {
    initSession();
    return $_SESSION['admin_id'] ?? null;
}

/**
 * Get admin username dari session
 * @return string|null
 */
function getAdminUsername() {
    initSession();
    return $_SESSION['admin_username'] ?? null;
}

/**
 * Get admin role dari session
 * @return string|null
 */
function getAdminRole() {
    initSession();
    return $_SESSION['admin_role'] ?? null;
}

/**
 * Logout admin - destroy session
 */
function logoutAdmin() {
    initSession();
    session_destroy();
    header('Location: ' . dirname(dirname(__DIR__)) . '/index.php');
    exit;
}

/**
 * Set session data
 */
function setSessionData($key, $value) {
    initSession();
    $_SESSION[$key] = $value;
}

/**
 * Get session data
 */
function getSessionData($key, $default = null) {
    initSession();
    return $_SESSION[$key] ?? $default;
}
