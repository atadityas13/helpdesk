<?php
/**
 * API: Create New Ticket
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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$name = sanitizeInput($input['name'] ?? '');
$email = sanitizeInput($input['email'] ?? '');
$phone = sanitizeInput($input['phone'] ?? '');
$subject = sanitizeInput($input['subject'] ?? '');
$message = sanitizeInput($input['message'] ?? '');

if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    jsonResponse(false, 'All fields are required');
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Invalid email format');
}

// Create ticket
$customerData = [
    'name' => $name,
    'email' => $email,
    'phone' => $phone
];

$result = createTicket($conn, $customerData, $subject, $message);

if ($result['success']) {
    jsonResponse(true, 'Ticket created successfully', [
        'ticket_number' => $result['ticket_number'],
        'ticket_id' => $result['ticket_id'],
        'customer_id' => $result['customer_id']
    ]);
} else {
    jsonResponse(false, $result['message'] ?? 'Error creating ticket');
}
?>
