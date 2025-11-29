<?php
/**
 * API: Send Message
 * Helpdesk MTsN 11 Majalengka
 */

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../helpers/functions.php';
require_once '../helpers/ticket.php';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input data (either from FormData or JSON)
    $input = [];
    
    if (!empty($_POST)) {
        // FormData dari browser
        $input = $_POST;
        if (isset($_FILES['attachment'])) {
            $input['attachment'] = $_FILES['attachment'];
        }
    } else {
        // JSON dari API
        $input = json_decode(file_get_contents('php://input'), true);
    }
    
    $ticketNumber = sanitizeInput($input['ticket_number'] ?? '');
    $message = sanitizeInput($input['message'] ?? '');
    $senderType = $input['sender_type'] ?? '';
    $attachmentUrl = null;
    
    // Validasi input
    if (empty($ticketNumber) || empty($message)) {
        jsonResponse(false, 'Ticket number dan message harus diisi');
    }
    
    if (empty($senderType) || !in_array($senderType, ['customer', 'admin'])) {
        jsonResponse(false, 'Invalid sender type');
    }
    
    // Get ticket by number
    $ticket = getTicketByNumber($conn, $ticketNumber);
    
    if (!$ticket) {
        jsonResponse(false, 'Ticket tidak ditemukan');
    }
    
    // Handle file attachment jika ada
    if (!empty($input['attachment']) && is_array($input['attachment'])) {
        $file = $input['attachment'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (in_array($file['type'], $allowedTypes) && $file['size'] <= 5 * 1024 * 1024) {
            $uploadsDir = __DIR__ . '/../../public/uploads';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }
            
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'attachment_' . $ticket['id'] . '_' . time() . '.' . $ext;
            $filepath = $uploadsDir . '/' . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $attachmentUrl = 'public/uploads/' . $filename;
            }
        }
    }
    
    // Insert message dengan sender_type yang benar
    $query = "INSERT INTO messages (ticket_id, sender_type, sender_id, message, attachment_url, created_at) 
              VALUES (?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        jsonResponse(false, 'Database error');
    }
    
    // sender_id: gunakan session admin_id jika admin, gunakan 0 untuk customer
    $senderId = 0;
    if ($senderType === 'admin' && isset($_SESSION['admin_id'])) {
        $senderId = $_SESSION['admin_id'];
    }
    
    $stmt->bind_param("isiss", $ticket['id'], $senderType, $senderId, $message, $attachmentUrl);
    
    if ($stmt->execute()) {
        $messageId = $conn->insert_id;
        
        // Update ticket updated_at
        $updateQuery = "UPDATE tickets SET updated_at = NOW() WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("i", $ticket['id']);
        $updateStmt->execute();
        $updateStmt->close();
        
        jsonResponse(true, 'Message sent successfully', [
            'message_id' => $messageId,
            'sender_type' => $senderType
        ]);
    } else {
        error_log("Execute failed: " . $stmt->error);
        jsonResponse(false, 'Error saving message');
    }
    
} else {
    jsonResponse(false, 'Invalid request method');
}
?>
