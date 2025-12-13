# 🚀 QUICK START GUIDE - SETUP DALAM 15 MENIT

**Target**: Memiliki aplikasi helpdesk yang fully functional

---

## 📋 CHECKLIST SEBELUM MULAI

- [ ] MySQL/MariaDB sudah installed
- [ ] PHP 7.4+ sudah installed
- [ ] Apache/Nginx sudah running
- [ ] Command line access (terminal/PowerShell)
- [ ] Text editor (VS Code atau sublime)

---

## 🎯 LANGKAH-LANGKAH SETUP (15 menit)

### STEP 1: Download Project (1 menit)

```bash
# Clone atau download project
git clone https://github.com/atadityas13/helpdesk.git
cd helpdesk

# Atau extract ZIP file jika sudah download
cd D:\BACKUP HOSTING\Source\Helpdesk\helpdesk
```

### STEP 2: Database Import (2 menit)

```bash
# Terminal/Command Prompt
mysql -u root -p

# Di MySQL prompt
CREATE DATABASE mtsnmaja_helpdesk CHARACTER SET utf8mb4;
USE mtsnmaja_helpdesk;
SOURCE database.sql;

# Verify
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

### STEP 3: Environment Configuration (2 menit)

```bash
# Copy .env.example ke .env
cp .env.example .env

# Edit .env dengan text editor
# Windows: Bisa langsung copy & rename
```

**Edit file `.env` ini dengan credentials database**:

```env
# Database
DB_HOST=localhost
DB_USER=root
DB_PASS=password_anda
DB_NAME=mtsnmaja_helpdesk

# App
APP_ENV=production
SESSION_TIMEOUT=3600
```

### STEP 4: Create Admin Account (2 menit)

```bash
# Generate bcrypt hash password
php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"
# Copy output: $2y$10$...

# Insert ke database
mysql -u root -p mtsnmaja_helpdesk

# Di MySQL
INSERT INTO admins (username, password, email, role) 
VALUES ('admin', '$2y$10$[PASTE_HASH_HERE]', 'admin@helpdesk.local', 'admin');

# Verify
SELECT * FROM admins;
```

### STEP 5: Set Folder Permissions (2 menit)

```bash
# Linux/Mac
chmod 755 public/uploads
chmod 755 logs

# Windows
# - Right-click folder → Properties
# - Security tab → Edit
# - Beri "Full Control" ke IIS_IUSRS atau Network Service
```

### STEP 6: Test Setup (2 menit)

```bash
# Buka browser
# Landing page
http://localhost/helpdesk/index.php

# Admin login
http://localhost/helpdesk/login.php
# Username: admin
# Password: admin123
```

✅ **SELESAI!** Aplikasi siap digunakan.

---

## 🔑 DEFAULT CREDENTIALS

| Item | Value |
|------|-------|
| **Admin Username** | admin |
| **Admin Password** | admin123 |
| **Database User** | root |
| **Database Name** | mtsnmaja_helpdesk |
| **Session Timeout** | 3600 detik (1 jam) |

⚠️ **IMPORTANT**: Ganti password admin setelah setup!

---

## 📍 URLS PENTING

```
Landing Page:       http://localhost/helpdesk/
Admin Login:        http://localhost/helpdesk/login.php
Admin Dashboard:    http://localhost/helpdesk/src/admin/dashboard.php
Kelola Tickets:     http://localhost/helpdesk/src/admin/manage-tickets.php
FAQ Management:     http://localhost/helpdesk/src/admin/faqs.php
Customer Chat:      http://localhost/helpdesk/chat.php
Logout:             http://localhost/helpdesk/logout.php
```

---

## 🧪 VERIFY SETUP

### 1. Test Widget di Landing Page
- Kunjungi `index.php`
- Lihat floating button di bottom-right
- Klik: Seharusnya ada menu dropdown
- Click "Ticket Baru": Seharusnya modal form muncul

### 2. Test Create Ticket
- Isi form ticket baru:
  - Nama: "Test User"
  - Email: "test@example.com"
  - Phone: "08123456789"
  - Subject: "Test Ticket"
  - Message: "This is test message"
- Click "Buat Ticket"
- Seharusnya chat window terbuka

### 3. Test Admin Login
- Kunjungi `login.php`
- Username: `admin`
- Password: `admin123`
- Click "Masuk"
- Seharusnya redirect ke dashboard

### 4. Test Dashboard
- Lihat statistik tickets
- Lihat activity feed
- Click "Kelola Tickets"

### 5. Test Manage Tickets
- Seharusnya ada list tickets di sidebar
- Click ticket dari list
- Seharusnya chat detail muncul di kanan
- Test kirim message
- Test update status

---

## 🔧 TROUBLESHOOTING QUICK FIX

### Error: "Database Connection Error"
```
Solusi:
1. Verify DB_HOST/DB_USER/DB_PASS di .env
2. Verify MySQL service running
3. Check database exists: SHOW DATABASES;
```

### Error: "Table doesn't exist"
```
Solusi:
1. Verify database.sql sudah di-import
2. Run: SHOW TABLES; untuk verify semua tables ada
3. Jika tidak ada, re-import: SOURCE database.sql;
```

### Widget not appearing
```
Solusi:
1. Check browser console (F12 → Console)
2. Verify widget.js loaded: Check Network tab
3. Verify CSS loaded: Check public/css/widget.css
```

### Login not working
```
Solusi:
1. Check admin account exists: SELECT * FROM admins;
2. Verify password hash correct
3. Try reset password: 
   UPDATE admins SET password = '$2y$10$[NEW_HASH]' 
   WHERE username = 'admin';
