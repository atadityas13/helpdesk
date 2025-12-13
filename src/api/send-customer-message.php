<?php
/**
 * API: Send Customer Message
 * POST /src/api/send-customer-message.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak allowed');
    }
    
    $ticketId = $_POST['ticket_id'] ?? null;
    $message = $_POST['message'] ?? null;
    
    if (!$ticketId || !is_numeric($ticketId)) {
        throw new Exception('Ticket ID tidak valid');
    }
    
    if (!$message || strlen(trim($message)) < 1) {
        throw new Exception('Pesan tidak boleh kosong');
    }
    
    $message = trim($message);
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Verify ticket exists
    $stmt = $conn->prepare("SELECT id, customer_id FROM tickets WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $ticketId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result->fetch_assoc()) {
        throw new Exception('Ticket tidak ditemukan');
    }
    
    $stmt->close();
    
    // Get customer ID
    $stmt = $conn->prepare("SELECT customer_id FROM tickets WHERE id = ?");
    $stmt->bind_param('i', $ticketId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $customerId = $row['customer_id'];
    
    // Insert message
    $senderType = 'customer';
    $stmt = $conn->prepare("
        INSERT INTO messages (ticket_id, sender_type, sender_id, message)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param('isss', $ticketId, $senderType, $customerId, $message);
    
    if (!$stmt->execute()) {
        throw new Exception('Gagal mengirim pesan');
    }
    
    $messageId = $conn->insert_id;
    
    // Update ticket updated_at
    $stmt = $conn->prepare("UPDATE tickets SET updated_at = NOW() WHERE id = ?");
    $stmt->bind_param('i', $ticketId);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Pesan berhasil dikirim',
        'data' => [
            'message_id' => $messageId
        ]
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
