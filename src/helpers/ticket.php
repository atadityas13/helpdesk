<?php
/**
 * Ticket Helper Functions
 * Functions untuk manipulasi ticket data
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Create new ticket
 * @param array $data
 * @return array
 */
function createTicket($data) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Validate data
        if (empty($data['customer_name']) || empty($data['email']) || empty($data['subject']) || empty($data['message'])) {
            throw new Exception('Data tidak lengkap');
        }
        
        // Check if customer exists
        $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $data['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $customerId = $row['id'];
        } else {
            // Create new customer
            $phone = $data['phone'] ?? null;
            $stmt = $conn->prepare("
                INSERT INTO customers (name, email, phone)
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param('sss', $data['customer_name'], $data['email'], $phone);
            
            if (!$stmt->execute()) {
                throw new Exception('Gagal membuat customer record');
            }
            
            $customerId = $conn->insert_id;
        }
        
        // Generate ticket number
        require_once __DIR__ . '/functions.php';
        $ticketNumber = generateTicketNumber();
        
        // Create ticket
        $status = 'open';
        $priority = $data['priority'] ?? 'medium';
        
        $stmt = $conn->prepare("
            INSERT INTO tickets (ticket_number, customer_id, subject, status, priority)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('sisss', $ticketNumber, $customerId, $data['subject'], $status, $priority);
        
        if (!$stmt->execute()) {
            throw new Exception('Gagal membuat ticket');
        }
        
        $ticketId = $conn->insert_id;
        
        // Add first message
        $senderType = 'customer';
        $stmt = $conn->prepare("
            INSERT INTO messages (ticket_id, sender_type, sender_id, message)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param('isss', $ticketId, $senderType, $customerId, $data['message']);
        
        if (!$stmt->execute()) {
            throw new Exception('Gagal membuat message');
        }
        
        return [
            'success' => true,
            'ticket_id' => $ticketId,
            'ticket_number' => $ticketNumber,
            'customer_id' => $customerId,
            'message' => 'Ticket berhasil dibuat'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Get ticket by ID
 * @param int $ticketId
 * @return array|null
 */
function getTicketById($ticketId) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT t.*, c.name, c.email, c.phone
            FROM tickets t
            JOIN customers c ON t.customer_id = c.id
            WHERE t.id = ?
            LIMIT 1
        ");
        
        $stmt->bind_param('i', $ticketId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get ticket by ticket number
 * @param string $ticketNumber
 * @return array|null
 */
function getTicketByNumber($ticketNumber) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT t.*, c.name, c.email, c.phone
            FROM tickets t
            JOIN customers c ON t.customer_id = c.id
            WHERE t.ticket_number = ?
            LIMIT 1
        ");
        
        $stmt->bind_param('s', $ticketNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get all tickets with pagination
 * @param int $page
 * @param int $perPage
 * @param string $status
 * @return array
 */
function getTickets($page = 1, $perPage = 20, $status = null) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $offset = ($page - 1) * $perPage;
        $query = "
            SELECT t.*, c.name, c.email,
                   COUNT(m.id) as message_count,
                   MAX(m.created_at) as last_message_at
            FROM tickets t
            JOIN customers c ON t.customer_id = c.id
            LEFT JOIN messages m ON t.id = m.ticket_id
        ";
        
        if ($status) {
            $query .= " WHERE t.status = ?";
        }
        
        $query .= " GROUP BY t.id ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($query);
        
        if ($status) {
            $stmt->bind_param('sii', $status, $perPage, $offset);
        } else {
            $stmt->bind_param('ii', $perPage, $offset);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Update ticket status
 * @param int $ticketId
 * @param string $status
 * @return bool
 */
function updateTicketStatus($ticketId, $status) {
    try {
        $allowedStatuses = ['open', 'in_progress', 'resolved', 'closed'];
        
        if (!in_array($status, $allowedStatuses)) {
            throw new Exception('Status tidak valid');
        }
        
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE tickets
            SET status = ?
            WHERE id = ?
        ");
        
        $stmt->bind_param('si', $status, $ticketId);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get ticket statistics
 * @return array
 */
function getTicketStatistics() {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $result = $conn->query("
            SELECT 
                COUNT(CASE WHEN status = 'open' THEN 1 END) as open,
                COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress,
                COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved,
                COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed,
                COUNT(*) as total
            FROM tickets
        ");
        
        return $result->fetch_assoc();
    } catch (Exception $e) {
        return [
            'open' => 0,
            'in_progress' => 0,
            'resolved' => 0,
            'closed' => 0,
            'total' => 0
        ];
    }
}
