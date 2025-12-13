<?php
/**
 * Rate Limiting Middleware
 * Prevent abuse dengan membatasi requests per action
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Check rate limit
 * @param string $action Action identifier (e.g., 'login', 'ticket', 'message')
 * @param string $identifier IP address atau customer_id
 * @param int $limit Maximum allowed requests
 * @param int $window Time window dalam detik
 * @return array ['allowed' => bool, 'remaining' => int, 'retry_after' => int]
 */
function checkRateLimit($action, $identifier, $limit = 5, $window = 3600) {
    if (!defined('ENABLE_RATE_LIMIT') || !ENABLE_RATE_LIMIT) {
        return ['allowed' => true, 'remaining' => $limit, 'retry_after' => 0];
    }
    
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Clean expired records
        $conn->query("DELETE FROM rate_limits WHERE expires_at < NOW()");
        
        // Get current count
        $stmt = $conn->prepare("
            SELECT id, count, expires_at 
            FROM rate_limits 
            WHERE action = ? AND identifier = ? AND expires_at > NOW()
            LIMIT 1
        ");
        
        $stmt->bind_param('ss', $action, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $count = $row['count'];
            $expires_at = strtotime($row['expires_at']);
            
            if ($count >= $limit) {
                $retry_after = max(0, $expires_at - time());
                return [
                    'allowed' => false,
                    'remaining' => 0,
                    'retry_after' => $retry_after
                ];
            }
            
            // Increment count
            $stmt = $conn->prepare("
                UPDATE rate_limits 
                SET count = count + 1 
                WHERE action = ? AND identifier = ?
            ");
            $stmt->bind_param('ss', $action, $identifier);
            $stmt->execute();
            
            return [
                'allowed' => true,
                'remaining' => $limit - ($count + 1),
                'retry_after' => 0
            ];
        } else {
            // Create new record
            $expires_at = date('Y-m-d H:i:s', time() + $window);
            $stmt = $conn->prepare("
                INSERT INTO rate_limits (action, identifier, count, expires_at)
                VALUES (?, ?, 1, ?)
            ");
            $stmt->bind_param('sss', $action, $identifier, $expires_at);
            $stmt->execute();
            
            return [
                'allowed' => true,
                'remaining' => $limit - 1,
                'retry_after' => 0
            ];
        }
    } catch (Exception $e) {
        // If database error, allow request
        error_log("Rate limit check error: " . $e->getMessage());
        return ['allowed' => true, 'remaining' => $limit, 'retry_after' => 0];
    }
}

/**
 * Check login rate limit
 * @return bool
 */
function checkLoginRateLimit() {
    $clientIp = getClientIP();
    $limit = defined('RATE_LIMIT_LOGIN') ? RATE_LIMIT_LOGIN : 5;
    
    $result = checkRateLimit('login', $clientIp, $limit, 900); // 15 minutes
    
    if (!$result['allowed']) {
        http_response_code(429);
        die(json_encode([
            'success' => false,
            'message' => 'Terlalu banyak attempt login. Silakan coba lagi dalam ' . ceil($result['retry_after'] / 60) . ' menit.'
        ]));
    }
    
    return true;
}

/**
 * Check ticket creation rate limit
 * @param string|int $identifier Customer ID atau IP
 * @return bool
 */
function checkTicketRateLimit($identifier) {
    $limit = defined('RATE_LIMIT_TICKET') ? RATE_LIMIT_TICKET : 3;
    
    $result = checkRateLimit('ticket', (string)$identifier, $limit, 3600); // 1 hour
    
    if (!$result['allowed']) {
        return false;
    }
    
    return true;
}

/**
 * Check message rate limit
 * @param string|int $identifier Customer ID atau IP
 * @return bool
 */
function checkMessageRateLimit($identifier) {
    $limit = defined('RATE_LIMIT_MESSAGE') ? RATE_LIMIT_MESSAGE : 10;
    
    $result = checkRateLimit('message', (string)$identifier, $limit, 300); // 5 minutes
    
    if (!$result['allowed']) {
        return false;
    }
    
    return true;
}

/**
 * Get client IP address
 * @return string
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    return filter_var($ip, FILTER_VALIDATE_IP) ?: '0.0.0.0';
}
