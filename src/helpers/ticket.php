<?php
/**
 * Ticket Helper Functions
 * Helpdesk MTsN 11 Majalengka
 */

/**
 * Create new ticket
 */
function createTicket($conn, $customerData, $subject, $message) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert or get customer
        $customerQuery = "SELECT id FROM customers WHERE email = ?";
        $stmt = $conn->prepare($customerQuery);
        $stmt->bind_param("s", $customerData['email']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result) {
            $customerId = $result['id'];
        } else {
            // Insert new customer
            $insertCustomer = "INSERT INTO customers (name, email, phone) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertCustomer);
            $stmt->bind_param("sss", $customerData['name'], $customerData['email'], $customerData['phone']);
            $stmt->execute();
            $customerId = $conn->insert_id;
        }
        
        // Generate ticket number
        $ticketNumber = generateTicketNumber();
        
        // Insert ticket
        $insertTicket = "INSERT INTO tickets (ticket_number, customer_id, subject, status, priority) 
                        VALUES (?, ?, ?, 'open', 'medium')";
        $stmt = $conn->prepare($insertTicket);
        $stmt->bind_param("sis", $ticketNumber, $customerId, $subject);
        $stmt->execute();
        $ticketId = $conn->insert_id;
        
        // Insert first message
        $insertMessage = "INSERT INTO messages (ticket_id, sender_type, sender_id, message) 
                         VALUES (?, 'customer', ?, ?)";
        $stmt = $conn->prepare($insertMessage);
        $stmt->bind_param("iis", $ticketId, $customerId, $message);
        $stmt->execute();
        
        $conn->commit();
        
        return [
            'success' => true,
            'ticket_id' => $ticketId,
            'ticket_number' => $ticketNumber,
            'customer_id' => $customerId
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        logError("Error creating ticket: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error creating ticket'
        ];
    }
}

/**
 * Get ticket messages
 */
function getTicketMessages($conn, $ticketId) {
    $query = "SELECT m.*, 
                     CASE WHEN m.sender_type = 'customer' THEN c.name ELSE a.username END as sender_name
              FROM messages m
              LEFT JOIN customers c ON m.sender_type = 'customer' AND m.sender_id = c.id
              LEFT JOIN admins a ON m.sender_type = 'admin' AND m.sender_id = a.id
              WHERE m.ticket_id = ?
              ORDER BY m.created_at ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ticketId);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Add message to ticket
 */
function addMessageToTicket($conn, $ticketId, $senderType, $senderId, $message, $attachmentUrl = null) {
    $query = "INSERT INTO messages (ticket_id, sender_type, sender_id, message, attachment_url) 
             VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isiss", $ticketId, $senderType, $senderId, $message, $attachmentUrl);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'message_id' => $conn->insert_id
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Error adding message'
        ];
    }
}

/**
 * Update ticket status
 */
function updateTicketStatus($conn, $ticketId, $status) {
    $validStatuses = ['open', 'in_progress', 'resolved', 'closed'];
    
    if (!in_array($status, $validStatuses)) {
        return ['success' => false, 'message' => 'Invalid status'];
    }
    
    $query = "UPDATE tickets SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $ticketId);
    
    if ($stmt->execute()) {
        return ['success' => true];
    } else {
        return ['success' => false, 'message' => 'Error updating status'];
    }
}

/**
 * Close ticket
 */
function closeTicket($conn, $ticketId) {
    return updateTicketStatus($conn, $ticketId, 'closed');
}
?>
