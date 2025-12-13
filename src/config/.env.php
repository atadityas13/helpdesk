<?php
/**
 * Environment Configuration Loader
 * Loads .env file and sets up application constants
 */

// Get root directory
$rootDir = dirname(dirname(dirname(__FILE__)));
$envFile = $rootDir . '/.env';

// Load .env file
if (!file_exists($envFile)) {
    die("Error: .env file not found. Please copy .env.example to .env and configure your settings.");
}

// Parse .env file
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    // Skip comments
    if (strpos($line, '#') === 0) {
        continue;
    }
    
    // Parse key=value
    if (strpos($line, '=') !== false) {
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Remove quotes if present
        if (in_array($value[0] ?? null, ['"', "'"])) {
            $value = substr($value, 1, -1);
        }
        
        // Set as constant or environment variable
        if (!defined($key)) {
            define($key, $value);
        }
        putenv("{$key}={$value}");
    }
}

// Set default constants if not defined
if (!defined('APP_ENV')) define('APP_ENV', 'production');
if (!defined('APP_DEBUG')) define('APP_DEBUG', false);
if (!defined('SESSION_TIMEOUT')) define('SESSION_TIMEOUT', 3600);
if (!defined('MAX_UPLOAD_SIZE')) define('MAX_UPLOAD_SIZE', 5242880);
if (!defined('ENABLE_CSRF')) define('ENABLE_CSRF', true);
if (!defined('ENABLE_RATE_LIMIT')) define('ENABLE_RATE_LIMIT', true);
if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH', 'public/uploads');

// Security settings
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Error handling based on APP_DEBUG
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', $rootDir . '/logs/error.log');
}

// Timezone
date_default_timezone_set('Asia/Jakarta');
