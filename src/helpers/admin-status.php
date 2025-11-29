<?php
/**
 * Admin Status Helper Functions
 * Helpdesk MTsN 11 Majalengka
 * 
 * Unified system untuk tracking admin viewing dan message read status
 */

/**
 * Track admin viewing - update atau insert record
 * @param $conn Database connection
 * @param $adminId Admin ID
 * @param $ticketNumber Ticket number
 * @param $isViewing Boolean - true untuk viewing, false untuk stop viewing
 */
function trackAdminViewing($conn, $adminId, $ticketNumber, $isViewing = true) {
    if ($isViewing) {
        // Check if record exists
        $checkQuery = "SELECT id FROM admin_viewing WHERE admin_id = ? AND ticket_number = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("is", $adminId, $ticketNumber);
        $checkStmt->execute();
        $existingRecord = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();
        
        if ($existingRecord) {
            // Update timestamp
            $updateQuery = "UPDATE admin_viewing SET last_view = NOW() WHERE admin_id = ? AND ticket_number = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("is", $adminId, $ticketNumber);
            $result = $updateStmt->execute();
            $updateStmt->close();
        } else {
            // Insert new record
            $insertQuery = "INSERT INTO admin_viewing (admin_id, ticket_number, last_view) VALUES (?, ?, NOW())";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("is", $adminId, $ticketNumber);
            $result = $insertStmt->execute();
            $insertStmt->close();
        }
        
        // Auto mark messages as read when admin views
        markMessagesAsReadByAdmin($conn, $ticketNumber);
        
        return ['success' => true, 'message' => 'Admin viewing tracked'];
    } else {
        // Remove viewing record
        $deleteQuery = "DELETE FROM admin_viewing WHERE admin_id = ? AND ticket_number = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("is", $adminId, $ticketNumber);
        $result = $deleteStmt->execute();
        $deleteStmt->close();
        
        return ['success' => true, 'message' => 'Admin viewing stopped'];
    }
}

/**
 * Get admin viewing status untuk ticket
 * @param $conn Database connection
 * @param $ticketNumber Ticket number
 * @return Array dengan admin info atau null
 */
function getAdminViewingStatus($conn, $ticketNumber) {
    // Cleanup old records (older than 35 seconds)
    $cleanupQuery = "DELETE FROM admin_viewing WHERE last_view < DATE_SUB(NOW(), INTERVAL 35 SECOND)";
    $conn->query($cleanupQuery);
    
    // Get current viewing admin
    $viewingQuery = "SELECT a.id, a.username, a.name, av.last_view
                     FROM admin_viewing av
                     JOIN admins a ON av.admin_id = a.id
                     WHERE av.ticket_number = ? 
                     AND av.last_view > DATE_SUB(NOW(), INTERVAL 35 SECOND)
                     ORDER BY av.last_view DESC
                     LIMIT 1";
    
    $viewStmt = $conn->prepare($viewingQuery);
    $viewStmt->bind_param("s", $ticketNumber);
    $viewStmt->execute();
    $viewResult = $viewStmt->get_result()->fetch_assoc();
    $viewStmt->close();
    
    return $viewResult;
}

/**
 * Mark messages as read by admin
 * @param $conn Database connection
 * @param $ticketNumber Ticket number
 */
function markMessagesAsReadByAdmin($conn, $ticketNumber) {
    // Get ticket ID
    $ticketQuery = "SELECT id FROM tickets WHERE ticket_number = ?";
    $ticketStmt = $conn->prepare($ticketQuery);
    $ticketStmt->bind_param("s", $ticketNumber);
    $ticketStmt->execute();
    $ticketResult = $ticketStmt->get_result()->fetch_assoc();
    $ticketStmt->close();
    
    if (!$ticketResult) {
        return ['success' => false, 'message' => 'Ticket not found'];
    }
    
    $ticketId = $ticketResult['id'];
    
    // Mark customer messages as read
    $updateQuery = "UPDATE messages 
                    SET is_read = TRUE 
                    WHERE ticket_id = ? 
                    AND sender_type = 'customer' 
                    AND is_read = FALSE";
    
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $ticketId);
    $result = $updateStmt->execute();
    $updateStmt->close();
    
    return ['success' => $result, 'message' => 'Messages marked as read'];
}

/**
 * Get admin status untuk customer view
 * @param $conn Database connection
 * @param $ticketNumber Ticket number
 * @return Array dengan status info
 */
function getAdminStatusForCustomer($conn, $ticketNumber) {
    // Get ticket
    $ticketQuery = "SELECT id FROM tickets WHERE ticket_number = ?";
    $ticketStmt = $conn->prepare($ticketQuery);
    $ticketStmt->bind_param("s", $ticketNumber);
    $ticketStmt->execute();
    $ticketResult = $ticketStmt->get_result()->fetch_assoc();
    $ticketStmt->close();
    
    if (!$ticketResult) {
        return [
            'admin_id' => null,
            'admin_name' => null,
            'is_connected' => false,
            'status' => 'no_ticket'
        ];
    }
    
    $ticketId = $ticketResult['id'];
    
    // Check if admin is currently viewing
    $viewingAdmin = getAdminViewingStatus($conn, $ticketNumber);
    
    if ($viewingAdmin) {
        return [
            'admin_id' => $viewingAdmin['id'],
            'admin_name' => $viewingAdmin['name'] ?? $viewingAdmin['username'],
            'is_connected' => true,
            'status' => 'actively_viewing',
            'last_activity' => $viewingAdmin['last_view']
        ];
    }
    
    // Check if there's an admin who has interacted with this ticket
    $messageQuery = "SELECT a.id, a.username, a.name, m.created_at
                     FROM messages m
                     JOIN admins a ON m.sender_id = a.id
                     WHERE m.ticket_id = ? AND m.sender_type = 'admin'
                     ORDER BY m.created_at DESC
                     LIMIT 1";
    
    $msgStmt = $conn->prepare($messageQuery);
    $msgStmt->bind_param("i", $ticketId);
    $msgStmt->execute();
    $msgResult = $msgStmt->get_result()->fetch_assoc();
    $msgStmt->close();
    
    if ($msgResult) {
        return [
            'admin_id' => $msgResult['id'],
            'admin_name' => $msgResult['name'] ?? $msgResult['username'],
            'is_connected' => false,
            'status' => 'previously_handled',
            'last_activity' => $msgResult['created_at']
        ];
    }
    
    return [
        'admin_id' => null,
        'admin_name' => null,
        'is_connected' => false,
        'status' => 'no_admin'
    ];
}

/**
 * Cleanup old admin viewing records
 * @param $conn Database connection
 */
function cleanupAdminViewing($conn) {
    $query = "DELETE FROM admin_viewing WHERE last_view < DATE_SUB(NOW(), INTERVAL 35 SECOND)";
    return $conn->query($query);
}
?>
