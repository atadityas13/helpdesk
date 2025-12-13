<?php
/**
 * API: Get Customer Messages
 * GET /src/api/get-customer-messages.php?ticket_id=1
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method tidak allowed');
    }
    
    $ticketId = $_GET['ticket_id'] ?? null;
    
    if (!$ticketId || !is_numeric($ticketId)) {
        throw new Exception('Ticket ID tidak valid');
    }
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT m.*, 
               CASE 
                   WHEN m.sender_type = 'customer' THEN c.name
                   WHEN m.sender_type = 'admin' THEN a.name
                   ELSE 'System'
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
            'ticket_id' => $row['ticket_id'],
            'sender_type' => $row['sender_type'],
            'sender_name' => $row['sender_name'],
            'message' => $row['message'],
            'created_at' => $row['created_at'],
            'created_at_formatted' => date('H:i', strtotime($row['created_at']))
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'messages' => $messages
        ]
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
