<?php
/**
 * API: Update Ticket Status
 * Helpdesk MTsN 11 Majalengka
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';
require_once '../middleware/session.php';
require_once '../helpers/functions.php';
require_once '../helpers/ticket.php';
require_once '../helpers/api-response.php';
require_once '../helpers/validator.php';

// Require admin login
requireAdminLogin();

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Invalid request method', 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Validate input
$validator = new Validator($input);
$validator
    ->required('ticket_id', 'ID ticket harus diisi')
    ->numeric('ticket_id', 'ID ticket harus berupa angka')
    ->required('status', 'Status harus diisi');

if (!$validator->isValid()) {
    validationErrorResponse($validator->errors());
}

$data = $validator->getData();
$ticketId = $data['ticket_id'];
$status = $data['status'];

// Validate status value
$validStatuses = ['open', 'in_progress', 'resolved', 'closed'];
if (!in_array($status, $validStatuses)) {
    errorResponse('Status tidak valid', 400);
}

// Update status
try {
    $result = updateTicketStatus($conn, $ticketId, $status);
    
    if ($result['success']) {
        successResponse('Status ticket berhasil diubah');
    } else {
        errorResponse($result['message'] ?? 'Gagal mengubah status ticket', 400);
    }
} catch (Exception $e) {
    error_log("Error updating ticket status: " . $e->getMessage());
    serverErrorResponse();
}
?>

