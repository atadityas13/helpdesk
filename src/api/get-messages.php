<?php
/**
 * API: Get Messages
 * GET /src/api/get-messages.php?ticket_number=TK-...
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/validator.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method tidak allowed');
    }
    
    if (!isset($_GET['ticket_number'])) {
        throw new Exception('Ticket number diperlukan');
    }
    
    $ticketNumber = sanitizeInput($_GET['ticket_number']);
    validateTicketNumber($ticketNumber);
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Get ticket by number
    $stmt = $conn->prepare("SELECT id FROM tickets WHERE ticket_number = ? LIMIT 1");
    $stmt->bind_param('s', $ticketNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!($ticket = $result->fetch_assoc())) {
        throw new Exception('Ticket tidak ditemukan');
    }
    
    $ticketId = $ticket['id'];
    
    // Get messages
    $stmt = $conn->prepare("
        SELECT m.*, 
               CASE 
                   WHEN m.sender_type = 'customer' THEN c.name
                   WHEN m.sender_type = 'admin' THEN a.username
                   ELSE 'Unknown'
               END as sender_name
        FROM messages m
        LEFT JOIN customers c ON m.sender_type = 'customer' AND m.sender_id = c.id
        LEFT JOIN admins a ON m.sender_type = 'admin' AND m.sender_id = a.id
        WHERE m.ticket_id = ?
        ORDER BY m.created_at ASC
    ");
    
    $stmt->bind_param('i', $ticketId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'id' => $row['id'],
            'sender_type' => $row['sender_type'],
            'sender_name' => $row['sender_name'],
            'message' => $row['message'],
            'attachment_url' => $row['attachment_url'],
            'created_at' => $row['created_at'],
            'created_at_formatted' => formatTimeRelative($row['created_at'])
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'ticket_number' => $ticketNumber,
            'ticket_id' => $ticketId,
            'messages' => $messages,
            'total_messages' => count($messages)
        ]
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
