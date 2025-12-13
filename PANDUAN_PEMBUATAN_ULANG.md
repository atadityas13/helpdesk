# 📋 PANDUAN LENGKAP PEMBUATAN ULANG APLIKASI HELPDESK

**Aplikasi**: Helpdesk MTsN 11 Majalengka  
**Versi**: 1.0 (Production-Ready)  
**Dibuat**: December 2025  
**Bahasa**: PHP 7.4+ | MySQL/MariaDB | HTML5 | CSS3 | JavaScript (Vanilla)

---

## 📑 DAFTAR ISI
1. [Overview Aplikasi](#overview-aplikasi)
2. [Arsitektur & Struktur File](#arsitektur--struktur-file)
3. [Database Schema](#database-schema)
4. [Setup Awal](#setup-awal)
5. [Implementasi Fitur Step-by-Step](#implementasi-fitur-step-by-step)
6. [API Endpoints](#api-endpoints)
7. [Security Implementation](#security-implementation)
8. [Deployment Guide](#deployment-guide)
9. [Troubleshooting](#troubleshooting)

---

## 🎯 OVERVIEW APLIKASI

### Deskripsi
Sistem ticketing support berbasis web untuk MTsN 11 Majalengka yang memungkinkan:
- **Customer**: Membuat ticket support dan chat real-time dengan admin
- **Admin**: Mengelola tickets, reply pesan, update status, dan manage FAQ

### Fitur Utama
✅ **Customer Side**
- Landing page dengan daftar FAQ
- Floating widget button untuk quick access
- Form pembuatan ticket baru
- Real-time chat interface
- Lanjutkan chat dengan nomor ticket
- File attachment support

✅ **Admin Side**
- Admin dashboard dengan statistik
- Kelola tickets dengan chat interface 2-kolom
- Real-time message refresh
- Update ticket status
- FAQ management (CRUD)
- Session management dengan timeout
- CSRF protection
- Rate limiting
- Input validation

### Target User
- **Sivitas Akademika MTsN 11**: Siswa, Guru, Staf Administrasi
- **Admin Support**: Staff IT/support sekolah

---

## 🏗️ ARSITEKTUR & STRUKTUR FILE

### Folder Structure
```
helpdesk/
├── index.php                          # Landing page dengan FAQ
├── login.php                          # Admin login
├── logout.php                         # Admin logout
├── chat.php                           # Customer chat interface
├── database.sql                       # Database schema
├── cleanup-event.sql                  # Cleanup events
├── .env.example                       # Environment template
├── .gitignore                         # Git ignore rules
│
├── config/
│   ├── .env.php                       # Environment loader
│   └── (create .env here)             # Environment variables
│
├── src/
│   ├── config/
│   │   └── database.php               # Database connection
│   │
│   ├── middleware/
│   │   ├── session.php                # Session management (3600s timeout)
│   │   ├── csrf.php                   # CSRF token protection
│   │   ├── auth.php                   # Admin authentication
│   │   └── rate-limit.php             # Rate limiting (login, ticket, message)
│   │
│   ├── helpers/
│   │   ├── functions.php              # General helpers
│   │   ├── validator.php              # Input validation
│   │   ├── api-response.php           # Standardized JSON responses
│   │   ├── admin-status.php           # Admin status checker
│   │   └── ticket.php                 # Ticket operations
│   │
│   ├── api/
│   │   ├── create-ticket.php          # POST: Create new ticket
│   │   ├── send-message.php           # POST: Send message
│   │   ├── get-messages.php           # GET: Fetch messages
│   │   ├── update-ticket-status.php   # POST: Update status
│   │   ├── mark-read.php              # POST: Mark as read
│   │   ├── typing-status.php          # POST: Typing indicator
│   │   ├── get-admin-status.php       # GET: Admin online status
│   │   ├── cleanup-admin-viewing.php  # POST: Cleanup viewing status
│   │   └── admin-viewing.php          # POST: Set viewing status
│   │
│   └── admin/
│       ├── dashboard.php              # Dashboard dengan statistik
│       ├── manage-tickets.php         # Kelola tickets & chat
│       ├── faqs.php                   # FAQ management
│       └── manage-tickets-old.php     # Backup original
│
├── public/
│   ├── css/
│   │   ├── dashboard.css              # Admin dashboard styles
│   │   └── widget.css                 # Floating widget styles
│   │
│   ├── js/
│   │   └── widget.js                  # Floating widget logic
│   │
│   └── uploads/                       # File upload storage
│
└── logs/                              # Application logs
```

### File Dependencies
```
index.php
├── src/config/database.php
├── src/helpers/functions.php
└── public/js/widget.js
    ├── src/api/create-ticket.php
    ├── src/api/send-message.php
    ├── src/api/get-messages.php
    └── chat.php

login.php
├── src/config/database.php
├── src/helpers/functions.php
├── src/middleware/session.php
├── src/middleware/auth.php
├── src/middleware/csrf.php
└── src/middleware/rate-limit.php

src/admin/dashboard.php
├── src/middleware/session.php
├── src/config/database.php
└── src/helpers/functions.php

src/admin/manage-tickets.php
├── src/middleware/session.php
├── src/config/database.php
├── src/helpers/functions.php
├── src/api/send-message.php
├── src/api/get-messages.php
└── src/api/update-ticket-status.php
```

---

## 🗄️ DATABASE SCHEMA

### Tables & Relationships

#### 1. **customers**
```sql
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Hubungan**: 1 customer → many tickets  
**Purpose**: Menyimpan data pengguna support

---

#### 2. **tickets**
```sql
CREATE TABLE tickets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ticket_number VARCHAR(50) UNIQUE NOT NULL,      -- Format: TK-YYYYMMDD-XXXXX
    customer_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_ticket_number (ticket_number),
    INDEX idx_customer_id (customer_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Status Flow**:
```
open → in_progress → resolved → closed
```

**Hubungan**: 1 ticket → many messages  
**Purpose**: Menyimpan detail ticket support

---

#### 3. **messages**
```sql
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ticket_id INT NOT NULL,
    sender_type ENUM('customer', 'admin') NOT NULL,
    sender_id INT NOT NULL,
    message LONGTEXT NOT NULL,
    attachment_url VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    INDEX idx_ticket_id (ticket_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Hubungan**: 1 ticket ← many messages  
**Purpose**: Menyimpan riwayat chat

---

#### 4. **admins**
```sql
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,              -- bcrypt hash
    email VARCHAR(255),
    role ENUM('admin', 'agent') DEFAULT 'agent',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Purpose**: Menyimpan akun admin/staff support

---

#### 5. **faqs**
```sql
CREATE TABLE faqs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question VARCHAR(255) NOT NULL,
    answer LONGTEXT NOT NULL,
    category VARCHAR(100),                       -- Support, Teknologi, Lainnya
    is_active BOOLEAN DEFAULT TRUE,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Purpose**: Knowledge base untuk reduce support tickets

---

#### 6. **rate_limits** (Auto-created)
```sql
CREATE TABLE rate_limits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    action VARCHAR(50),
    identifier VARCHAR(255),
    count INT,
    expires_at TIMESTAMP,
    INDEX idx_action_identifier (action, identifier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Auto-generated** oleh `src/middleware/rate-limit.php`

---

### ER Diagram
```
┌────────────────────┐
│    CUSTOMERS       │
├────────────────────┤
│ id (PK)            │
│ name               │
│ email (UNIQUE)     │
│ phone              │
│ created_at         │
└──────────┬─────────┘
           │ 1
           │ has many
           │ N
      ┌────▼──────────────────┐
      │     TICKETS            │
      ├───────────────────────┤
      │ id (PK)               │
      │ ticket_number (UNIQUE)│
      │ customer_id (FK)      │
      │ subject               │
      │ status ↕              │◄─── open/in_progress/resolved/closed
      │ priority              │
      │ created_at            │
      │ updated_at            │
      └──────────┬────────────┘
                 │ 1
                 │ has many
                 │ N
            ┌────▼──────────────────┐
            │    MESSAGES            │
            ├───────────────────────┤
            │ id (PK)               │
            │ ticket_id (FK)        │
            │ sender_type           │◄─── customer/admin
            │ sender_id             │
            │ message (LONGTEXT)    │
            │ attachment_url        │
            │ is_read               │
            │ created_at            │
            └───────────────────────┘

┌────────────────────┐
│     ADMINS         │
├────────────────────┤
│ id (PK)            │
│ username (UNIQUE)  │
│ password (bcrypt)  │
│ email              │
│ role               │
│ is_active          │
│ created_at         │
└────────────────────┘

┌────────────────────┐
│      FAQs          │
├────────────────────┤
│ id (PK)            │
│ question           │
│ answer (LONGTEXT)  │
│ category           │
│ is_active          │
│ views              │
│ created_at         │
│ updated_at         │
└────────────────────┘
```

---

## 🚀 SETUP AWAL

### Prerequisites
- PHP 7.4+
- MySQL 5.7+ atau MariaDB 10.3+
- Web Server (Apache / Nginx)
- Git (optional)
- Composer (optional, untuk future packages)

### Step 1: Persiapan Database

```bash
# Login ke MySQL
mysql -u root -p

# Import schema
SOURCE database.sql;

# Verify tables
USE mtsnmaja_helpdesk;
SHOW TABLES;
```

**Expected Output**:
```
+-------------------+
| Tables_in_helpdesk|
+-------------------+
| admins            |
| customers         |
| faqs              |
| messages          |
| rate_limits       |
| tickets           |
+-------------------+
```

### Step 2: Environment Setup

```bash
# Copy .env.example ke .env
cp .env.example .env

# Edit .env dengan credentials database
nano .env
```

**Isi .env**:
```
# Database
DB_HOST=localhost
DB_USER=mtsnmaja_ataditya
DB_PASS=your_password_here
DB_NAME=mtsnmaja_helpdesk

# App Config
APP_ENV=production
SESSION_TIMEOUT=3600              # 1 jam
MAX_LOGIN_ATTEMPTS=5
LOGIN_ATTEMPT_WINDOW=900          # 15 menit

# File Upload
MAX_UPLOAD_SIZE=5242880           # 5MB

# Notifications
ADMIN_EMAIL=admin@helpdesk.local
SITE_URL=http://localhost/helpdesk
```

### Step 3: Folder Permissions

```bash
# Linux/Mac
chmod 755 helpdesk/
chmod 755 helpdesk/public/uploads/
chmod 755 helpdesk/logs/

# Windows: Beri full access ke folder uploads & logs
```

### Step 4: Create Admin Account

```bash
# Login ke database
mysql -u mtsnmaja_ataditya -p mtsnmaja_helpdesk

# Insert admin (default password: admin123)
# IMPORTANT: Ganti password setelah first login!
INSERT INTO admins (username, password, email, role) 
VALUES ('admin', '$2y$10$YYour_bcrypt_hash_here', 'admin@helpdesk.local', 'admin');
```

**Generate bcrypt hash** (gunakan online tool atau PHP):
```php
<?php
echo password_hash('admin123', PASSWORD_BCRYPT);
?>
```

### Step 5: Verify Installation

1. Buka browser: `http://localhost/helpdesk`
   - Harus melihat landing page dengan FAQ
   - Floating button di bottom-right

2. Login admin: `http://localhost/helpdesk/login.php`
   - Username: `admin`
   - Password: `[sesuai yang di-insert]`

3. Verify dashboard muncul dengan statistik

---

## 🛠️ IMPLEMENTASI FITUR STEP-BY-STEP

### PHASE 1: Foundation (Day 1-2)

#### 1.1 Database Setup ✅
```sql
-- Import database.sql
-- Verify semua tables terbuat
-- Insert admin account default
```

#### 1.2 Environment Configuration ✅
- Copy `.env.example` → `.env`
- Isi credentials database
- Verify `config/.env.php` load dengan benar

#### 1.3 Basic Connection Test ✅
```php
// Test file: test-connection.php
<?php
require_once 'src/config/database.php';
echo "Database connection: OK";
?>
```

---

### PHASE 2: Frontend Public (Day 2-3)

#### 2.1 Landing Page (`index.php`) ✅
```php
// Features:
// - Hero section dengan gradient
// - How it works section
// - Responsive grid layout
// - FAQ accordion dengan toggle
// - Embedded floating widget
```

**Content Sections**:
1. **Header**: Logo, branding
2. **How it Works**: 4 step visual
3. **Features**: Benefits of support system
4. **FAQs**: Query dari database
5. **Widget Integration**: `<script src="public/js/widget.js"></script>`

#### 2.2 Floating Widget (`public/js/widget.js`) ✅
```javascript
// HelpdeskWidget class
// - Floating button positioning
// - Menu dropdown (Ticket Baru / Lanjutkan Chat)
// - Modal forms untuk create ticket & continue chat
// - Real-time chat window dengan message refresh
// - localStorage untuk store ticket_number
```

**Key Methods**:
```javascript
- constructor()               // Init & check localStorage
- init()                      // Setup event listeners
- createFloatingButton()      // Create button & menu
- attachEventListeners()      // Attach click handlers
- openNewTicketForm()         // Modal: Create ticket
- openContinueTicketForm()    // Modal: Continue chat
- openChatWindow()            // Chat interface
- sendMessage()               // POST to API
- loadMessages()              // Refresh messages setiap 3s
- displayMessages()           // Render messages
```

#### 2.3 Widget CSS (`public/css/widget.css`) ✅
```css
/* Styles untuk:
   - .helpdesk-floating-button (fixed positioning)
   - .helpdesk-btn-main (gradient + shadow)
   - .helpdesk-menu (dropdown animation)
   - .helpdesk-modal (form modals)
   - .helpdesk-chat-window (chat interface)
   - .helpdesk-message (message bubbles)
*/
```

---

### PHASE 3: Customer API (Day 3-4)

#### 3.1 Create Ticket API (`src/api/create-ticket.php`) ✅
```php
// Method: POST
// Input: name, email, phone, subject, message
// Process:
//   1. Rate limit check (10/hour per IP)
//   2. Validate input (Validator class)
//   3. Get/Create customer
//   4. Generate ticket_number (TK-YYYYMMDD-XXXXX)
//   5. Insert ticket (status=open)
//   6. Insert message pertama
//   7. Return ticket_number + success
```

**Implementation Details**:
```php
// Rate limit
checkRateLimit('create_ticket', $clientIp, $conn);

// Validation
$validator = new Validator($input);
$validator
    ->required('name', 'Nama harus diisi')
    ->required('email', 'Email harus diisi')
    ->email('email')
    ->required('subject', 'Subjek harus diisi')
    ->min('subject', 3, 'Minimal 3 karakter')
    ->required('message', 'Pesan harus diisi')
    ->min('message', 5, 'Minimal 5 karakter');

if (!$validator->isValid()) {
    validationErrorResponse($validator->errors());
}

// Create ticket
createTicket($conn, $customerData, $subject, $message);
```

#### 3.2 Send Message API (`src/api/send-message.php`) ✅
```php
// Method: POST
// Input: ticket_number, message, sender_type (customer/admin)
// Process:
//   1. Validate ticket_number
//   2. Rate limit check (30/hour per IP)
//   3. Validate message
//   4. Get ticket_id dari ticket_number
//   5. Insert message
//   6. Update ticket updated_at
```

#### 3.3 Get Messages API (`src/api/get-messages.php`) ✅
```php
// Method: GET/POST
// Input: ticket_number
// Output: Array of messages dengan sender info
// Process:
//   1. Validate ticket_number
//   2. Fetch messages (ORDER BY created_at DESC)
//   3. Return formatted messages
//   4. Auto-refresh setiap 3 detik dari widget
```

#### 3.4 Mark Read API (`src/api/mark-read.php`) ✅
```php
// Method: POST
// Input: ticket_number
// Process:
//   1. Update messages WHERE is_read=0
//   2. Hanya untuk admin yang membaca
```

---

### PHASE 4: Admin Authentication (Day 4)

#### 4.1 Session Management (`src/middleware/session.php`) ✅
```php
// Functions:
// - initSession()              // Start session + timeout
// - requireAdminLogin()        // Redirect jika belum login
// - requireAdminAccess()       // Verify akses admin
// - getSessionRemainingTime()  // Get remaining timeout
// - isAdminLoggedIn()          // Check login status
// - logoutAdmin()              // Clear session
```

**Session Timeout**:
```php
// 3600 detik (1 jam)
// Auto-refresh pada setiap request
// Auto-redirect ke login jika timeout
```

#### 4.2 CSRF Protection (`src/middleware/csrf.php`) ✅
```php
// Functions:
// - generateCsrfToken()        // Generate token (random_bytes)
// - getCsrfTokenField()        // HTML input field
// - validateCsrfToken()        // Verify hash_equals
// - verifyCsrfRequest()        // POST verification
```

**Implementation**:
```html
<!-- Di setiap form -->
<?php echo getCsrfTokenField(); ?>

<!-- Di form submit -->
if (!verifyCsrfRequest()) {
    die('CSRF token invalid');
}
```

#### 4.3 Authentication (`src/middleware/auth.php`) ✅
```php
// Functions:
// - verifyAdminPassword()      // Check username/password
// - setAdminSession()          // Store di $_SESSION
// - getCurrentAdmin()          // Get admin dari session
```

**Password Verification**:
```php
// Menggunakan password_verify(input, bcrypt_hash)
// Tidak boleh plain text atau simple MD5
```

#### 4.4 Login Page (`login.php`) ✅
```php
// Features:
// - Gradient background
// - Centered form
// - CSRF token protection
// - Rate limiting (5 attempts per 15 menit)
// - Error messages
// - Remember me (optional)
// - Redirect jika sudah login
```

---

### PHASE 5: Admin Dashboard (Day 5)

#### 5.1 Dashboard (`src/admin/dashboard.php`) ✅
```php
// Features:
// - Session check + require login
// - Statistics cards:
//   - Total tickets (all)
//   - Open tickets
//   - In-progress tickets
//   - Resolved tickets
//   - Closed tickets
//   - Total customers
//   - Total messages
// - Activity feed (5 latest tickets)
// - Modern gradient header
// - Responsive layout
```

**Database Queries**:
```php
// Total tickets per status
SELECT COUNT(*) as total FROM tickets WHERE status = 'open'

// Activity feed
SELECT t.*, c.name 
FROM tickets t 
JOIN customers c ON t.customer_id = c.id 
ORDER BY t.updated_at DESC LIMIT 5

// Unread message count
SELECT COUNT(*) as unread 
FROM messages 
WHERE is_read=0 AND sender_type='customer'
```

**Sidebar Navigation**:
```
- Dashboard (active)
- Kelola Tickets (dengan unread badge)
- FAQ Management
- Logout
```

---

### PHASE 6: Ticket Management (Day 5-6)

#### 6.1 Manage Tickets Page (`src/admin/manage-tickets.php`) ✅
```php
// Layout: 2 Column (Sidebar + Chat)
// 
// Sidebar (kiri):
// - Search & filter
// - List semua tickets
//   - Ticket number
//   - Customer name
//   - Status badge
//   - Priority badge
//   - Unread count
//   - Last updated time
// - Auto-refresh
//
// Chat Panel (kanan):
// - Ticket detail (number, customer, subject)
// - Message history
// - Message input form
// - File upload
// - Status update buttons
// - Admin status indicator
```

**Key Features**:
```php
// Auto-load first ticket on page load
// Real-time message refresh (2 detik)
// Click ticket → Load in chat panel
// Status buttons untuk update
// Unread message count di sidebar
// Empty state jika tidak ada ticket
```

#### 6.2 Update Ticket Status (`src/api/update-ticket-status.php`) ✅
```php
// Method: POST
// Input: ticket_id, new_status
// Status Flow:
//   open → in_progress → resolved → closed
// Process:
//   1. Validate status
//   2. Update database
//   3. Insert log message (optional)
```

---

### PHASE 7: FAQ Management (Day 6)

#### 7.1 FAQs Page (`src/admin/faqs.php`) ✅
```php
// Features:
// - List FAQs dengan card grid
// - Add FAQ modal/form
// - Edit FAQ inline
// - Delete dengan confirmation
// - Category tagging
// - Search functionality
// - Sort by views/date
// - Toggle active/inactive
```

**CRUD Operations**:
```php
// Create: POST form → Insert into faqs
// Read: GET → Display all active FAQs
// Update: POST form → Update question/answer
// Delete: POST with confirmation → Soft delete (is_active=0)
```

---

### PHASE 8: Supporting Features (Day 7)

#### 8.1 Typing Status (`src/api/typing-status.php`)
```php
// Method: POST
// Input: ticket_id, is_typing
// Purpose: Show "Admin is typing..." indicator
```

#### 8.2 Admin Viewing (`src/api/admin-viewing.php`)
```php
// Method: POST
// Input: ticket_id
// Purpose: Track which ticket admin is viewing
```

#### 8.3 Get Admin Status (`src/api/get-admin-status.php`)
```php
// Method: GET
// Input: ticket_id
// Output: {is_online: bool, viewing_since: timestamp}
```

---

## 📡 API ENDPOINTS

### Customer APIs

#### 1. Create Ticket
```
POST /src/api/create-ticket.php
Content-Type: application/json

Request:
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "08123456789",
  "subject": "Tidak bisa login",
  "message": "Saya lupa password..."
}

Response Success (200):
{
  "success": true,
  "message": "Ticket created successfully",
  "data": {
    "ticket_number": "TK-20251213-12345",
    "customer_id": 1
  }
}

Response Error (400):
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": "Email harus valid"
  }
}
```

#### 2. Send Message
```
POST /src/api/send-message.php
Content-Type: application/json

Request:
{
  "ticket_number": "TK-20251213-12345",
  "message": "Apakah ada perkembangan?",
  "sender_type": "customer"
}

Response:
{
  "success": true,
  "message": "Message sent",
  "data": {
    "message_id": 15
  }
}
```

#### 3. Get Messages
```
GET /src/api/get-messages.php?ticket_number=TK-20251213-12345

Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "message": "Halo, saya butuh bantuan...",
      "sender_type": "customer",
      "sender_name": "John Doe",
      "created_at": "2025-12-13 10:30:00",
      "attachment_url": null
    },
    {
      "id": 2,
      "message": "Baik, saya akan membantu...",
      "sender_type": "admin",
      "sender_name": "Admin Support",
      "created_at": "2025-12-13 10:35:00",
      "attachment_url": null
    }
  ]
}
```

#### 4. Mark Messages as Read
```
POST /src/api/mark-read.php
Content-Type: application/json

Request:
{
  "ticket_number": "TK-20251213-12345"
}

Response:
{
  "success": true,
  "message": "Messages marked as read"
}
```

### Admin APIs

#### 1. Update Ticket Status
```
POST /src/api/update-ticket-status.php
Content-Type: application/json
(Requires: Session auth + CSRF token)

Request:
{
  "ticket_id": 1,
  "new_status": "in_progress"
}

Response:
{
  "success": true,
  "message": "Status updated to in_progress"
}
```

#### 2. Send Message (Admin)
```
POST /src/api/send-message.php
(Same as customer, but sender_type = "admin")

Request:
{
  "ticket_number": "TK-20251213-12345",
  "message": "Masalah sudah kami perbaiki",
  "sender_type": "admin"
}
```

#### 3. Get Admin Status
```
GET /src/api/get-admin-status.php?ticket_id=1

Response:
{
  "success": true,
  "data": {
    "is_online": true,
    "admin_name": "Admin Support",
    "viewing_since": "2025-12-13 11:00:00"
  }
}
```

---

## 🔐 SECURITY IMPLEMENTATION

### 1. Session Management (3600s timeout)
```php
// src/middleware/session.php
- Session timeout setelah 1 jam
- Auto-refresh pada setiap request
- Secure cookie settings
- REGENERATE session ID setelah login
- Destroy session pada logout
```

**Implementation**:
```php
session_start();
$timeout = 3600; // 1 jam

if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $timeout) {
        session_destroy();
        header('Location: login.php?expired=1');
    }
}
$_SESSION['last_activity'] = time();
```

### 2. CSRF Protection
```php
// src/middleware/csrf.php
- Random token generation (random_bytes(32))
- Token stored di session
- Verify dengan hash_equals()
- Required pada semua form POST
```

**Usage**:
```html
<!-- Dalam form -->
<?php echo getCsrfTokenField(); ?>
<!-- Generates: <input type="hidden" name="csrf_token" value="..."> -->

<!-- Verify di backend -->
if (!verifyCsrfRequest()) {
    die('CSRF token invalid');
}
```

### 3. Password Security
```php
// src/middleware/auth.php
- Bcrypt hashing (password_hash dengan PASSWORD_BCRYPT)
- Verify dengan password_verify()
- NEVER store plain text
- NEVER use MD5 atau SHA1
```

**Hash Generation**:
```php
$hash = password_hash('admin123', PASSWORD_BCRYPT);
// $2y$10$...
```

### 4. Input Validation
```php
// src/helpers/validator.php
- Fluent interface untuk easy validation
- Built-in rules: required, email, min, max, in, numeric, phone
- Custom error messages
- Returns validated/sanitized data
```

**Usage**:
```php
$validator = new Validator($_POST);
$validator
    ->required('name', 'Nama wajib diisi')
    ->email('email')
    ->min('password', 8, 'Minimal 8 karakter')
    ->in('status', ['open', 'closed'], 'Status invalid');

if (!$validator->isValid()) {
    // Show errors
}
$data = $validator->getData(); // Clean data
```

### 5. SQL Injection Prevention
```php
// Prepared Statements dengan mysqli
$query = "SELECT * FROM customers WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
```

### 6. Rate Limiting
```php
// src/middleware/rate-limit.php
- Per-action limits
- Per-IP identifier
- Auto-cleanup expired entries
- Configurable limits
```

**Limits**:
```
- login: 5 attempts per 15 menit
- create_ticket: 10 per jam
- send_message: 30 per jam
```

**Check**:
```php
checkRateLimit('create_ticket', $clientIp, $conn);
// Throws exception jika limit exceeded
```

### 7. Environment Variables
```
// .env file (git-ignored)
- Database credentials
- App settings
- Security tokens
- NEVER commit .env ke git
```

### 8. XSS Prevention
```php
// Sanitize output
htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

// atau dalam template
<?php echo htmlspecialchars($user_input); ?>
```

### 9. File Upload Security
```php
// Validate file type & size
// Store outside web root jika possible
// Generate random filename
// Validate MIME type
// Max 5MB per file
```

---

## 📦 DEPLOYMENT GUIDE

### Production Checklist

#### 1. Pre-Deployment
- [ ] Update `.env` dengan production credentials
- [ ] Generate new random bcrypt hash untuk admin password
- [ ] Test semua features di staging
- [ ] Backup database
- [ ] Verify all middleware loading
- [ ] Check error logs

#### 2. Server Setup
```bash
# Install dependencies
sudo apt-get update
sudo apt-get install php php-mysql php-mysqli apache2 mysql-server

# Enable modules
sudo a2enmod rewrite

# Set permissions
sudo chown -R www-data:www-data /var/www/helpdesk
sudo chmod -R 755 /var/www/helpdesk
sudo chmod -R 775 /var/www/helpdesk/public/uploads
sudo chmod -R 775 /var/www/helpdesk/logs
```

#### 3. Database Backup & Restore
```bash
# Backup
mysqldump -u user -p database_name > backup.sql

# Restore
mysql -u user -p database_name < backup.sql
```

#### 4. SSL Certificate
```bash
# If using Apache with Let's Encrypt
sudo certbot certonly --apache -d yourdomain.com

# Redirect HTTP to HTTPS di .htaccess
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

#### 5. Monitoring & Logs
```bash
# Check error logs
tail -f /var/log/apache2/error.log
tail -f /var/log/apache2/access.log
tail -f /var/www/helpdesk/logs/*.log

# Monitor disk space
df -h

# Monitor database
mysql -e "SHOW PROCESSLIST;"
```

#### 6. Backup Strategy
```bash
# Daily backup
0 2 * * * mysqldump -u user -p pass db > /backup/db-$(date +\%Y\%m\%d).sql

# Keep 7 days rotation
find /backup -name "db-*.sql" -mtime +7 -delete
```

---

## 🐛 TROUBLESHOOTING

### Common Issues & Solutions

#### 1. Database Connection Error
```
Error: Connection failed: php_network_getaddresses: getaddrinfo failed
```

**Solution**:
```php
// Check .env.php path
// Verify DB_HOST, DB_USER, DB_PASS
// Verify MySQL service running
// Check user permissions
sudo service mysql status
```

#### 2. CSRF Token Error
```
Error: CSRF token invalid
```

**Solution**:
```php
// Ensure session_start() called before generateCsrfToken()
// Check if POST data contains csrf_token
// Verify token matches session
// Check $_POST not empty
```

#### 3. Session Timeout Issues
```
Error: Session expired (redirected to login)
```

**Solution**:
```php
// Check SESSION_TIMEOUT in .env (default 3600)
// Verify session_start() called
// Check if JavaScript blocking requests
// Verify Cookie domain settings
```

#### 4. Rate Limit Triggered
```
Error: Too many requests. Please try again later.
```

**Solution**:
```
// Wait for limit to expire
// Check your IP address
// Verify rate_limits table created
// Check current counts: SELECT * FROM rate_limits;
```

#### 5. File Upload Failed
```
Error: Upload failed
```

**Solution**:
```bash
# Check folder permissions
ls -la /var/www/helpdesk/public/uploads/

# Check PHP upload size limit
php -i | grep "upload_max_filesize"

# Update php.ini if needed
upload_max_filesize = 10M
post_max_size = 10M
```

#### 6. Empty Ticket List
```
Problem: Tickets not showing in manage-tickets.php
```

**Solution**:
```sql
-- Verify data exists
SELECT t.*, c.name FROM tickets t 
JOIN customers c ON t.customer_id = c.id;

-- Check if customer records exist
SELECT * FROM customers;

-- Verify column names match
DESCRIBE tickets;
DESCRIBE customers;
```

#### 7. Messages Not Loading
```
Problem: Chat messages empty
```

**Solution**:
```php
// Check get-messages.php response
// Verify ticket_number format
// Check messages table has data
// Verify sender_type values
// Check timestamps in database
```

---

## 📋 CODE QUALITY & STANDARDS

### Coding Standards
```php
// PSR-4: Autoloading (untuk future improvements)
// PSR-2: Code Style
// Constants: UPPERCASE_WITH_UNDERSCORES
// Functions: camelCase
// Classes: PascalCase
// Variables: $snake_case

// Comments
/**
 * Function description
 * 
 * @param type $param Description
 * @return type Description
 */
function myFunction($param) {
    // Implementation
}
```

### File Headers
```php
<?php
/**
 * File Title
 * Helpdesk MTsN 11 Majalengka
 * 
 * Description of what this file does
 */
```

### Error Handling
```php
// Try-catch untuk exceptions
try {
    // Code
} catch (Exception $e) {
    error_log($e->getMessage());
    errorResponse('Server error', 500);
}

// Database errors
if (!$result) {
    error_log($conn->error);
    die('Database error');
}
```

---

## 📈 PERFORMANCE OPTIMIZATION

### Database Indexes
```sql
-- Already created in schema
INDEX idx_ticket_number (ticket_number)
INDEX idx_customer_id (customer_id)
INDEX idx_status (status)
INDEX idx_ticket_created (created_at)
INDEX idx_message_read (is_read)
```

### Caching Strategies
```php
// Messages dapat di-cache
// FAQs dapat di-cache (query berat)
// Admin session data dalam memory
// Ticket list dalam session untuk quick access
```

### Asset Optimization
```css
/* CSS bundling */
/* Minimize production CSS */
/* Use CDN untuk external libraries */

/* JavaScript */
/* Defer non-critical JS */
/* Minimize dalam production */
```

---

## 🔄 MAINTENANCE & UPDATES

### Regular Maintenance Tasks

#### Weekly
```
- Check error logs
- Verify database size
- Check unread message count
- Monitor rate limit table size
```

#### Monthly
- [ ] Database optimization (OPTIMIZE TABLE)
- [ ] Backup verification
- [ ] Security patches check
- [ ] Performance review

#### Quarterly
- [ ] Full system audit
- [ ] Update dependencies
- [ ] Review access logs
- [ ] Clean old data

### Database Cleanup
```sql
-- Auto-cleanup (configured di cleanup-event.sql)
-- Runs: Every night at 2 AM
-- Deletes: Messages older than 90 days

-- Manual cleanup
DELETE FROM messages WHERE DATE(created_at) < DATE_SUB(NOW(), INTERVAL 90 DAY);
DELETE FROM rate_limits WHERE expires_at < NOW();
```

---

## 📚 QUICK REFERENCE

### File Locations
```
Landing Page: /index.php
Admin Login: /login.php
Admin Dashboard: /src/admin/dashboard.php
Ticket Management: /src/admin/manage-tickets.php
FAQ Management: /src/admin/faqs.php
Customer Chat: /chat.php

Database Config: /src/config/database.php
Environment Loader: /config/.env.php
Environment Variables: /.env (git-ignored)
```

### Default Credentials
```
Admin Username: admin
Admin Password: [set during setup]
Default Role: admin
Session Timeout: 3600 seconds (1 hour)
```

### Database Credentials (from .env)
```
Host: localhost
User: mtsnmaja_ataditya
Database: mtsnmaja_helpdesk
Charset: utf8mb4
```

### Key Features by User
```
CUSTOMER:
- Create ticket baru
- Chat real-time dengan admin
- File attachment
- Lanjutkan chat dengan nomor ticket
- View FAQ

ADMIN:
- Dashboard dengan statistik
- Kelola tickets & messages
- Update ticket status
- Manage FAQ
- Session management (1 jam timeout)
- Rate limiting protection
```

---

## 🎓 LEARNING PATH

### If Building from Scratch

1. **Database Foundation** (1 day)
   - Create tables
   - Design relationships
   - Add indexes

2. **Backend APIs** (2 days)
   - Middleware (session, csrf, rate-limit, auth)
   - Helpers (validator, api-response, functions)
   - API endpoints

3. **Admin Panel** (2 days)
   - Login system
   - Dashboard
   - Ticket management
   - FAQ CRUD

4. **Frontend Widget** (2 days)
   - Floating button
   - Forms modal
   - Chat interface
   - Real-time refresh

5. **Testing & Deployment** (1 day)
   - Functional testing
   - Security testing
   - Performance testing
   - Deployment

---

## 📞 SUPPORT & DOCUMENTATION

### Getting Help
1. Check `TROUBLESHOOTING` section
2. Review error logs in `/logs/`
3. Check database records
4. Verify .env configuration
5. Test API endpoints manually

### Testing API Endpoints
```bash
# Using curl
curl -X POST http://localhost/helpdesk/src/api/create-ticket.php \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "phone": "08123456789",
    "subject": "Test Ticket",
    "message": "This is a test message"
  }'

# Response
{"success":true,"message":"Ticket created successfully","data":{"ticket_number":"TK-20251213-00001"}}
```

---

## ✅ FINAL CHECKLIST

Before going live:

- [ ] Database imported with schema
- [ ] .env file created with correct credentials
- [ ] Admin account created with hashed password
- [ ] Permissions set on uploads/ and logs/
- [ ] Landing page loads correctly
- [ ] Floating widget appears
- [ ] Create ticket form works
- [ ] Admin login works
- [ ] Dashboard loads
- [ ] Ticket management functional
- [ ] Messages send/receive
- [ ] File upload works
- [ ] CSRF protection active
- [ ] Rate limiting working
- [ ] Session timeout functional
- [ ] SSL certificate installed (production)
- [ ] Backups automated
- [ ] Logs monitoring setup
- [ ] Error handling tested
- [ ] Performance acceptable

---

**Version**: 1.0  
**Last Updated**: December 2025  
**Maintainer**: Development Team  
**Status**: Production Ready ✅

---

Dokumentasi ini adalah panduan lengkap untuk membangun ulang aplikasi Helpdesk dari nol hingga siap production.
