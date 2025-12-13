<?php
/**
 * CSRF Protection Middleware
 * Protect dari Cross-Site Request Forgery attacks
 */

require_once __DIR__ . '/session.php';

/**
 * Generate CSRF token - secure random
 * @return string
 */
function generateCsrfToken() {
    initSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get CSRF token field untuk HTML form
 * @return string HTML input element
 */
function getCsrfTokenField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Get CSRF token value
 * @return string
 */
function getCsrfToken() {
    return generateCsrfToken();
}

/**
 * Validate CSRF token dari request
 * @param string $token
 * @return bool
 */
function validateCsrfToken($token) {
    initSession();
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verify CSRF token di POST request
 * @return bool
 */
function verifyCsrfRequest() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return true;  // GET requests don't need CSRF
    }
    
    if (!defined('ENABLE_CSRF') || !ENABLE_CSRF) {
        return true;  // CSRF disabled
    }
    
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($token)) {
        return false;
    }
    
    return validateCsrfToken($token);
}

/**
 * Require valid CSRF token - return error if invalid
 */
function requireValidCsrfToken() {
    if (!verifyCsrfRequest()) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'Invalid CSRF token']));
    }
}
