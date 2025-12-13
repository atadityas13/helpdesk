# 🎯 PROJECT SETUP GUIDE

Panduan lengkap untuk setup Helpdesk System MTsN 11 Majalengka

## 📋 Checklist Awal

- [ ] MySQL/MariaDB terinstall
- [ ] PHP 7.4 atau lebih tinggi terinstall
- [ ] Web server (Apache/Nginx) running
- [ ] Terminal/PowerShell access
- [ ] Text editor (VS Code/Sublime)

---

## 🚀 Langkah Setup (15 Menit)

### 1️⃣ Buat Database

```bash
# Akses MySQL
mysql -u root -p

# Jalankan perintah ini di MySQL prompt
CREATE DATABASE mtsnmaja_helpdesk CHARACTER SET utf8mb4;
USE mtsnmaja_helpdesk;
SOURCE database.sql;

# Verify
SHOW TABLES;
```

**Expected output: 8 tables created**

---

### 2️⃣ Setup Environment File

```bash
# Copy .env.example ke .env
cp .env.example .env

# Edit .env dengan text editor
```

**Edit file `.env` - sesuaikan credentials:**

```env
# Database
DB_HOST=localhost
DB_USER=root
DB_PASS=your_password
DB_NAME=mtsnmaja_helpdesk
DB_CHARSET=utf8mb4

# Application
APP_ENV=production
APP_DEBUG=false
SESSION_TIMEOUT=3600

# File Upload
ALLOWED_EXTENSIONS=pdf,doc,docx,txt,jpg,png,jpeg

# Rate Limiting
RATE_LIMIT_LOGIN=5
RATE_LIMIT_TICKET=3
RATE_LIMIT_MESSAGE=10
```

---

### 3️⃣ Setup Folder Permissions

**Windows (Jika pakai IIS):**
- Right-click `public/uploads` → Properties
- Security tab → Edit
- Beri "Modify" permission ke IIS_IUSRS atau Network Service

**Linux/Mac:**
```bash
chmod 755 public/uploads
chmod 755 logs
chmod 777 public/uploads  # Untuk file uploads
```

---

### 4️⃣ Test Installation

#### A. Test Landing Page
```
URL: http://localhost/helpdesk/
Expected: Muncul halaman utama dengan FAQ
```

#### B. Test Admin Login
```
URL: http://localhost/helpdesk/login.php
Username: admin
Password: admin123
Expected: Redirect ke dashboard
```

#### C. Test Create Ticket
- Klik "Buat Ticket Baru" di landing page
- Isi form dengan data test
- Submit → Chat window terbuka

#### D. Test Admin Dashboard
- Login sebagai admin
- Lihat statistik tickets
- Klik "Kelola Tickets" 
- Lihat list tickets aktif

---

## 📁 Project Structure

```
helpdesk/
├── .env.example              # Environment template
├── .gitignore               # Git ignore
├── database.sql             # Database schema
├── README_SETUP.md          # File ini
│
├── index.php                # Landing page dengan FAQ
├── login.php                # Admin login
├── logout.php               # Admin logout  
├── chat.php                 # Customer chat interface
│
├── src/
│   ├── config/
│   │   ├── .env.php         # Environment loader
│   │   └── database.php     # Database connection
│   │
│   ├── middleware/
│   │   ├── session.php      # Session management
│   │   ├── csrf.php         # CSRF protection
│   │   ├── auth.php         # Authentication
│   │   └── rate-limit.php   # Rate limiting
│   │
│   ├── helpers/
│   │   ├── functions.php    # General functions
│   │   ├── validator.php    # Input validation
│   │   └── ticket.php       # Ticket operations
│   │
│   ├── api/
│   │   ├── login.php        # Admin login
│   │   ├── create-ticket.php
│   │   ├── send-message.php
│   │   ├── get-messages.php
│   │   ├── get-faqs.php
│   │   ├── update-ticket-status.php
│   │   ├── get-ticket.php
│   │   ├── get-ticket-messages.php
│   │   ├── send-admin-message.php
│   │   ├── create-faq.php
│   │   ├── update-faq.php
│   │   └── delete-faq.php
│   │
│   └── admin/
│       ├── dashboard.php           # Admin dashboard
│       ├── manage-tickets.php      # Ticket management
│       └── faqs.php                # FAQ management
│
├── public/
│   ├── css/
│   │   └── style.css        # Global styles
│   ├── js/
│   │   └── widget.js        # Widget JavaScript (optional)
│   └── uploads/             # File uploads
│
└── logs/
    └── activity.log         # Activity logs
```

