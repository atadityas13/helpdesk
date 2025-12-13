<?php
/**
 * API: Get FAQ
 * GET /src/api/get-faq.php?id=1
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/session.php';

try {
    requireAdminLogin();
    
    if (!isset($_GET['id'])) {
        throw new Exception('FAQ ID diperlukan');
    }
    
    $faqId = (int)$_GET['id'];
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM faqs WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $faqId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!($faq = $result->fetch_assoc())) {
        throw new Exception('FAQ tidak ditemukan');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $faq
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
