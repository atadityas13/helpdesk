<?php
/**
 * API: Update Ticket Status
 * POST /src/api/update-ticket-status.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/session.php';
require_once __DIR__ . '/../middleware/csrf.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/validator.php';
require_once __DIR__ . '/../helpers/ticket.php';

try {
    // Require admin login
    requireAdminLogin();
    
    // Verify CSRF
    requireValidCsrfToken();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak allowed');
    }
    
    if (!isset($_POST['ticket_id']) || !isset($_POST['status'])) {
        throw new Exception('Data tidak lengkap');
    }
    
    $ticketId = (int)$_POST['ticket_id'];
    $status = sanitizeInput($_POST['status']);
    
    // Validate inputs
    validateInteger($ticketId, 'Ticket ID');
    validateEnum($status, ['open', 'in_progress', 'resolved', 'closed'], 'Status');
    
    // Update ticket
    if (!updateTicketStatus($ticketId, $status)) {
        throw new Exception('Gagal update status ticket');
    }
    
    // Log action
    logAction('update_ticket_status', "Ticket ID: $ticketId, Status: $status");
    
    echo json_encode([
        'success' => true,
        'message' => 'Status ticket berhasil diupdate'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
