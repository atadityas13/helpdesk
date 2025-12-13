<?php
/**
 * Rate Limiting Middleware
 * Helpdesk MTsN 11 Majalengka
 */

class RateLimiter {
    private $conn;
    private $limits = [
        'login' => ['attempts' => 5, 'window' => 900],           // 5 attempts per 15 minutes
        'create_ticket' => ['attempts' => 10, 'window' => 3600], // 10 per hour
        'send_message' => ['attempts' => 30, 'window' => 3600],  // 30 per hour
    ];
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->createRateLimitTable();
    }
    
    /**
     * Check apakah request masih dalam rate limit
     */
    public function checkLimit($action, $identifier) {
        if (!isset($this->limits[$action])) {
            return true; // No limit set for this action
        }
        
        $limit = $this->limits[$action];
        $now = time();
        $windowStart = $now - $limit['window'];
        
        // Count attempts dalam window
        $query = "SELECT COUNT(*) as count FROM rate_limits 
                 WHERE action = ? AND identifier = ? AND timestamp > ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return true; // Jika error, biarkan request
        }
        
        $stmt->bind_param("ssi", $action, $identifier, $windowStart);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        // Jika sudah melebihi limit
        if ($result['count'] >= $limit['attempts']) {
            return false;
        }
        
        // Record attempt
        $insertQuery = "INSERT INTO rate_limits (action, identifier, timestamp) 
                       VALUES (?, ?, ?)";
        $insertStmt = $this->conn->prepare($insertQuery);
        if ($insertStmt) {
            $insertStmt->bind_param("ssi", $action, $identifier, $now);
            $insertStmt->execute();
            $insertStmt->close();
        }
        
        return true;
    }
    
    /**
     * Create rate_limits table jika belum ada
     */
    private function createRateLimitTable() {
        $query = "CREATE TABLE IF NOT EXISTS rate_limits (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    action VARCHAR(50) NOT NULL,
                    identifier VARCHAR(255) NOT NULL,
                    timestamp INT NOT NULL,
                    INDEX idx_action_identifier (action, identifier),
                    INDEX idx_timestamp (timestamp)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->conn->query($query);
    }
    
    /**
     * Cleanup old rate limit records (lebih dari 1 jam)
     */
    public function cleanup() {
        $query = "DELETE FROM rate_limits WHERE timestamp < ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $oneHourAgo = time() - 3600;
            $stmt->bind_param("i", $oneHourAgo);
            $stmt->execute();
            $stmt->close();
        }
    }
}

/**
 * Check rate limit dan return JSON error jika exceeded
 */
function checkRateLimit($action, $identifier = null, $conn = null) {
    global $conn as $globalConn;
    
    if (!$conn) {
        $conn = $globalConn ?? null;
    }
    
    if (!$conn) {
        return; // Jika tidak ada koneksi, skip
    }
    
    if (!$identifier) {
        $identifier = $_SERVER['REMOTE_ADDR'];
    }
    
    $limiter = new RateLimiter($conn);
    
    if (!$limiter->checkLimit($action, $identifier)) {
        http_response_code(429);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Terlalu banyak request. Silahkan coba lagi dalam beberapa menit.',
            'code' => 'RATE_LIMIT_EXCEEDED'
        ]);
        exit;
    }
    
    // Cleanup old records setiap kali ada request
    if (rand(1, 100) === 1) {
        $limiter->cleanup();
    }
}

?>
