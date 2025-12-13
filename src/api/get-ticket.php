<?php
/**
 * API: Get Ticket Details
 * GET /src/api/get-ticket.php?id=123
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/session.php';

try {
    requireAdminLogin();
    
    if (!isset($_GET['id'])) {
        throw new Exception('Ticket ID diperlukan');
    }
    
    $ticketId = (int)$_GET['id'];
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT t.*, c.name, c.email, c.phone
        FROM tickets t
        JOIN customers c ON t.customer_id = c.id
        WHERE t.id = ?
        LIMIT 1
    ");
    
    $stmt->bind_param('i', $ticketId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!($ticket = $result->fetch_assoc())) {
        throw new Exception('Ticket tidak ditemukan');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $ticket
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
