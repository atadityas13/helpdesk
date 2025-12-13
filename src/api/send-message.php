<?php
/**
 * API: Send Message
 * POST /src/api/send-message.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/rate-limit.php';
require_once __DIR__ . '/../middleware/csrf.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/validator.php';

try {
    // Check rate limit
    $clientIP = getClientIP();
    if (!checkMessageRateLimit($clientIP)) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Terlalu banyak request']);
        exit;
    }
    
    // Validate POST data
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak allowed');
    }
    
    if (!isset($_POST['ticket_number']) || !isset($_POST['message'])) {
        throw new Exception('Data tidak lengkap');
    }
    
    $ticketNumber = sanitizeInput($_POST['ticket_number']);
    $message = sanitizeInput($_POST['message']);
    $senderType = 'customer';
    
    // Validate inputs
    validateTicketNumber($ticketNumber);
    if (strlen($message) < 1 || strlen($message) > 5000) {
        throw new Exception('Message tidak valid');
    }
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Get ticket by number
    $stmt = $conn->prepare("SELECT id, customer_id FROM tickets WHERE ticket_number = ? LIMIT 1");
    $stmt->bind_param('s', $ticketNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!($ticket = $result->fetch_assoc())) {
        throw new Exception('Ticket tidak ditemukan');
    }
    
    $ticketId = $ticket['id'];
    $customerId = $ticket['customer_id'];
    
    // Handle file upload
    $attachmentUrl = null;
    if (isset($_FILES['attachment'])) {
        $uploadResult = uploadFile($_FILES['attachment']);
        if ($uploadResult['success']) {
            $attachmentUrl = $uploadResult['filename'];
        }
    }
    
    // Insert message
    $stmt = $conn->prepare("
        INSERT INTO messages (ticket_id, sender_type, sender_id, message, attachment_url)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('isisss', $ticketId, $senderType, $customerId, $message, $attachmentUrl);
    
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
    
    logAction('send_message', 'Ticket: ' . $ticketNumber, $customerId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Pesan berhasil dikirim',
        'data' => [
            'ticket_id' => $ticketId,
            'ticket_number' => $ticketNumber
        ]
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
