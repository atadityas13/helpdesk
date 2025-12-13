<?php
/**
 * API: Get FAQs
 * GET /src/api/get-faqs.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $result = $conn->query("
        SELECT id, question, answer, category
        FROM faqs
        WHERE is_active = TRUE
        ORDER BY views DESC, created_at DESC
        LIMIT 50
    ");
    
    $faqs = [];
    while ($row = $result->fetch_assoc()) {
        $faqs[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $faqs,
        'total' => count($faqs)
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
