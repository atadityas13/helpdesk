<?php
/**
 * API: Delete FAQ
 * POST /src/api/delete-faq.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/session.php';
require_once __DIR__ . '/../middleware/csrf.php';
require_once __DIR__ . '/../helpers/functions.php';

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
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("DELETE FROM faqs WHERE id = ?");
    $stmt->bind_param('i', $id);
    
    if (!$stmt->execute()) {
        throw new Exception('Gagal menghapus FAQ');
    }
    
    logAction('delete_faq', "FAQ ID: $id");
    
    echo json_encode([
        'success' => true,
        'message' => 'FAQ berhasil dihapus'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
