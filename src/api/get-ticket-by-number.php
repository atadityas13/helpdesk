<?php
/**
 * API: Get Ticket by Number
 * GET /src/api/get-ticket-by-number.php?ticket_number=TK-XXXXX
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/ticket.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method tidak allowed');
    }
    
    $ticketNumber = $_GET['ticket_number'] ?? null;
    
    if (!$ticketNumber) {
        throw new Exception('Nomor ticket harus diisi');
    }
    
    $ticket = getTicketByNumber($ticketNumber);
    
    if (!$ticket) {
        throw new Exception('Ticket tidak ditemukan');
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'ticket' => [
                'id' => $ticket['id'],
                'ticket_number' => $ticket['ticket_number'],
                'subject' => $ticket['subject'],
                'status' => $ticket['status'],
                'priority' => $ticket['priority'],
                'customer_name' => $ticket['name'],
                'customer_email' => $ticket['email'],
                'created_at' => $ticket['created_at'],
                'updated_at' => $ticket['updated_at']
            ]
        ]
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
