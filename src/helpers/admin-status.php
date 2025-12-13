<?php
/**
 * Helper: Admin Status Checker
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get online admins
 */
function getOnlineAdmins() {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $result = $conn->query("
            SELECT id, username, last_activity
            FROM admins
            WHERE is_online = TRUE AND is_active = TRUE
            ORDER BY last_activity DESC
        ");
        
        return $result->fetch_all(MYSQLI_ASSOC) ?? [];
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Check if admin is online
 */
function isAdminOnline($adminId) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT is_online FROM admins WHERE id = ? LIMIT 1
        ");
        $stmt->bind_param('i', $adminId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return (bool)$row['is_online'];
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get admin activity
 */
function getAdminActivity($adminId, $limit = 10) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Get unread messages count
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count
            FROM messages
            WHERE ticket_id IN (SELECT id FROM tickets WHERE assigned_to = ?)
            AND is_read = FALSE
        ");
        $stmt->bind_param('i', $adminId);
        $stmt->execute();
        $result = $stmt->get_result();
        $unreadCount = $result->fetch_assoc()['count'] ?? 0;
        
        return ['unread_messages' => $unreadCount];
    } catch (Exception $e) {
        return ['unread_messages' => 0];
    }
}
