<?php
/**
 * Logout Handler
 * Destroy session and redirect to login
 */

require_once 'src/middleware/auth.php';

// Logout user
logoutAdmin();

// Redirect to login with message
header('Location: login.php?logged_out=1');
exit;
?>
