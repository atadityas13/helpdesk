<?php
/**
 * Authentication Middleware
 * Handles admin authentication dan login verification
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Authenticate admin dengan username/email dan password
 * @param string $identifier Username atau email
 * @param string $password
 * @return array ['success' => bool, 'message' => string, 'admin_id' => int|null]
 */
function authenticateAdmin($identifier, $password) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Get admin by username OR email
        $stmt = $conn->prepare("
            SELECT id, username, password, email, role, is_active
            FROM admins
            WHERE username = ? OR email = ?
            LIMIT 1
        ");
        
        $stmt->bind_param('ss', $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!($admin = $result->fetch_assoc())) {
            return [
                'success' => false,
                'message' => 'Email/Username atau password salah',
                'admin_id' => null
            ];
        }
        
        // Check if active
        if (!$admin['is_active']) {
            return [
                'success' => false,
                'message' => 'Akun tidak aktif',
                'admin_id' => null
            ];
        }
        
        // Verify password
        if (!password_verify($password, $admin['password'])) {
            return [
                'success' => false,
                'message' => 'Email/Username atau password salah',
                'admin_id' => null
            ];
        }
        
        // Set session
        initSession();
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['last_activity'] = time();
        $_SESSION['timeout'] = time() + SESSION_TIMEOUT;
        
        // Update last activity
        $stmt = $conn->prepare("
            UPDATE admins 
            SET last_activity = NOW(), is_online = TRUE
            WHERE id = ?
        ");
        $stmt->bind_param('i', $admin['id']);
        $stmt->execute();
        
        return [
            'success' => true,
            'message' => 'Login berhasil',
            'admin_id' => $admin['id']
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage(),
            'admin_id' => null
        ];
    }
}

/**
 * Check jika admin memiliki permission
 * @param string $role Required role (e.g., 'admin')
 * @return bool
 */
function requireAdminRole($role) {
    requireAdminLogin();
    
    $currentRole = getAdminRole();
    
    if ($role === 'admin' && $currentRole !== 'admin') {
        http_response_code(403);
        die('Akses ditolak. Admin privileges diperlukan.');
    }
    
    return true;
}

/**
 * Hash password dengan bcrypt
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, [
        'cost' => 10
    ]);
}

/**
 * Set admin as online
 * @param int $adminId
 */
function setAdminOnline($adminId) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE admins 
            SET is_online = TRUE, last_activity = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param('i', $adminId);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Set online error: " . $e->getMessage());
    }
}

/**
 * Set admin as offline
 * @param int $adminId
 */
function setAdminOffline($adminId) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE admins 
            SET is_online = FALSE
            WHERE id = ?
        ");
        $stmt->bind_param('i', $adminId);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Set offline error: " . $e->getMessage());
    }
}

/**
 * Get admin status
 * @param int $adminId
 * @return array ['online' => bool, 'last_activity' => string]
 */
function getAdminStatus($adminId) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT is_online, last_activity
            FROM admins
            WHERE id = ?
            LIMIT 1
        ");
        
        $stmt->bind_param('i', $adminId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return [
                'online' => (bool)$row['is_online'],
                'last_activity' => $row['last_activity']
            ];
        }
        
        return ['online' => false, 'last_activity' => null];
    } catch (Exception $e) {
        return ['online' => false, 'last_activity' => null];
    }
}
