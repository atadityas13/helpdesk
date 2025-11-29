<?php
/**
 * API: Get Admin Status
 * Helpdesk MTsN 11 Majalengka
 * 
 * Menggunakan unified admin-status helper
 */

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../helpers/functions.php';
require_once '../helpers/ticket.php';
require_once '../helpers/admin-status.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $ticketNumber = sanitizeInput($_GET['ticket_number'] ?? '');
    
    if (empty($ticketNumber)) {
        jsonResponse(false, 'Ticket number is required');
    }
    
    // Get admin status using unified helper
    $adminStatus = getAdminStatusForCustomer($conn, $ticketNumber);
    
    jsonResponse(true, 'Admin status fetched', $adminStatus);
    
} else {
    jsonResponse(false, 'Invalid request method');
}
?>
