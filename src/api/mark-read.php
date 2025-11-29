<?php
/**
 * API: Mark Messages as Read
 * Helpdesk MTsN 11 Majalengka
 * 
 * Menggunakan unified admin-status helper
 */

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../helpers/functions.php';
require_once '../helpers/ticket.php';
require_once '../helpers/admin-status.php';

session_start();

// Handle POST request - mark messages as read
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $ticketNumber = sanitizeInput($input['ticket_number'] ?? '');
    $viewerType = $input['viewer_type'] ?? ''; // 'customer' atau 'admin'
    
    if (empty($ticketNumber)) {
        jsonResponse(false, 'Ticket number is required');
    }
    
    // Get ticket
    $ticket = getTicketByNumber($conn, $ticketNumber);
    
    if (!$ticket) {
        jsonResponse(false, 'Ticket not found');
    }
    
    // Logic:
    // - Customer viewing → mark admin messages as read
    // - Admin viewing → mark customer messages as read (handled by trackAdminViewing)
    
    if ($viewerType === 'customer') {
        // Customer marking admin messages as read
        $senderTypeToMark = 'admin';
        
        $query = "UPDATE messages 
                  SET is_read = TRUE 
                  WHERE ticket_id = ? 
                  AND sender_type = ? 
                  AND is_read = FALSE";
        
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            jsonResponse(false, 'Database error');
        }
        
        $stmt->bind_param("is", $ticket['id'], $senderTypeToMark);
        
        if ($stmt->execute()) {
            jsonResponse(true, 'Messages marked as read');
        } else {
            error_log("Execute failed: " . $stmt->error);
            jsonResponse(false, 'Error marking messages as read');
        }
    } else {
        // Admin marking customer messages as read
        // This is handled automatically by trackAdminViewing helper
        jsonResponse(true, 'Messages marked as read');
    }
}

else {
    jsonResponse(false, 'Invalid request method');
}
?>
