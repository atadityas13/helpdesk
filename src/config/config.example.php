<?php
/**
 * Example Configuration File
 * Rename this to config.php and update values
 * Helpdesk MTsN 11 Majalengka
 */

// Database Configuration
define('DB_HOST', 'localhost');      // Database host
define('DB_USER', 'root');           // Database username
define('DB_PASS', '');               // Database password
define('DB_NAME', 'helpdesk_mtsn11'); // Database name
define('DB_PORT', 3306);             // Database port

// Application Settings
define('APP_NAME', 'Helpdesk MTsN 11 Majalengka');
define('APP_URL', 'http://localhost/helpdesk');
define('APP_ENV', 'development');    // development atau production

// Email Configuration (untuk fitur email nanti)
define('MAIL_HOST', 'smtp.mailtrap.io');
define('MAIL_PORT', '2525');
define('MAIL_USER', 'your_email@example.com');
define('MAIL_PASS', 'your_password');
define('MAIL_FROM', 'noreply@helpdesk.local');

// File Upload Settings
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10 MB
define('UPLOAD_PATH', __DIR__ . '/public/uploads/');
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Ticket Settings
define('TICKET_PREFIX', 'TK');
define('TICKET_AUTO_CLOSE_DAYS', 30);
define('TICKET_AUTO_RESPONSE_ENABLED', false);

// Pagination
define('ITEMS_PER_PAGE', 20);

// Session Settings
define('SESSION_TIMEOUT', 3600); // 1 hour

// Error Logging
define('LOG_PATH', __DIR__ . '/logs/');
define('LOG_ERRORS', true);
define('DISPLAY_ERRORS', APP_ENV === 'development'); // Never true in production
?>