---

## 🔑 Default Credentials

| Item | Value |
|------|-------|
| **Admin Username** | admin |
| **Admin Password** | admin123 |
| **Database User** | root |
| **Database Name** | mtsnmaja_helpdesk |

**⚠️ PENTING**: Ganti password admin setelah setup!

```php
// Di MySQL:
// Password hash untuk "admin123" sudah disimpan di database

// Untuk ganti password admin:
php -r "echo password_hash('password_baru', PASSWORD_BCRYPT);"
// Copy output, kemudian:
UPDATE admins SET password = '[HASH]' WHERE username = 'admin';
```

---

## 🌐 Important URLs

```
Landing Page:        http://localhost/helpdesk/
Admin Login:         http://localhost/helpdesk/login.php
Admin Dashboard:     http://localhost/helpdesk/src/admin/dashboard.php
Kelola Tickets:      http://localhost/helpdesk/src/admin/manage-tickets.php
FAQ Management:      http://localhost/helpdesk/src/admin/faqs.php
Customer Chat:       http://localhost/helpdesk/chat.php
Logout:              http://localhost/helpdesk/logout.php
```

---

## ✅ Verification Checklist

- [ ] Database created dengan 8 tables
- [ ] .env file sudah dikonfigurasi
- [ ] Landing page bisa diakses
- [ ] Admin login berhasil
- [ ] Bisa membuat ticket baru
- [ ] Admin bisa lihat tickets
- [ ] Chat messaging berfungsi
- [ ] FAQ management berfungsi

---

## 🐛 Troubleshooting

### Error: "Connection refused to database"
```
✓ Pastikan MySQL service running
✓ Check DB_HOST/DB_USER/DB_PASS di .env
✓ Cek port MySQL (default 3306)
```

### Error: "Table doesn't exist"
```
✓ Verifikasi database.sql sudah di-import
✓ Run: SHOW TABLES; di MySQL
✓ Jika tidak ada, re-import database.sql
```

### Error: "Permission denied" saat upload file
```
✓ Linux: chmod 777 public/uploads
✓ Windows: Beri Full Control ke folder uploads
```

### Login tidak berfungsi
```
✓ Cek admin account exists: SELECT * FROM admins;
✓ Clear browser cache/cookies
✓ Cek browser console (F12 → Console)
```

### Chat messages tidak muncul
```
✓ Cek format ticket_number: TK-YYYYMMDD-XXXXX
✓ Verify messages table punya data
✓ Check network tab di browser (F12)
```

---

## 📝 Configuration Details

### Session Settings
```php
SESSION_TIMEOUT = 3600 (1 jam)
Otomatis logout jika idle
```

### Rate Limiting
```php
LOGIN: 5 attempts per 15 minutes
TICKET: 3 per jam
MESSAGE: 10 per 5 menit
```

### Security Features
```
✓ CSRF Protection (tokens)
✓ SQL Injection Prevention (prepared statements)
✓ Password Hashing (bcrypt)
✓ XSS Protection (htmlspecialchars)
✓ Rate Limiting
✓ Session Management
```

---

## 🎓 Next Steps

### Customization
1. Edit branding di `index.php`
2. Ubah colors di `public/css/style.css`
3. Customize email/info di database

### Admin Management
1. Create additional admin accounts
2. Set proper role (admin/agent)
3. Configure ticketing workflow

### Backup & Maintenance
1. Regular database backups
2. Monitor logs di `/logs/`
3. Clear old tickets periodically

### Production Deployment
1. Update .env ke production settings
2. Set APP_DEBUG=false
3. Use HTTPS
4. Strong admin passwords
5. Regular security updates

---

## 📚 Documentation Files

- **QUICK_START.md** - Quick reference (10 min)
- **PANDUAN_PEMBUATAN_ULANG.md** - Complete guide (45 min)
- **DOKUMENTASI_TEKNIS.md** - Technical details (60 min)
- **RINGKASAN_PROYEK.md** - Executive summary (20 min)

---

## 💬 Support

Jika ada error atau pertanyaan, cek:
1. Documentation files
2. Logs di `/logs/` folder
3. Browser developer console (F12)
4. Database logs di MySQL

---

**Status**: ✅ Ready to Deploy  
**Version**: 1.0  
**Last Updated**: December 2025
