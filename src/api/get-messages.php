<?php
/**
 * API: Get Messages
 * Helpdesk MTsN 11 Majalengka
 */

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../helpers/functions.php';
require_once '../helpers/ticket.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $ticketNumber = sanitizeInput($_GET['ticket_number'] ?? '');
    
    if (empty($ticketNumber)) {
        jsonResponse(false, 'Ticket number is required');
    }
    
    // Get ticket
    $ticket = getTicketByNumber($conn, $ticketNumber);
    
    if (!$ticket) {
        jsonResponse(false, 'Ticket not found');
    }
    
    // Get messages dengan sender info
    $query = "SELECT 
                m.id,
                m.ticket_id,
                m.sender_type,
                m.sender_id,
                m.message,
                m.attachment_url,
                m.is_read,
                m.created_at,
                CASE 
                    WHEN m.sender_type = 'customer' THEN 'Customer'
                    WHEN m.sender_type = 'admin' THEN COALESCE(a.username, 'Admin')
                    ELSE 'Unknown'
                END as sender_name
              FROM messages m
              LEFT JOIN admins a ON m.sender_type = 'admin' AND m.sender_id = a.id
              WHERE m.ticket_id = ?
              ORDER BY m.created_at ASC";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        jsonResponse(false, 'Database error');
    }
    
    $stmt->bind_param("i", $ticket['id']);
    
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        jsonResponse(false, 'Error fetching messages');
    }
    
    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Ensure sender_type is returned correctly
    foreach ($messages as &$msg) {
        // Pastikan sender_type adalah lowercase 'customer' atau 'admin'
        $msg['sender_type'] = strtolower($msg['sender_type']);
        $msg['is_read'] = (bool) $msg['is_read'];
    }
    unset($msg);
    
    jsonResponse(true, 'Messages fetched', [
        'ticket' => $ticket,
        'messages' => $messages
    ]);
    
} else {
    jsonResponse(false, 'Invalid request method');
}
