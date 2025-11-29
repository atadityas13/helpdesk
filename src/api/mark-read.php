<?php
/**
 * API: Mark Messages as Read
 * Helpdesk MTsN 11 Majalengka
 */

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../helpers/functions.php';
require_once '../helpers/ticket.php';

// Handle POST request - mark messages as read
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $ticketNumber = sanitizeInput($input['ticket_number'] ?? '');
    
    if (empty($ticketNumber)) {
        jsonResponse(false, 'Ticket number is required');
    }
    
    // Get ticket
    $ticket = getTicketByNumber($conn, $ticketNumber);
    
    if (!$ticket) {
        jsonResponse(false, 'Ticket not found');
    }
    
    // Mark all unread messages dari ADMIN sebagai dibaca (untuk customer view)
    // Atau mark semua unread messages dari CUSTOMER sebagai dibaca (untuk admin view)
    $query = "UPDATE messages SET is_read = TRUE 
              WHERE ticket_id = ? AND is_read = FALSE";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ticket['id']);
    
    if ($stmt->execute()) {
        jsonResponse(true, 'Messages marked as read');
    } else {
        jsonResponse(false, 'Error marking messages as read');
    }
}

else {
    jsonResponse(false, 'Invalid request method');
}
?>
