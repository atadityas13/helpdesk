<?php
/**
 * API: Send Admin Message
 * POST /src/api/send-admin-message.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/session.php';
require_once __DIR__ . '/../middleware/csrf.php';
require_once __DIR__ . '/../helpers/functions.php';

try {
    requireAdminLogin();
    requireValidCsrfToken();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak allowed');
    }
    
    if (!isset($_POST['ticket_id']) || !isset($_POST['message'])) {
        throw new Exception('Data tidak lengkap');
    }
    
    $ticketId = (int)$_POST['ticket_id'];
    $message = sanitizeInput($_POST['message']);
    $adminId = getAdminId();
    $senderType = 'admin';
    
    if (strlen($message) < 1 || strlen($message) > 5000) {
        throw new Exception('Message tidak valid');
    }
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Insert message
    $stmt = $conn->prepare("
        INSERT INTO messages (ticket_id, sender_type, sender_id, message)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param('isis', $ticketId, $senderType, $adminId, $message);
    
    if (!$stmt->execute()) {
        throw new Exception('Gagal mengirim message');
    }
    
    // Update ticket status to in_progress if open
    $stmt = $conn->prepare("
        UPDATE tickets
        SET status = 'in_progress'
        WHERE id = ? AND status = 'open'
    ");
    $stmt->bind_param('i', $ticketId);
    $stmt->execute();
    
    logAction('admin_message', "Ticket ID: $ticketId", $adminId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Pesan berhasil dikirim'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
