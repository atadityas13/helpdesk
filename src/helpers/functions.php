<?php
/**
 * General Helper Functions
 * Utility functions untuk aplikasi
 */

/**
 * Sanitize input string
 * @param string $input
 * @return string
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (simple validation)
 * @param string $phone
 * @return bool
 */
function isValidPhone($phone) {
    // Format: 08xx atau +62xx
    return preg_match('/^(\+62|0)8[0-9]{8,11}$/', preg_replace('/[^0-9+]/', '', $phone));
}

/**
 * Format date to Indonesian format
 * @param string $date
 * @return string
 */
function formatDateIndonesian($date) {
    $months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return $date;
    }
    
    $day = date('d', $timestamp);
    $month = (int)date('m', $timestamp) - 1;
    $year = date('Y', $timestamp);
    
    return $day . ' ' . $months[$month] . ' ' . $year;
}

/**
 * Format time to relative format (e.g., "5 menit lalu")
 * @param string $date
 * @return string
 */
function formatTimeRelative($date) {
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'baru saja';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' menit lalu';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' jam lalu';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' hari lalu';
    } else {
        return formatDateIndonesian($date);
    }
}

/**
 * Generate unique ticket number
 * Format: TK-YYYYMMDD-XXXXX
 * @return string
 */
function generateTicketNumber() {
    $date = date('Ymd');
    $random = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
    return 'TK-' . $date . '-' . $random;
}

/**
 * Truncate text dengan ellipsis
 * @param string $text
 * @param int $length
 * @param string $suffix
 * @return string
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Get file extension
 * @param string $filename
 * @return string
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check jika file extension allowed
 * @param string $filename
 * @return bool
 */
function isAllowedFileExtension($filename) {
    $allowed = explode(',', ALLOWED_EXTENSIONS ?? 'pdf,doc,docx,txt,jpg,png,jpeg');
    $allowed = array_map('trim', $allowed);
    
    return in_array(getFileExtension($filename), $allowed);
}

/**
 * Upload file
 * @param array $file $_FILES array
 * @return array ['success' => bool, 'message' => string, 'filename' => string|null]
 */
function uploadFile($file) {
    // Check if file exists
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'File tidak valid'];
    }
    
    // Check file size
    $maxSize = defined('MAX_FILE_SIZE') ? MAX_FILE_SIZE : 5242880;
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File terlalu besar (max ' . round($maxSize / 1024 / 1024) . 'MB)'];
    }
    
    // Check file extension
    if (!isAllowedFileExtension($file['name'])) {
        return ['success' => false, 'message' => 'Tipe file tidak diizinkan'];
    }
    
    // Create upload directory if not exists
    $uploadDir = __DIR__ . '/../../' . (defined('UPLOAD_PATH') ? UPLOAD_PATH : 'public/uploads');
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $ext = getFileExtension($file['name']);
    $filename = 'file_' . time() . '_' . uniqid() . '.' . $ext;
    $filepath = $uploadDir . '/' . $filename;
    
    // Move file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'Gagal upload file'];
    }
    
    return ['success' => true, 'message' => 'File uploaded berhasil', 'filename' => $filename];
}

/**
 * Delete file
 * @param string $filename
 * @return bool
 */
function deleteFile($filename) {
    $uploadDir = __DIR__ . '/../../' . (defined('UPLOAD_PATH') ? UPLOAD_PATH : 'public/uploads');
    $filepath = $uploadDir . '/' . basename($filename);
    
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    
    return false;
}

/**
 * Log action
 * @param string $action
 * @param string $details
 * @param int|null $userId
 */
function logAction($action, $details = '', $userId = null) {
    $logDir = __DIR__ . '/../../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $userId = $userId ?? (isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 'unknown');
    $message = "[$timestamp] [User: $userId] [Action: $action] $details\n";
    
    file_put_contents($logFile, $message, FILE_APPEND);
}

/**
 * Get page title
 * @return string
 */
function getPageTitle() {
    return 'Helpdesk MTsN 11 Majalengka';
}

/**
 * Get page base URL
 * @return string
 */
function getBaseURL() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . '://' . $host . $path;
}

/**
 * Redirect to URL
 * @param string $url
 * @param int $code HTTP status code
 */
function redirect($url, $code = 302) {
    http_response_code($code);
    header('Location: ' . $url);
    exit;
}

/**
 * Check jika request adalah AJAX
 * @return bool
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get safe JSON response
 * @param bool $success
 * @param string $message
 * @param array $data
 * @return string
 */
function getJsonResponse($success, $message = '', $data = []) {
    return json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => time()
    ]);
}

/**
 * Set response headers untuk JSON
 */
function setJsonHeaders() {
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
}
