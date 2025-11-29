<?php
/**
 * API: Update Ticket Status
 * Helpdesk MTsN 11 Majalengka
 */

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../middleware/auth.php';
require_once '../helpers/functions.php';
require_once '../helpers/ticket.php';

// Require admin login
requireAdminLogin();

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$input = json_decode(file_get_contents('php://input'), true);

$ticketId = sanitizeInput($input['ticket_id'] ?? '');
$status = sanitizeInput($input['status'] ?? '');

if (empty($ticketId) || empty($status)) {
    jsonResponse(false, 'Ticket ID and status are required');
}

// Validate status
$validStatuses = ['open', 'in_progress', 'resolved', 'closed'];
if (!in_array($status, $validStatuses)) {
    jsonResponse(false, 'Invalid status');
}

// Update status
$result = updateTicketStatus($conn, $ticketId, $status);

if ($result['success']) {
    jsonResponse(true, 'Ticket status updated successfully');
} else {
    jsonResponse(false, $result['message'] ?? 'Error updating ticket status');
}
?>
