<?php
/**
 * API: Mark Messages as Read
 * Helpdesk MTsN 11 Majalengka
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';
require_once '../helpers/functions.php';
require_once '../helpers/ticket.php';
require_once '../helpers/api-response.php';
require_once '../helpers/validator.php';
require_once '../middleware/session.php';

initSession();

// Handle POST request - mark messages as read
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Invalid request method', 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Validate
$validator = new Validator($input);
$validator->required('ticket_number', 'Nomor ticket harus diisi');

if (!$validator->isValid()) {
    validationErrorResponse($validator->errors());
}

$data = $validator->getData();
$ticketNumber = $data['ticket_number'];
$viewerType = $input['viewer_type'] ?? 'customer'; // 'customer' atau 'admin'

// Get ticket
$ticket = getTicketByNumber($conn, $ticketNumber);

if (!$ticket) {
    notFoundResponse('Ticket tidak ditemukan');
}

// Logic:
// - Customer viewing → mark admin messages as read
// - Admin viewing → mark customer messages as read

if ($viewerType === 'customer') {
    // Customer marking admin messages as read
    $senderTypeToMark = 'admin';
    
    $query = "UPDATE messages 
              SET is_read = TRUE 
              WHERE ticket_id = ? 
              AND sender_type = ? 
              AND is_read = FALSE";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Database prepare failed: " . $conn->error);
        serverErrorResponse();
    }
    
    $stmt->bind_param("is", $ticket['id'], $senderTypeToMark);
    
    if ($stmt->execute()) {
        $stmt->close();
        successResponse('Pesan sudah ditandai sebagai dibaca');
    } else {
        error_log("Database execute failed: " . $stmt->error);
        $stmt->close();
        serverErrorResponse();
    }
} else {
    // Admin marking customer messages as read
    $senderTypeToMark = 'customer';
    
    $query = "UPDATE messages 
              SET is_read = TRUE 
              WHERE ticket_id = ? 
              AND sender_type = ? 
              AND is_read = FALSE";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Database prepare failed: " . $conn->error);
        serverErrorResponse();
    }
    
    $stmt->bind_param("is", $ticket['id'], $senderTypeToMark);
    
    if ($stmt->execute()) {
        $stmt->close();
        successResponse('Pesan sudah ditandai sebagai dibaca');
    } else {
        error_log("Database execute failed: " . $stmt->error);
        $stmt->close();
        serverErrorResponse();
    }
}
?>
;
    }
}

else {
    jsonResponse(false, 'Invalid request method');
}
?>
