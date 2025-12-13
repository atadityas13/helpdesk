<?php
/**
 * Logout Handler
 * Destroy session and redirect to login
 */

require_once 'src/middleware/session.php';

// Logout user
logoutAdmin();

// Logout will redirect automatically
exit;
?>
