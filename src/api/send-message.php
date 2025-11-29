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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

// Check if it's multipart form data (with file) or JSON
$input = [];
if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false) {
    $input = $_POST;
} else {
    $input = json_decode(file_get_contents('php://input'), true);
}

// Validate input
$ticketNumber = sanitizeInput($input['ticket_number'] ?? '');
$message = sanitizeInput($input['message'] ?? '');
$senderType = sanitizeInput($input['sender_type'] ?? 'customer');

if (empty($ticketNumber) || (empty($message) && empty($_FILES['attachment']))) {
    jsonResponse(false, 'Ticket number and message or attachment are required');
}

// Get ticket
$ticket = getTicketByNumber($conn, $ticketNumber);

if (!$ticket) {
    jsonResponse(false, 'Ticket not found');
}

// Determine sender ID
$senderId = null;
if ($senderType === 'customer') {
    $senderId = $ticket['customer_id'];
} else if ($senderType === 'admin') {
    // In real scenario, get from session
    $senderId = $input['sender_id'] ?? 0;
}

// Handle file attachment
$attachmentUrl = null;
if (!empty($_FILES['attachment'])) {
    $file = $_FILES['attachment'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    // Validate file
    if (!in_array($file['type'], $allowedTypes)) {
        jsonResponse(false, 'Only image files are allowed (JPG, PNG, GIF, WebP)');
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB max
        jsonResponse(false, 'File size must not exceed 5MB');
    }
    
    // Create uploads directory if not exists
    $uploadsDir = __DIR__ . '/../../public/uploads';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'attachment_' . $ticket['id'] . '_' . time() . '.' . $ext;
    $filepath = $uploadsDir . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $attachmentUrl = 'public/uploads/' . $filename;
    } else {
        jsonResponse(false, 'Failed to upload file');
    }
}

// Add message
$result = addMessageToTicket($conn, $ticket['id'], $senderType, $senderId, $message, $attachmentUrl);

if ($result['success']) {
    // Update ticket to in_progress if first message from admin
    if ($senderType === 'admin') {
        if ($ticket['status'] === 'open') {
            updateTicketStatus($conn, $ticket['id'], 'in_progress');
        }
    }
    
    jsonResponse(true, 'Message sent successfully', [
        'message_id' => $result['message_id']
    ]);
} else {
    jsonResponse(false, $result['message'] ?? 'Error sending message');
}
?>
