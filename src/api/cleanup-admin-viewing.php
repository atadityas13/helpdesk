<?php
/**
 * API: Cleanup Admin Viewing Records
 * Helpdesk MTsN 11 Majalengka
 * 
 * Dipanggil ketika ada admin baru view ticket
 * Cleanup records yang sudah lebih dari 30 detik
 */

header('Content-Type: application/json');
session_start();

require_once '../config/database.php';
require_once '../helpers/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['admin_id'])) {
        jsonResponse(false, 'Unauthorized');
    }
    
    try {
        // Clean up records older than 30 seconds
        // Hanya delete records yang BUKAN milik admin yang sedang aktif
        $cleanupQuery = "DELETE FROM admin_viewing 
                         WHERE last_view < DATE_SUB(NOW(), INTERVAL 30 SECOND)
                         AND admin_id != ?";
        
        $stmt = $conn->prepare($cleanupQuery);
        $stmt->bind_param("i", $_SESSION['admin_id']);
        $result = $stmt->execute();
        $stmt->close();
        
        if ($result) {
            jsonResponse(true, 'Cleanup completed');
        } else {
            jsonResponse(false, 'Cleanup failed');
        }
    } catch (Exception $e) {
        error_log("Cleanup error: " . $e->getMessage());
        jsonResponse(false, 'Database error');
    }
} else {
    jsonResponse(false, 'Invalid request method');
}
?>
