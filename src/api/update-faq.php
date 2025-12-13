<?php
/**
 * API: Update FAQ
 * POST /src/api/update-faq.php
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
    
    if (!isset($_POST['id'])) {
        throw new Exception('FAQ ID diperlukan');
    }
    
    $id = (int)$_POST['id'];
    
    $data = validatePostData([
        'question' => ['required', 'minLength:5', 'maxLength:255'],
        'answer' => ['required', 'minLength:10'],
        'category' => ['required']
    ]);
    
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        UPDATE faqs
        SET question = ?, answer = ?, category = ?, is_active = ?
        WHERE id = ?
    ");
    $stmt->bind_param('sssii', $data['question'], $data['answer'], $data['category'], $isActive, $id);
    
    if (!$stmt->execute()) {
        throw new Exception('Gagal update FAQ');
    }
    
    logAction('update_faq', "FAQ ID: $id");
    
    echo json_encode([
        'success' => true,
        'message' => 'FAQ berhasil diupdate'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
