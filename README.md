# Helpdesk MTsN 11 Majalengka

Sistem helpdesk terintegrasi dengan floating button widget dan admin dashboard untuk manajemen ticket chat.

## 📋 Fitur

### User Side:
- ✅ Floating button widget (akses dari mana saja)
- ✅ Chat interface seperti WhatsApp
- ✅ Buat ticket baru
- ✅ Lanjutkan chat dengan nomor ticket
- ✅ History chat otomatis tersimpan
- ✅ Real-time message loading (AJAX polling)

### Admin Side:
- ✅ Dashboard dengan statistik
- ✅ Manajemen tickets dalam satu tempat
- ✅ Chat interface dengan customer
- ✅ FAQ/Knowledge Base management
- ✅ Status tracking
- ✅ Search & filter

## 🚀 Setup & Installation

### 1. Database Setup

```bash
# Import SQL ke MySQL Anda
mysql -u root helpdesk_mtsn11 < database.sql
```

**Atau manual:**
1. Buka phpMyAdmin
2. Create database: `helpdesk_mtsn11`
3. Import file: `database.sql`

### 2. Konfigurasi Database

Edit file `src/config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Sesuaikan password
define('DB_NAME', 'helpdesk_mtsn11');
```

### 3. Login Admin

**Default Credentials:**
- Username: `admin`
- Password: `password123`

**Path:** `http://localhost/helpdesk/login.php`

## 📁 Struktur Project

```
helpdesk/
├── public/
│   ├── js/
│   │   └── widget.js           # Floating button & chat logic
│   ├── css/
│   │   ├── widget.css          # Widget styling
│   │   └── dashboard.css       # Admin panel styling
│   └── uploads/                # Customer uploads (future)
│
├── src/
│   ├── api/
│   │   ├── create-ticket.php   # API: Buat ticket
│   │   ├── get-messages.php    # API: Ambil messages
│   │   └── send-message.php    # API: Kirim message
│   ├── admin/
│   │   ├── dashboard.php       # Dashboard utama
│   │   ├── manage-tickets.php  # Manage tickets
│   │   └── faqs.php            # FAQ management
│   ├── config/
│   │   └── database.php        # Database connection
│   ├── middleware/
│   │   └── auth.php            # Authentication
│   └── helpers/
│       ├── functions.php       # General functions
│       └── ticket.php          # Ticket functions
│
├── database.sql                # Database schema
├── login.php                   # Login page
└── README.md                   # Documentation
```

## 🔧 Integration Guide

### Cara mengintegrasikan widget ke website Anda:

**Tambahkan script ini sebelum closing `</body>` tag:**

```html
<!-- Helpdesk Widget -->
<script src="path/to/helpdesk/public/js/widget.js"></script>
```

**Contoh:**
```html
<!DOCTYPE html>
<html>
<head>
    <title>My Website</title>
</head>
<body>
    <!-- Your content -->
    
    <!-- Helpdesk Widget -->
    <script src="http://localhost/helpdesk/public/js/widget.js"></script>
</body>
</html>
```

Floating button akan langsung muncul di bottom-right corner website Anda!

## 🛠️ API Documentation

### 1. Create Ticket
**Endpoint:** `POST /src/api/create-ticket.php`

**Request:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "08123456789",
    "subject": "Bantuan Login",
    "message": "Saya lupa password"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Ticket created successfully",
    "data": {
        "ticket_number": "TK-20251129-00001",
        "ticket_id": 1,
        "customer_id": 1
    }
}
```

### 2. Get Messages
**Endpoint:** `GET /src/api/get-messages.php?ticket_number=TK-20251129-00001`

**Response:**
```json
{
    "success": true,
    "message": "Messages retrieved successfully",
    "data": {
        "ticket": {
            "id": 1,
            "ticket_number": "TK-20251129-00001",
            "subject": "Bantuan Login",
            "status": "open"
        },
        "messages": [
            {
                "id": 1,
                "message": "Saya lupa password",
                "sender_type": "customer",
                "sender_name": "John Doe",
                "created_at": "2024-11-29 10:30:00"
            }
        ]
    }
}
```

### 3. Send Message
**Endpoint:** `POST /src/api/send-message.php`

**Request:**
```json
{
    "ticket_number": "TK-20251129-00001",
    "message": "Silakan reset password melalui link di email Anda",
    "sender_type": "customer"
}
```

## 🎨 Customization

### Mengubah Warna Widget

Edit `public/css/widget.css`, cari:
```css
.helpdesk-btn-main {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

Ubah warna sesuai preferensi Anda.

### Mengubah Posisi Widget

Edit `public/js/widget.js`, cari di constructor:
```javascript
bottom: 30px;
right: 30px;
```

Ubah nilai sesuai kebutuhan.

## 📊 Database Schema

### Customers Table
```sql
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

### Tickets Table
```sql
CREATE TABLE tickets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ticket_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    status ENUM('open', 'in_progress', 'resolved', 'closed'),
    priority ENUM('low', 'medium', 'high'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
```

### Messages Table
```sql
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ticket_id INT NOT NULL,
    sender_type ENUM('customer', 'admin') NOT NULL,
    sender_id INT NOT NULL,
    message LONGTEXT NOT NULL,
    attachment_url VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

## 🔐 Security Notes

1. **Password Hashing**: Default admin password sudah di-hash dengan `password_verify()`
2. **Input Sanitization**: Semua input di-sanitize dengan `sanitizeInput()`
3. **SQL Injection Prevention**: Menggunakan prepared statements
4. **Session Management**: Admin login menggunakan PHP sessions

## 🚀 Deployment Checklist

- [ ] Update database credentials di `src/config/database.php`
- [ ] Change default admin password
- [ ] Set proper file permissions (uploads folder 755)
- [ ] Enable HTTPS
- [ ] Setup error logging
- [ ] Test all APIs
- [ ] Test widget integration on different browsers

## 📞 Fitur yang Bisa Ditambah

- [ ] File attachment support
- [ ] Typing indicator
- [ ] Read receipt
- [ ] Agent assignment
- [ ] Auto-reply
- [ ] Email notifications
- [ ] Video/voice call
- [ ] Rating system
- [ ] Analytics dashboard
- [ ] Mobile app

## 📝 License

© 2024 MTsN 11 Majalengka

## 👨‍💻 Support

Hubungi administrator untuk support lebih lanjut.
