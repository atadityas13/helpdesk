<?php
/**
 * API: Send Message
 * Helpdesk MTsN 11 Majalengka
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';
require_once '../helpers/functions.php';
require_once '../helpers/ticket.php';
require_once '../helpers/api-response.php';
require_once '../helpers/validator.php';
require_once '../middleware/rate-limit.php';
require_once '../middleware/session.php';

// Initialize session untuk check admin login
initSession();

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Invalid request method', 405);
}

// Check rate limit
$clientIp = $_SERVER['REMOTE_ADDR'];
checkRateLimit('send_message', $clientIp, $conn);

// Get input data
$input = [];

if (!empty($_POST)) {
    $input = $_POST;
    if (isset($_FILES['attachment'])) {
        $input['attachment'] = $_FILES['attachment'];
    }
} else {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
}

// Validate input
$validator = new Validator($input);
$validator
    ->required('ticket_number', 'Nomor ticket harus diisi')
    ->required('message', 'Pesan harus diisi');

if (!$validator->isValid()) {
    validationErrorResponse($validator->errors());
}

$data = $validator->getData();
$ticketNumber = $data['ticket_number'];
$message = $data['message'];
$senderType = $input['sender_type'] ?? 'customer';

// Validate sender type
if (!in_array($senderType, ['customer', 'admin'])) {
    errorResponse('Tipe pengirim tidak valid', 400);
}

// Get ticket
$ticket = getTicketByNumber($conn, $ticketNumber);
if (!$ticket) {
    notFoundResponse('Ticket tidak ditemukan');
}

$attachmentUrl = null;

// Handle file attachment
if (!empty($input['attachment']) && is_array($input['attachment'])) {
    $file = $input['attachment'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = MAX_UPLOAD_SIZE;
    
    if (!in_array($file['type'], $allowedTypes)) {
        errorResponse('Tipe file tidak didukung', 400);
    }
    
    if ($file['size'] > $maxSize) {
        errorResponse('Ukuran file terlalu besar', 400);
    }
    
    $uploadsDir = __DIR__ . '/../../public/uploads';
    if (!is_dir($uploadsDir)) {
        if (!mkdir($uploadsDir, 0755, true)) {
            serverErrorResponse('Gagal menyimpan file');
        }
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'msg_' . $ticket['id'] . '_' . time() . '.' . $ext;
    $filepath = $uploadsDir . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $attachmentUrl = 'public/uploads/' . $filename;
    } else {
        serverErrorResponse('Gagal menyimpan file');
    }
}

// Insert message
$query = "INSERT INTO messages (ticket_id, sender_type, sender_id, message, attachment_url, created_at) 
          VALUES (?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($query);
if (!$stmt) {
    error_log("Database prepare failed: " . $conn->error);
    serverErrorResponse();
}

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
    if ($updateStmt) {
        $updateStmt->bind_param("i", $ticket['id']);
        $updateStmt->execute();
        $updateStmt->close();
    }
    
    $stmt->close();
    
    successResponse('Pesan berhasil dikirim', [
        'message_id' => $messageId,
        'sender_type' => $senderType
    ], 201);
} else {
    error_log("Database execute failed: " . $stmt->error);
    $stmt->close();
    serverErrorResponse();
}
?>

