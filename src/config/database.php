<?php
/**
 * Database Configuration
 * Helpdesk MTsN 11 Majalengka
 */

// Load environment configuration
require_once __DIR__ . '/../../config/.env.php';

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database Connection Error: Please contact administrator");
}
?>
