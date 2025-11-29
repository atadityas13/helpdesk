<?php
/**
 * Helper Functions
 * Helpdesk MTsN 11 Majalengka
 */

/**
 * Generate unique ticket number
 * Format: TK-YYYYMMDD-XXXXX
 */
function generateTicketNumber() {
    $date = date('Ymd');
    $random = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
    return 'TK-' . $date . '-' . $random;
}

/**
 * Sanitize input
 */
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in (admin)
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Get customer by ticket number
 */
function getCustomerByTicket($conn, $ticketNumber) {
    $query = "SELECT c.* FROM customers c 
              JOIN tickets t ON c.id = t.customer_id 
              WHERE t.ticket_number = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $ticketNumber);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Get ticket by number
 */
function getTicketByNumber($conn, $ticketNumber) {
    $query = "SELECT * FROM tickets WHERE ticket_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $ticketNumber);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Format date time
 */
function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

/**
 * Get status badge color
 */
function getStatusBadge($status) {
    $badges = [
        'open' => 'badge-primary',
        'in_progress' => 'badge-warning',
        'resolved' => 'badge-success',
        'closed' => 'badge-secondary'
    ];
    
    return $badges[$status] ?? 'badge-light';
}

/**
 * Log error
 */
function logError($message) {
    $logFile = __DIR__ . '/../../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND);
}

/**
 * JSON response
 */
function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}
?>
