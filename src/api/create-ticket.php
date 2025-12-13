<?php
/**
 * API: Create New Ticket
 * POST /src/api/create-ticket.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/rate-limit.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/validator.php';
require_once __DIR__ . '/../helpers/ticket.php';

try {
    // Check rate limit
    $clientIP = getClientIP();
    if (!checkTicketRateLimit($clientIP)) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Terlalu banyak request. Silakan coba lagi nanti.']);
        exit;
    }
    
    // Validate POST data
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak allowed');
    }
    
    $required = [
        'name' => ['required', 'minLength:3', 'maxLength:255'],
        'email' => ['required', 'email'],
        'subject' => ['required', 'minLength:5', 'maxLength:255'],
        'message' => ['required', 'minLength:10']
    ];
    
    $data = validatePostData($required);
    
    // Optional fields
    $data['phone'] = $_POST['phone'] ?? null;
    $data['priority'] = $_POST['priority'] ?? 'medium';
    $data['customer_name'] = $data['name'];
    
    // Validate priority
    $allowedPriorities = ['low', 'medium', 'high'];
    if (!in_array($data['priority'], $allowedPriorities)) {
        throw new Exception('Priority tidak valid');
    }
    
    // Create ticket
    $result = createTicket($data);
    
    if (!$result['success']) {
        throw new Exception($result['message']);
    }
    
    // Log action
    logAction('create_ticket', 'Ticket: ' . $result['ticket_number'], $result['customer_id']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Ticket berhasil dibuat',
        'data' => [
            'ticket_id' => $result['ticket_id'],
            'ticket_number' => $result['ticket_number'],
            'customer_id' => $result['customer_id']
        ]
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
