<?php
/**
 * API: Get Messages for Ticket
 * Helpdesk MTsN 11 Majalengka
 */

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../helpers/functions.php';
require_once '../helpers/ticket.php';

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Invalid request method');
}

// Get ticket number from query
$ticketNumber = sanitizeInput($_GET['ticket_number'] ?? '');

if (empty($ticketNumber)) {
    jsonResponse(false, 'Ticket number is required');
}

// Get ticket
$ticket = getTicketByNumber($conn, $ticketNumber);

if (!$ticket) {
    jsonResponse(false, 'Ticket not found');
}

// Get messages
$messages = getTicketMessages($conn, $ticket['id']);

jsonResponse(true, 'Messages retrieved successfully', [
    'ticket' => $ticket,
    'messages' => $messages
]);
?>
