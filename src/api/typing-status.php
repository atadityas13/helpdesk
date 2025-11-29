<?php
/**
 * API: Typing Status
 * Helpdesk MTsN 11 Majalengka
 */

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../helpers/functions.php';
require_once '../helpers/ticket.php';

// Handle POST request - set typing status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    $ticketNumber = sanitizeInput($input['ticket_number'] ?? '');
    $isTyping = isset($input['is_typing']) ? (bool)$input['is_typing'] : false;

    if (empty($ticketNumber)) {
        jsonResponse(false, 'Ticket number is required');
    }

    // Get ticket
    $ticket = getTicketByNumber($conn, $ticketNumber);

    if (!$ticket) {
        jsonResponse(false, 'Ticket not found');
    }

    // Update or create typing status in a file
    $typingFile = __DIR__ . '/../../logs/typing_' . md5($ticket['id']) . '.json';

    if ($isTyping) {
        $typingData = [
            'admin_name' => 'Admin Support',
            'ticket_id' => $ticket['id'],
            'timestamp' => time()
        ];
        file_put_contents($typingFile, json_encode($typingData));
    } else {
        if (file_exists($typingFile)) {
            unlink($typingFile);
        }
    }

    jsonResponse(true, 'Typing status updated');
}

// Handle GET request - check typing status
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $ticketNumber = sanitizeInput($_GET['ticket_number'] ?? '');

    if (empty($ticketNumber)) {
        jsonResponse(false, 'Ticket number is required');
    }

    // Get ticket
    $ticket = getTicketByNumber($conn, $ticketNumber);

    if (!$ticket) {
        jsonResponse(false, 'Ticket not found');
    }

    $typingFile = __DIR__ . '/../../logs/typing_' . md5($ticket['id']) . '.json';

    if (file_exists($typingFile)) {
        $typingData = json_decode(file_get_contents($typingFile), true);
        
        // Check if typing status is still fresh (within last 5 seconds)
        if (time() - $typingData['timestamp'] < 5) {
            jsonResponse(true, 'Admin is typing', [
                'is_typing' => true,
                'admin_name' => $typingData['admin_name']
            ]);
        } else {
            // Clean up old file
            unlink($typingFile);
            jsonResponse(true, 'No one is typing', ['is_typing' => false]);
        }
    } else {
        jsonResponse(true, 'No one is typing', ['is_typing' => false]);
    }
}

else {
    jsonResponse(false, 'Invalid request method');
}
?>
