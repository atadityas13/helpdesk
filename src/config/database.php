<?php
/**
 * Database Configuration
 * Helpdesk MTsN 11 Majalengka
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'mtsnmaja_ataditya');
define('DB_PASS', 'Admin021398');
define('DB_NAME', 'mtsnmaja_helpdesk');

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8
    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>