```

### Chat messages not showing
```
Solusi:
1. Verify messages table has data: SELECT * FROM messages;
2. Check get-messages.php response in browser Network tab
3. Verify ticket_number format: TK-YYYYMMDD-XXXXX
```

---

## ✨ NEXT STEPS SETELAH SETUP

### Immediately (Hari 1)
- [ ] Change admin password
- [ ] Test semua features
- [ ] Backup database
- [ ] Customize branding di index.php

### Short Term (Minggu 1)
- [ ] Add more admin accounts
- [ ] Create FAQ entries
- [ ] Set up email notifications (optional)
- [ ] Configure rate limits sesuai kebutuhan

### Medium Term (Bulan 1)
- [ ] Setup automated backups
- [ ] Monitor error logs
- [ ] Optimize performance
- [ ] Train admin staff

### Long Term (Ongoing)
- [ ] Regular backups
- [ ] Security updates
- [ ] Monitor usage
- [ ] Scale infrastructure jika diperlukan

---

## 📞 QUICK REFERENCE

### File Structure
```
helpdesk/
├── index.php                      ← Landing page
├── login.php                      ← Admin login
├── logout.php                     ← Logout
├── chat.php                       ← Customer chat
├── database.sql                   ← Database schema
├── .env                           ← Configuration (git-ignored)
├── .env.example                   ← Configuration template
│
├── src/
│   ├── api/                       ← API endpoints
│   ├── admin/                     ← Admin pages
│   ├── middleware/                ← Auth, CSRF, Rate limit, Session
│   ├── helpers/                   ← Validators, Responses
│   └── config/
│       ├── database.php           ← DB connection
│       └── .env.php               ← Load .env file
│
└── public/
    ├── css/                       ← Stylesheets
    ├── js/                        ← Widget JavaScript
    └── uploads/                   ← File uploads
```

### Key PHP Functions

```php
// Session & Auth
requireAdminLogin()           // Check admin logged in
isAdminLoggedIn()            // Current login status
getSessionRemainingTime()    // Session timeout remaining
logoutAdmin()                // Logout

// CSRF & Security
generateCsrfToken()          // Generate CSRF token
verifyCsrfRequest()          // Verify CSRF on POST
checkRateLimit()             // Check rate limit

// Validation
$validator = new Validator($data)
    ->required('field', 'Error message')
    ->email('email')
    ->isValid()              // Check valid
    ->getData()              // Get clean data

// Responses
successResponse($message, $data)
errorResponse($message, $code)
validationErrorResponse($errors)

// Tickets
createTicket($conn, $customerData, $subject, $message)
getTicketByNumber($conn, $ticketNumber)
updateTicketStatus($conn, $ticketId, $newStatus)
```

### Key API Endpoints

```
POST   /src/api/create-ticket.php       ← Create ticket
POST   /src/api/send-message.php        ← Send message
GET    /src/api/get-messages.php        ← Get messages
POST   /src/api/mark-read.php           ← Mark as read
POST   /src/api/update-ticket-status.php ← Update status
GET    /src/api/get-admin-status.php    ← Admin online status
```

---

## 🎓 LEARNING RESOURCES

### Understand the Flow

1. **Customer Creating Ticket**:
   - Open index.php
   - Click floating button
   - Click "Ticket Baru"
   - Fill form → Widget opens
   - Form submit → POST to create-ticket.php
   - API generates ticket_number
   - Chat window opens
   - Customer chat with admin

2. **Admin Managing Ticket**:
   - Login at login.php
   - Dashboard shows stats
   - Click "Kelola Tickets"
   - See ticket list in sidebar
   - Click ticket → Chat loads
   - Send messages to customer
   - Update ticket status
   - Ticket closed

### Code Walkthrough

Start dengan file ini untuk understand:
1. `index.php` - Landing page layout
2. `src/config/database.php` - DB connection
3. `public/js/widget.js` - Frontend logic
4. `src/api/create-ticket.php` - Create ticket flow
5. `src/admin/dashboard.php` - Admin dashboard

---

## 💾 BACKUP SEKARANG!

```bash
# Backup database
mysqldump -u root -p mtsnmaja_helpdesk > backup-$(date +%Y%m%d).sql

# Backup files (opsional)
tar -czf helpdesk-backup-$(date +%Y%m%d).tar.gz helpdesk/

# Restore jika diperlukan
mysql -u root -p mtsnmaja_helpdesk < backup-20251213.sql
```

---

## ✅ FINAL CHECKLIST

- [ ] Database imported
- [ ] .env configured
- [ ] Admin account created
- [ ] Permissions set
- [ ] Landing page loads
- [ ] Widget appears
- [ ] Create ticket works
- [ ] Admin login works
- [ ] Dashboard shows stats
- [ ] Ticket management works
- [ ] Messages send/receive
- [ ] Status update works
- [ ] Backup created

---

**Selamat! 🎉 Helpdesk application Anda sudah siap digunakan!**

Untuk dokumentasi lebih lengkap, baca `PANDUAN_PEMBUATAN_ULANG.md`
