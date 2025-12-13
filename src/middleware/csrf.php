<?php
/**
 * CSRF Protection Middleware
 * Helpdesk MTsN 11 Majalengka
 */

/**
 * Generate CSRF token - simpan di session
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get HTML hidden input untuk CSRF token
 */
function getCsrfTokenField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Get CSRF token value (untuk API/AJAX)
 */
function getCsrfToken() {
    return generateCsrfToken();
}

/**
 * Validate CSRF token
 */
function validateCsrfToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    // Use hash_equals untuk prevent timing attacks
    return hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/**
 * Verify CSRF token dari request
 */
function verifyCsrfRequest() {
    // Hanya check untuk POST, PUT, DELETE, PATCH
    if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE', 'PATCH'])) {
        return true;
    }
    
    // Get token dari POST atau header
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    
    if (!$token || !validateCsrfToken($token)) {
        return false;
    }
    
    return true;
}

?>
