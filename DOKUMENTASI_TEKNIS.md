# 🔧 DOKUMENTASI TEKNIS DETAIL - HELPDESK SYSTEM

---

## 📌 DAFTAR ISI
1. [Middleware Details](#middleware-details)
2. [Helper Functions](#helper-functions)
3. [API Specifications](#api-specifications)
4. [Database Queries](#database-queries)
5. [Frontend Architecture](#frontend-architecture)
6. [Security Deep Dive](#security-deep-dive)
7. [Performance Metrics](#performance-metrics)

---

## 🔌 MIDDLEWARE DETAILS

### 1. Session Middleware (`src/middleware/session.php`)

**Purpose**: Manage admin session dengan auto-timeout

**Key Functions**:

```php
/**
 * Initialize session dengan timeout configuration
 */
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        $_SESSION['timeout'] = time() + (int)SESSION_TIMEOUT;
    }
}

/**
 * Check jika admin logged in
 * @return bool
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

/**
 * Require admin login - redirect jika belum
 */
function requireAdminLogin() {
    initSession();
    
    if (!isAdminLoggedIn()) {
        header('Location: ../../login.php');
        exit;
    }
    
    // Check timeout
    if (time() > $_SESSION['timeout']) {
        session_destroy();
        header('Location: ../../login.php?expired=1');
        exit;
    }
    
    // Refresh timeout
    $_SESSION['timeout'] = time() + (int)SESSION_TIMEOUT;
}

/**
 * Get remaining session time dalam detik
 * @return int
 */
function getSessionRemainingTime() {
    return max(0, $_SESSION['timeout'] - time());
}

/**
 * Logout admin - destroy session
 */
function logoutAdmin() {
    session_destroy();
    header('Location: ../../index.php');
    exit;
}
```

**Session Variables Stored**:
```php
$_SESSION['admin_id']           // ID dari tabel admins
$_SESSION['admin_username']     // Username untuk display
$_SESSION['admin_email']        // Email
$_SESSION['admin_role']         // 'admin' atau 'agent'
$_SESSION['last_activity']      // Timestamp untuk timeout tracking
$_SESSION['timeout']            // Expiry timestamp
$_SESSION['csrf_token']         // CSRF token
```

**Usage in Files**:
```php
// Di setiap admin page
require_once '../middleware/session.php';
requireAdminLogin();  // Auto-redirect jika belum login
```

---

### 2. CSRF Middleware (`src/middleware/csrf.php`)

**Purpose**: Protect dari Cross-Site Request Forgery attacks

**Key Functions**:

```php
/**
 * Generate CSRF token - secure random
 * @return string
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get CSRF token field untuk HTML form
 * @return string HTML input element
 */
function getCsrfTokenField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Validate CSRF token dari request
 * @param string $token
 * @return bool
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verify CSRF token di POST request
 * @return bool
 */
function verifyCsrfRequest() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return true;  // GET requests don't need CSRF
    }
    
    $token = $_POST['csrf_token'] ?? '';
    return validateCsrfToken($token);
}
```

**Security Details**:
- Uses `random_bytes(32)` untuk generate 256-bit random token
- Uses `hash_equals()` untuk timing-safe comparison
- Token disimpan di session (server-side)
- Token dikirim di POST body (client-side)

**Usage**:
```html
<!-- Dalam form -->
<form method="POST" action="/submit">
    <?php echo getCsrfTokenField(); ?>
    <input type="text" name="data">
    <button type="submit">Submit</button>
</form>

<!-- Di backend -->
<?php
require_once 'src/middleware/csrf.php';

if (!verifyCsrfRequest()) {
    die('CSRF token tidak valid');
}

$data = $_POST['data'];
// Process...
?>
```

---

### 3. Rate Limit Middleware (`src/middleware/rate-limit.php`)

**Purpose**: Prevent brute force dan spam attacks

**RateLimiter Class**:

```php
class RateLimiter {
    /**
     * Check if action is allowed
     * 
     * @param string $action ('login', 'create_ticket', 'send_message')
     * @param string $identifier (IP address atau user ID)
     * @param mysqli $conn
     * @param int $limit (max attempts)
     * @param int $window (time window dalam detik)
     * 
     * @throws Exception jika limit exceeded
     */
    public static function checkLimit($action, $identifier, $conn, $limit = 5, $window = 900) {
        // Create table jika belum ada
        self::ensureTableExists($conn);
        
        // Cleanup expired entries
        self::cleanup($conn);
        
        // Get current count
        $query = "SELECT COUNT(*) as count FROM rate_limits 
                 WHERE action = ? AND identifier = ? AND expires_at > NOW()";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $action, $identifier);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['count'] >= $limit) {
            throw new Exception("Rate limit exceeded for $action");
        }
        
        // Insert new entry
        $expiry = date('Y-m-d H:i:s', time() + $window);
        $insert = "INSERT INTO rate_limits (action, identifier, expires_at) 
                  VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert);
        $stmt->bind_param("sss", $action, $identifier, $expiry);
        $stmt->execute();
    }
    
    /**
     * Create rate_limits table jika belum ada
     */
    private static function ensureTableExists($conn) {
        $sql = "CREATE TABLE IF NOT EXISTS rate_limits (
            id INT PRIMARY KEY AUTO_INCREMENT,
            action VARCHAR(50) NOT NULL,
            identifier VARCHAR(255) NOT NULL,
            count INT DEFAULT 1,
            expires_at TIMESTAMP NOT NULL,
            INDEX idx_action_identifier (action, identifier),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $conn->query($sql);
    }
    
    /**
     * Cleanup expired entries
     */
    private static function cleanup($conn) {
        $conn->query("DELETE FROM rate_limits WHERE expires_at < NOW()");
    }
}

/**
 * Convenience function
 */
function checkRateLimit($action, $identifier, $conn) {
    RateLimiter::checkLimit($action, $identifier, $conn, ...);
}
```

**Configured Limits**:

```php
// login: 5 attempts per 900 detik (15 menit)
checkRateLimit('login', $ip, $conn);

// create_ticket: 10 attempts per 3600 detik (1 jam)
// Line 16: checkRateLimit('create_ticket', $clientIp, $conn);
// Query uses: LIMIT 10 per hour from same IP

// send_message: 30 attempts per 3600 detik (1 jam)
checkRateLimit('send_message', $clientIp, $conn);
```

---

### 4. Auth Middleware (`src/middleware/auth.php`)

**Purpose**: Verify admin credentials and manage authentication

**Key Functions**:

```php
/**
 * Verify admin password
 * @param mysqli $conn
 * @param string $username
 * @param string $password (plain text)
 * @return bool
 */
function verifyAdminPassword($conn, $username, $password) {
    $query = "SELECT id, password FROM admins WHERE username = ? AND is_active = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $admin = $result->fetch_assoc();
    
    // Verify bcrypt hash
    if (!password_verify($password, $admin['password'])) {
        return false;
    }
    
    // Set session
    setAdminSession($conn, $admin['id']);
    return true;
}

/**
 * Set admin session setelah login berhasil
 */
function setAdminSession($conn, $adminId) {
    // Get admin details
    $query = "SELECT id, username, email, role FROM admins WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    
    // Regenerate session ID untuk security
    session_regenerate_id(true);
    
    // Set session variables
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['login_time'] = time();
}

/**
 * Get current logged in admin
 * @return array|null
 */
function getCurrentAdmin() {
    if (isAdminLoggedIn()) {
        return [
            'id' => $_SESSION['admin_id'],
            'username' => $_SESSION['admin_username'],
            'email' => $_SESSION['admin_email'],
            'role' => $_SESSION['admin_role']
        ];
    }
    return null;
}

/**
 * Verify admin role
 * @param string $required_role ('admin' atau 'agent')
 * @return bool
 */
function verifyAdminRole($required_role) {
    return isAdminLoggedIn() && $_SESSION['admin_role'] === $required_role;
}
```

---

## 🎯 HELPER FUNCTIONS

### 1. Validator Class (`src/helpers/validator.php`)

**Purpose**: Input validation dengan fluent interface

**Methods**:

```php
class Validator {
    private $data = [];
    private $errors = [];
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    /**
     * Validate required field
     */
    public function required($field, $message = '') {
        if (empty($this->data[$field])) {
            $this->errors[$field] = $message ?: "$field is required";
        }
        return $this;
    }
    
    /**
     * Validate email format
     */
    public function email($field, $message = '') {
        if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?: 'Invalid email format';
        }
        return $this;
    }
    
    /**
     * Validate minimum length
     */
    public function min($field, $length, $message = '') {
        if (strlen($this->data[$field]) < $length) {
            $this->errors[$field] = $message ?: "Minimum $length characters";
        }
        return $this;
    }
    
    /**
     * Validate maximum length
     */
    public function max($field, $length, $message = '') {
        if (strlen($this->data[$field]) > $length) {
            $this->errors[$field] = $message ?: "Maximum $length characters";
        }
        return $this;
    }
    
    /**
     * Validate value in array
     */
    public function in($field, $allowed, $message = '') {
        if (!in_array($this->data[$field], $allowed)) {
            $this->errors[$field] = $message ?: 'Invalid option';
        }
        return $this;
    }
    
    /**
     * Check if valid
     */
    public function isValid() {
        return count($this->errors) === 0;
    }
    
    /**
     * Get errors
     */
    public function errors() {
        return $this->errors;
    }
    
    /**
     * Get clean data
     */
    public function getData() {
        $clean = [];
        foreach ($this->data as $key => $value) {
            $clean[$key] = sanitizeInput($value);
        }
        return $clean;
    }
}
```

**Usage**:

```php
$validator = new Validator($_POST);
$validator
    ->required('name', 'Nama harus diisi')
    ->required('email', 'Email harus diisi')
    ->email('email', 'Email tidak valid')
    ->required('message', 'Pesan harus diisi')
    ->min('message', 5, 'Minimal 5 karakter');

if (!$validator->isValid()) {
    validationErrorResponse($validator->errors());
}

$cleanData = $validator->getData();
```

---

### 2. API Response Helper (`src/helpers/api-response.php`)

**Purpose**: Standardized JSON response format

**Response Functions**:

```php
/**
 * Success response
 */
function successResponse($message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Error response
 */
function errorResponse($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

/**
 * Validation error response
 */
function validationErrorResponse($errors) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $errors
    ]);
    exit;
}

/**
 * Not found response
 */
function notFoundResponse($message = 'Resource not found') {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

/**
 * Unauthorized response
 */
function unauthorizedResponse($message = 'Unauthorized') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

/**
 * Server error response
 */
function serverErrorResponse($message = 'Internal server error') {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}
```

---

### 3. Ticket Helper (`src/helpers/ticket.php`)

**Purpose**: Ticket operations

**Key Functions**:

```php
/**
 * Generate unique ticket number
 * Format: TK-YYYYMMDD-XXXXX
 */
function generateTicketNumber() {
    $date = date('Ymd');
    $random = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
    return 'TK-' . $date . '-' . $random;
}

/**
 * Create new ticket with transaction
 */
function createTicket($conn, $customerData, $subject, $message) {
    $conn->begin_transaction();
    
    try {
        // Get atau create customer
        $customerQuery = "SELECT id FROM customers WHERE email = ?";
        $stmt = $conn->prepare($customerQuery);
        $stmt->bind_param("s", $customerData['email']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result) {
            $customerId = $result['id'];
        } else {
            // Insert new customer
            $insertCustomer = "INSERT INTO customers (name, email, phone) 
                             VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertCustomer);
            $stmt->bind_param("sss", 
                $customerData['name'], 
                $customerData['email'], 
                $customerData['phone']
            );
            $stmt->execute();
            $customerId = $conn->insert_id;
        }
        
        // Generate ticket number
        $ticketNumber = generateTicketNumber();
        
        // Insert ticket
        $insertTicket = "INSERT INTO tickets 
                        (ticket_number, customer_id, subject, status, priority) 
                        VALUES (?, ?, ?, 'open', 'medium')";
        $stmt = $conn->prepare($insertTicket);
        $stmt->bind_param("sis", $ticketNumber, $customerId, $subject);
        $stmt->execute();
        $ticketId = $conn->insert_id;
        
        // Insert message
        $insertMessage = "INSERT INTO messages 
                         (ticket_id, sender_type, sender_id, message) 
                         VALUES (?, 'customer', ?, ?)";
        $stmt = $conn->prepare($insertMessage);
        $stmt->bind_param("iis", $ticketId, $customerId, $message);
        $stmt->execute();
        
        $conn->commit();
        
        return [
            'ticket_id' => $ticketId,
            'ticket_number' => $ticketNumber,
            'customer_id' => $customerId
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

/**
 * Get ticket by number
 */
function getTicketByNumber($conn, $ticketNumber) {
    $query = "SELECT * FROM tickets WHERE ticket_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $ticketNumber);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Get all messages untuk ticket
 */
function getTicketMessages($conn, $ticketId) {
    $query = "SELECT m.*, c.name as customer_name, a.username as admin_name 
             FROM messages m 
             LEFT JOIN customers c ON m.sender_type = 'customer' AND m.sender_id = c.id 
             LEFT JOIN admins a ON m.sender_type = 'admin' AND m.sender_id = a.id 
             WHERE m.ticket_id = ? 
             ORDER BY m.created_at ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ticketId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Update ticket status
 */
function updateTicketStatus($conn, $ticketId, $newStatus) {
    $allowed = ['open', 'in_progress', 'resolved', 'closed'];
    
    if (!in_array($newStatus, $allowed)) {
        throw new Exception('Invalid status');
    }
    
    $query = "UPDATE tickets SET status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $newStatus, $ticketId);
    $stmt->execute();
    
    return $stmt->affected_rows > 0;
}
```

---

## 📡 API SPECIFICATIONS

### 1. Create Ticket API
**File**: `src/api/create-ticket.php`

```php
// Header
header('Content-Type: application/json; charset=utf-8');

// Method: POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// Process:
// 1. Rate limit: 10 per jam dari IP
// 2. JSON decode input
// 3. Validate: name, email, subject, message
// 4. Create ticket via helper
// 5. Return ticket_number

// Response Success:
{
  "success": true,
  "message": "Ticket created successfully",
  "data": {
    "ticket_id": 1,
    "ticket_number": "TK-20251213-12345",
    "customer_id": 1
  }
}

// Response Validation Error (422):
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": "Email harus valid",
    "message": "Pesan minimal 5 karakter"
  }
}

// Response Rate Limit (429):
{
  "success": false,
  "message": "Rate limit exceeded"
}
```

---

### 2. Send Message API
**File**: `src/api/send-message.php`

```php
// Method: POST
// Input JSON:
{
  "ticket_number": "TK-20251213-12345",
  "message": "Hello admin...",
  "sender_type": "customer"  // atau 'admin'
}

// Process:
// 1. Validate ticket_number format (TK-YYYYMMDD-XXXXX)
// 2. Rate limit check (30/jam)
// 3. Get ticket ID dari ticket_number
// 4. Validate message not empty
// 5. Insert message ke database
// 6. Update ticket updated_at
// 7. Return message_id

// Response:
{
  "success": true,
  "message": "Message sent successfully",
  "data": {
    "message_id": 42,
    "ticket_id": 1
  }
}
```

---

### 3. Get Messages API
**File**: `src/api/get-messages.php`

```php
// Method: GET
// Query: ?ticket_number=TK-20251213-12345

// Process:
// 1. Validate ticket_number
// 2. Get ticket ID
// 3. Fetch all messages dengan JOIN ke customers/admins
// 4. Return messages array
// 5. Sort by created_at ASC

// Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "ticket_id": 1,
      "sender_type": "customer",
      "sender_id": 1,
      "sender_name": "John Doe",
      "message": "Hello...",
      "attachment_url": null,
      "is_read": false,
      "created_at": "2025-12-13 10:30:00"
    },
    {
      "id": 2,
      "ticket_id": 1,
      "sender_type": "admin",
      "sender_id": 1,
      "sender_name": "Admin Support",
      "message": "Hi, we can help...",
      "attachment_url": null,
      "is_read": false,
      "created_at": "2025-12-13 10:35:00"
    }
  ]
}
```

---

### 4. Update Ticket Status API
**File**: `src/api/update-ticket-status.php`

```php
// Method: POST
// Requires: Session auth (admin only)
// Input JSON:
{
  "ticket_id": 1,
  "new_status": "in_progress"  // open|in_progress|resolved|closed
}

// Process:
// 1. Require admin login
// 2. Verify CSRF token
// 3. Validate ticket_id exists
// 4. Validate new_status in allowed list
// 5. Update database
// 6. Return success

// Response:
{
  "success": true,
  "message": "Status updated to in_progress"
}
```

---

## 📊 DATABASE QUERIES

### High-Performance Queries

#### 1. Get All Tickets with Customer
```sql
-- With message count
SELECT 
    t.id,
    t.ticket_number,
    t.subject,
    t.status,
    t.priority,
    t.created_at,
    t.updated_at,
    c.name as customer_name,
    COUNT(DISTINCT m.id) as message_count,
    SUM(CASE WHEN m.is_read = 0 AND m.sender_type = 'customer' 
             THEN 1 ELSE 0 END) as unread_count
FROM tickets t
JOIN customers c ON t.customer_id = c.id
LEFT JOIN messages m ON t.id = m.ticket_id
WHERE t.status IN ('open', 'in_progress')
GROUP BY t.id, t.ticket_number, t.subject, t.status, t.priority, 
         t.created_at, t.updated_at, c.name
ORDER BY t.updated_at DESC
LIMIT 50;

-- Indexes needed:
-- CREATE INDEX idx_ticket_number ON tickets(ticket_number);
-- CREATE INDEX idx_customer_id ON tickets(customer_id);
-- CREATE INDEX idx_status ON tickets(status);
-- CREATE INDEX idx_updated_at ON tickets(updated_at);
```

#### 2. Get Dashboard Statistics
```sql
-- Total by status
SELECT 
    status,
    COUNT(*) as count
FROM tickets
GROUP BY status;

-- Recent activity
SELECT 
    t.id,
    t.ticket_number,
    t.subject,
    t.status,
    c.name as customer_name,
    t.updated_at,
    COUNT(m.id) as message_count
FROM tickets t
JOIN customers c ON t.customer_id = c.id
LEFT JOIN messages m ON t.id = m.ticket_id
ORDER BY t.updated_at DESC
LIMIT 10;

-- Unread messages count
SELECT COUNT(*) as unread
FROM messages
WHERE is_read = 0 AND sender_type = 'customer';
```

#### 3. Get Ticket with Full History
```sql
SELECT 
    t.*,
    c.name as customer_name,
    c.email as customer_email,
    c.phone as customer_phone,
    m.id as message_id,
    m.sender_type,
    m.sender_id,
    COALESCE(c2.name, a.username) as sender_name,
    m.message,
    m.attachment_url,
    m.is_read,
    m.created_at as message_created_at
FROM tickets t
JOIN customers c ON t.customer_id = c.id
LEFT JOIN messages m ON t.id = m.ticket_id
LEFT JOIN customers c2 ON m.sender_type = 'customer' AND m.sender_id = c2.id
LEFT JOIN admins a ON m.sender_type = 'admin' AND m.sender_id = a.id
WHERE t.ticket_number = ?
ORDER BY m.created_at ASC;
```

#### 4. Search Tickets
```sql
SELECT 
    t.id,
    t.ticket_number,
    t.subject,
    t.status,
    c.name as customer_name,
    t.created_at
FROM tickets t
JOIN customers c ON t.customer_id = c.id
WHERE (
    t.ticket_number LIKE CONCAT('%', ?, '%') OR
    t.subject LIKE CONCAT('%', ?, '%') OR
    c.name LIKE CONCAT('%', ?, '%') OR
    c.email LIKE CONCAT('%', ?, '%')
)
AND t.status = ?
ORDER BY t.created_at DESC
LIMIT 50;
```

---

## 🎨 FRONTEND ARCHITECTURE

### Widget.js Architecture

```javascript
/**
 * Configuration
 */
const HELPDESK_CONFIG = {
    apiBase: '/helpdesk/src/api/',
    buttonId: 'helpdesk-floating-btn',
    chatWindowId: 'helpdesk-chat-window',
    storageKey: 'helpdesk_ticket_number',
    refreshInterval: 3000  // 3 detik
};

/**
 * HelpdeskWidget Class
 */
class HelpdeskWidget {
    // Constructor & Initialization
    constructor() {}
    init() {}
    injectStyles() {}
    
    // UI Components
    createFloatingButton() {}
    openNewTicketForm() {}
    openContinueTicketForm() {}
    openChatWindow() {}
    
    // Event Handling
    attachEventListeners() {}
    
    // API Communication
    submitNewTicket() {}
    sendMessage() {}
    loadMessages() {}
    displayMessages() {}
    
    // Utilities
    escapeHtml() {}
    formatTime() {}
}

/**
 * Initialization
 */
document.addEventListener('DOMContentLoaded', () => {
    new HelpdeskWidget();
});
```

### Widget State Management

```javascript
// localStorage usage
localStorage.setItem('helpdesk_ticket_number', 'TK-20251213-12345');
localStorage.getItem('helpdesk_ticket_number');

// Session state in memory
this.ticketNumber = localStorage.getItem(HELPDESK_CONFIG.storageKey);

// Message polling
setInterval(() => this.loadMessages(ticketNumber), 3000);
```

### Widget Event Flow

```
User Action
    ↓
Event Listener
    ↓
Handler Method
    ↓
API Call (fetch)
    ↓
Backend Processing
    ↓
JSON Response
    ↓
Update DOM / localStorage
    ↓
User sees result
```

---

## 🔒 SECURITY DEEP DIVE

### 1. Password Security

**Storage**:
```php
// During registration/change password
$hash = password_hash($plaintext_password, PASSWORD_BCRYPT, ['cost' => 10]);
// Hasil: $2y$10$...

// During login verification
$matches = password_verify($plaintext_input, $stored_hash);
```

**Why Bcrypt?**
- Automatically salted
- Cost parameter untuk slow hashing (proteksi brute force)
- Resistant to GPU attacks
- Built-in PHP function

---

### 2. CSRF Attack Prevention

**Attack Flow** (tanpa CSRF protection):
```
1. Attacker creates fake form
2. User clicks attacker's link
3. Attacker's form submits to helpdesk
4. Request succeeds karena user logged in
```

**Prevention**:
```php
// Generate random token di session
// Include token di form hidden input
// Verify token pada POST dengan hash_equals()
// Timing-safe comparison (not == or ===)
```

---

### 3. Session Hijacking Prevention

**Measures**:
```php
// 1. Regenerate session ID after login
session_regenerate_id(true);

// 2. Auto-timeout after 1 hour
$timeout = time() + 3600;

// 3. Check timeout on every request
if (time() > $_SESSION['timeout']) {
    session_destroy();
    // redirect to login
}

// 4. Secure cookie settings
session.cookie_httponly = On       // No JS access
session.cookie_secure = On         // HTTPS only (production)
session.cookie_samesite = Strict   // CSRF protection
```

---

### 4. SQL Injection Prevention

**Vulnerable Code** (DON'T USE):
```php
$query = "SELECT * FROM users WHERE email = '$email'";
// SQL: SELECT * FROM users WHERE email = 'admin@test.com' OR '1'='1'
```

**Safe Code** (USE THIS):
```php
$query = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
```

---

### 5. XSS Prevention

**Vulnerable Code** (DON'T USE):
```php
echo $user_input;  // User dapat inject JS
```

**Safe Code** (USE THIS):
```php
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');
// Converts: < > " ' & to HTML entities
```

---

### 6. Rate Limiting Strategy

**Purpose**: Prevent brute force attacks

**Limits Set**:
```
login:           5 attempts per 15 menit
create_ticket:   10 per jam
send_message:    30 per jam
```

**Implementation**:
```sql
-- Per action per identifier (IP/user)
SELECT COUNT(*) FROM rate_limits
WHERE action = 'login' 
AND identifier = '192.168.1.1'
AND expires_at > NOW()

-- If count >= limit, reject request
```

---

## 📈 PERFORMANCE METRICS

### Query Performance

```sql
-- Analyze query plan
EXPLAIN SELECT t.*, c.name FROM tickets t
JOIN customers c ON t.customer_id = c.id
WHERE t.status = 'open'
ORDER BY t.updated_at DESC;

-- Expected: Using index on status
-- Using join buffer (BNL) for efficient joining
```

### Database Size Estimation

```
Assumptions:
- 10,000 tickets per tahun
- 5 messages per ticket rata-rata
- 50,000 total messages per tahun

Storage:
- tickets: 10,000 × ~500 bytes = 5 MB
- messages: 50,000 × ~1 KB = 50 MB
- customers: 2,000 × ~300 bytes = 0.6 MB
Total: ~60 MB per tahun

Scalability:
- Up to 1 tahun data: Acceptable
- Recommend archival after 2 tahun
```

### Page Load Metrics

```
Landing page (index.php):
- HTML: ~80 KB
- CSS: ~50 KB (inline)
- JS: ~30 KB
- Total: ~160 KB

Admin Dashboard (dashboard.php):
- HTML: ~30 KB
- CSS: ~40 KB
- Data: Variable (queries)
- Total: ~70 KB + query time

Widget Load:
- JS: ~25 KB
- CSS: ~35 KB
- First request to server: ~100ms
- Message refresh: ~200ms
```

### Caching Strategy

```php
// FAQ caching (tidak sering berubah)
$faqs = apcu_fetch('faqs_list');
if (!$faqs) {
    $faqs = /* query database */;
    apcu_store('faqs_list', $faqs, 3600); // 1 hour
}

// Admin data caching
$admin = apcu_fetch('admin_' . $admin_id);
if (!$admin) {
    $admin = /* query database */;
    apcu_store('admin_' . $admin_id, $admin, 7200); // 2 hours
}
```

---

**Version**: 1.0  
**Last Updated**: December 2025

Dokumentasi teknis ini memberikan detail mendalam untuk developers yang ingin memahami internal system.
