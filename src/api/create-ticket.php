<?php
/**
 * API: Create New Ticket
 * Helpdesk MTsN 11 Majalengka
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';
require_once '../helpers/functions.php';
require_once '../helpers/ticket.php';
require_once '../helpers/api-response.php';
require_once '../helpers/validator.php';
require_once '../middleware/rate-limit.php';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Invalid request method', 405);
}

// Check rate limit berdasarkan IP
$clientIp = $_SERVER['REMOTE_ADDR'];
checkRateLimit('create_ticket', $clientIp, $conn);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    errorResponse('Invalid JSON input', 400);
}

// Validate input
$validator = new Validator($input);
$validator
    ->required('name', 'Nama harus diisi')
    ->required('email', 'Email harus diisi')
    ->email('email')
    ->required('subject', 'Subjek harus diisi')
    ->min('subject', 3, 'Subjek minimal 3 karakter')
    ->required('message', 'Pesan harus diisi')
    ->min('message', 5, 'Pesan minimal 5 karakter');

if (!$validator->isValid()) {
    validationErrorResponse($validator->errors());
}

$data = $validator->getData();

// Create ticket
$customerData = [
    'name' => $data['name'],
    'email' => $data['email'],
    'phone' => $data['phone'] ?? ''
];

try {
    $result = createTicket($conn, $customerData, $data['subject'], $data['message']);
    
    if ($result['success']) {
        successResponse('Ticket berhasil dibuat', [
            'ticket_number' => $result['ticket_number'],
            'ticket_id' => $result['ticket_id'],
            'customer_id' => $result['customer_id']
        ], 201);
    } else {
        errorResponse($result['message'] ?? 'Gagal membuat ticket', 400);
    }
} catch (Exception $e) {
    error_log("Error creating ticket: " . $e->getMessage());
    serverErrorResponse('Gagal membuat ticket');
}
?>
