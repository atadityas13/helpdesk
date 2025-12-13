<?php
/**
 * API: Admin Login
 * POST /src/api/login.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/session.php';
require_once __DIR__ . '/../middleware/csrf.php';
require_once __DIR__ . '/../middleware/rate-limit.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/functions.php';

try {
    // Check rate limit
    checkLoginRateLimit();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak allowed');
    }
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        throw new Exception('Email dan password diperlukan');
    }
    
    // Authenticate
    $result = authenticateAdmin($email, $password);
    
    if (!$result['success']) {
        throw new Exception($result['message']);
    }
    
    logAction('admin_login', 'Email: ' . $email, $result['admin_id']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Login berhasil',
        'admin_id' => $result['admin_id']
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
