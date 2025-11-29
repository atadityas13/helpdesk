<?php
/**
 * API: Send Message
 * Helpdesk MTsN 11 Majalengka
 */

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../helpers/functions.php';
require_once '../helpers/ticket.php';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$ticketNumber = sanitizeInput($input['ticket_number'] ?? '');
$message = sanitizeInput($input['message'] ?? '');
$senderType = sanitizeInput($input['sender_type'] ?? 'customer');

if (empty($ticketNumber) || empty($message)) {
    jsonResponse(false, 'Ticket number and message are required');
}

// Get ticket
$ticket = getTicketByNumber($conn, $ticketNumber);

if (!$ticket) {
    jsonResponse(false, 'Ticket not found');
}

// Determine sender ID
$senderId = null;
if ($senderType === 'customer') {
    $senderId = $ticket['customer_id'];
} else if ($senderType === 'admin') {
    // In real scenario, get from session
    $senderId = $input['sender_id'] ?? 0;
}

// Add message
$result = addMessageToTicket($conn, $ticket['id'], $senderType, $senderId, $message);

if ($result['success']) {
    // Update ticket to in_progress if first message from admin
    if ($senderType === 'admin') {
        if ($ticket['status'] === 'open') {
            updateTicketStatus($conn, $ticket['id'], 'in_progress');
        }
    }
    
    jsonResponse(true, 'Message sent successfully', [
        'message_id' => $result['message_id']
    ]);
} else {
    jsonResponse(false, $result['message'] ?? 'Error sending message');
}
?>
