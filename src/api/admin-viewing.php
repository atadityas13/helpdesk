<?php
/**
 * API: Track Admin Viewing Ticket
 * Helpdesk MTsN 11 Majalengka
 */

header('Content-Type: application/json');
session_start();

require_once '../config/database.php';
require_once '../helpers/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Support both Content-Type: application/json dan text/plain (untuk sendBeacon)
    $input = null;
    
    if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
    } elseif (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'text/plain') !== false) {
        // sendBeacon mengirim dengan Content-Type text/plain
        $input = json_decode(file_get_contents('php://input'), true);
    } else {
        $input = $_POST;
    }
    
    $ticketNumber = sanitizeInput($input['ticket_number'] ?? '');
    $isViewing = $input['is_viewing'] ?? false;
    
    if (empty($ticketNumber) || !isset($_SESSION['admin_id'])) {
        // Tetap return JSON untuk sendBeacon
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    $adminId = $_SESSION['admin_id'];
    
    if ($isViewing) {
        // Cleanup records lama dari admin lain (on-demand)
        $cleanupQuery = "DELETE FROM admin_viewing 
                         WHERE last_view < DATE_SUB(NOW(), INTERVAL 30 SECOND)
                         AND admin_id != ?";
        
        $cleanupStmt = $conn->prepare($cleanupQuery);
        $cleanupStmt->bind_param("i", $adminId);
        $cleanupStmt->execute();
        $cleanupStmt->close();
        
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
        
        jsonResponse(true, 'Admin viewing status updated');
    } else {
        // Remove viewing record - LANGSUNG hapus
        $deleteQuery = "DELETE FROM admin_viewing WHERE admin_id = ? AND ticket_number = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("is", $adminId, $ticketNumber);
        $result = $deleteStmt->execute();
        $deleteStmt->close();
        
        jsonResponse(true, 'Admin viewing status removed');
    }
    
} else {
    jsonResponse(false, 'Invalid request method');
}
?>
