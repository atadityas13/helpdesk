<?php
/**
 * API: Track Admin Viewing Ticket
 * Helpdesk MTsN 11 Majalengka
 * 
 * Menggunakan unified admin-status helper
 */

header('Content-Type: application/json');
session_start();

require_once '../config/database.php';
require_once '../helpers/functions.php';
require_once '../helpers/admin-status.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Support both Content-Type: application/json dan text/plain (untuk sendBeacon)
    $input = null;
    
    if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
    } elseif (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'text/plain') !== false) {
        // sendBeacon mengirim dengan Content-Type text/plain
        $input = json_decode(file_get_contents('php://input'), true);
    } else {
        $input = $_POST;
    }
    
    $ticketNumber = sanitizeInput($input['ticket_number'] ?? '');
    $isViewing = $input['is_viewing'] ?? false;
    
    if (empty($ticketNumber) || !isset($_SESSION['admin_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    $adminId = $_SESSION['admin_id'];
    
    // Use unified helper function
    $result = trackAdminViewing($conn, $adminId, $ticketNumber, $isViewing);
    
    jsonResponse($result['success'], $result['message']);
    
} else {
    jsonResponse(false, 'Invalid request method');
}
?>
