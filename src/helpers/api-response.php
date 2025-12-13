<?php
/**
 * API Response Helper
 * Standardized JSON responses
 */

/**
 * Send success response
 */
function sendSuccessResponse($message = '', $data = []) {
    header('Content-Type: application/json');
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data,
        'timestamp' => time()
    ]);
    exit;
}

/**
 * Send error response
 */
function sendErrorResponse($message = '', $statusCode = 400, $data = []) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'data' => $data,
        'timestamp' => time()
    ]);
    exit;
}

/**
 * Send validation error
 */
function sendValidationError($message = '') {
    sendErrorResponse($message, 422);
}

/**
 * Send unauthorized response
 */
function sendUnauthorized($message = 'Unauthorized') {
    sendErrorResponse($message, 401);
}

/**
 * Send forbidden response
 */
function sendForbidden($message = 'Forbidden') {
    sendErrorResponse($message, 403);
}

/**
 * Send not found response
 */
function sendNotFound($message = 'Not Found') {
    sendErrorResponse($message, 404);
}

/**
 * Send rate limit response
 */
function sendRateLimitResponse($retryAfter = 60) {
    header('Content-Type: application/json');
    header('Retry-After: ' . $retryAfter);
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => 'Terlalu banyak request. Coba lagi dalam ' . $retryAfter . ' detik.',
        'retry_after' => $retryAfter,
        'timestamp' => time()
    ]);
    exit;
}
