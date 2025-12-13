<?php
/**
 * API: Get Messages
 * Helpdesk MTsN 11 Majalengka
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';
require_once '../helpers/functions.php';
require_once '../helpers/ticket.php';
require_once '../helpers/api-response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Invalid request method', 405);
}

$ticketNumber = sanitizeInput($_GET['ticket_number'] ?? '');

if (empty($ticketNumber)) {
    errorResponse('Nomor ticket harus diisi', 400);
}

// Get ticket
$ticket = getTicketByNumber($conn, $ticketNumber);

if (!$ticket) {
    notFoundResponse('Ticket tidak ditemukan');
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
    error_log("Database prepare failed: " . $conn->error);
    serverErrorResponse();
}

$stmt->bind_param("i", $ticket['id']);

if (!$stmt->execute()) {
    error_log("Database execute failed: " . $stmt->error);
    $stmt->close();
    serverErrorResponse();
}

$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Format data
foreach ($messages as &$msg) {
    $msg['sender_type'] = strtolower($msg['sender_type']);
    $msg['is_read'] = (bool) $msg['is_read'];
}
unset($msg);

successResponse('Pesan berhasil diambil', [
    'ticket' => $ticket,
    'messages' => $messages
]);
?>
