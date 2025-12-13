<?php
/**
 * API: Create FAQ
 * POST /src/api/create-faq.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/session.php';
require_once __DIR__ . '/../middleware/csrf.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/validator.php';

try {
    requireAdminRole('admin');
    requireValidCsrfToken();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak allowed');
    }
    
    $data = validatePostData([
        'question' => ['required', 'minLength:5', 'maxLength:255'],
        'answer' => ['required', 'minLength:10'],
        'category' => ['required']
    ]);
    
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        INSERT INTO faqs (question, answer, category, is_active)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param('sssi', $data['question'], $data['answer'], $data['category'], $isActive);
    
    if (!$stmt->execute()) {
        throw new Exception('Gagal membuat FAQ');
    }
    
    logAction('create_faq', 'Question: ' . $data['question']);
    
    echo json_encode([
        'success' => true,
        'message' => 'FAQ berhasil dibuat'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
