<?php
/**
 * API: Get Admin Status
 * Helpdesk MTsN 11 Majalengka
 */

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../helpers/functions.php';
require_once '../helpers/ticket.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $ticketNumber = sanitizeInput($_GET['ticket_number'] ?? '');
    
    if (empty($ticketNumber)) {
        jsonResponse(false, 'Ticket number is required');
    }
    
    $ticket = getTicketByNumber($conn, $ticketNumber);
    
    if (!$ticket) {
        jsonResponse(false, 'Ticket not found');
    }
    
    // Check admin_viewing dengan kondisi yang lebih fleksibel:
    // 1. last_view dalam 30 detik terakhir
    // 2. Hanya satu kondisi untuk menghindari timezone issues
    $viewingQuery = "SELECT a.id, a.username, a.name, av.last_view
                     FROM admin_viewing av
                     JOIN admins a ON av.admin_id = a.id
                     WHERE av.ticket_number = ? 
                     AND av.last_view > DATE_SUB(NOW(), INTERVAL 30 SECOND)
                     ORDER BY av.last_view DESC
                     LIMIT 1";
    
    $viewStmt = $conn->prepare($viewingQuery);
    
    if (!$viewStmt) {
        error_log("Prepare failed: " . $conn->error);
        jsonResponse(false, 'Database error');
    }
    
    $viewStmt->bind_param("s", $ticketNumber);
    
    if (!$viewStmt->execute()) {
        error_log("Execute failed: " . $viewStmt->error);
        jsonResponse(false, 'Error fetching admin info');
    }
    
    $viewResult = $viewStmt->get_result()->fetch_assoc();
    $viewStmt->close();
    
    if ($viewResult) {
        // Admin SEDANG membuka chat ini
        jsonResponse(true, 'Admin status fetched', [
            'admin_id' => $viewResult['id'],
            'admin_name' => $viewResult['name'] ?? $viewResult['username'],
            'is_connected' => true,
            'status' => 'actively_viewing',
            'last_activity' => $viewResult['last_view']
        ]);
    } else {
        // Check if there's an admin who has interacted with this ticket before
        $messageQuery = "SELECT a.id, a.username, a.name, m.created_at
                         FROM messages m
                         JOIN admins a ON m.sender_id = a.id
                         WHERE m.ticket_id = ? AND m.sender_type = 'admin'
                         ORDER BY m.created_at DESC
                         LIMIT 1";
        
        $msgStmt = $conn->prepare($messageQuery);
        
        if (!$msgStmt) {
            error_log("Prepare failed: " . $conn->error);
            jsonResponse(false, 'Database error');
        }
        
        $msgStmt->bind_param("i", $ticket['id']);
        
        if (!$msgStmt->execute()) {
            error_log("Execute failed: " . $msgStmt->error);
            jsonResponse(false, 'Error fetching admin info');
        }
        
        $msgResult = $msgStmt->get_result()->fetch_assoc();
        $msgStmt->close();
        
        if ($msgResult) {
            // Admin pernah handle tapi tidak sedang membuka
            jsonResponse(true, 'Admin status fetched', [
                'admin_id' => $msgResult['id'],
                'admin_name' => $msgResult['name'] ?? $msgResult['username'],
                'is_connected' => false,
                'status' => 'previously_handled',
                'last_activity' => $msgResult['created_at']
            ]);
        } else {
            // Belum ada admin
            jsonResponse(true, 'No admin assigned yet', [
                'admin_id' => null,
                'admin_name' => null,
                'is_connected' => false,
                'status' => 'no_admin',
                'last_activity' => null
            ]);
        }
    }
    
} else {
    jsonResponse(false, 'Invalid request method');
}
?>
