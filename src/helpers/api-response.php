<?php
/**
 * API Response Handler
 * Helpdesk MTsN 11 Majalengka
 */

/**
 * Send JSON response dengan format standard
 */
function apiResponse($success, $message, $data = null, $statusCode = null) {
    // Determine HTTP status code
    if ($statusCode === null) {
        $statusCode = $success ? 200 : 400;
    }
    
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('c')
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

/**
 * Success response
 */
function successResponse($message, $data = null, $statusCode = 200) {
    apiResponse(true, $message, $data, $statusCode);
}

/**
 * Error response
 */
function errorResponse($message, $statusCode = 400, $data = null) {
    apiResponse(false, $message, $data, $statusCode);
}

/**
 * Validation error response
 */
function validationErrorResponse($errors) {
    apiResponse(false, 'Validation failed', $errors, 400);
}

/**
 * Not found response
 */
function notFoundResponse($message = 'Resource not found') {
    apiResponse(false, $message, null, 404);
}

/**
 * Unauthorized response
 */
function unauthorizedResponse($message = 'Unauthorized') {
    apiResponse(false, $message, null, 401);
}

/**
 * Server error response
 */
function serverErrorResponse($message = 'Internal server error') {
    error_log("Server error: $message");
    apiResponse(false, $message, null, 500);
}

?>
