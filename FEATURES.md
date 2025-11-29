# 📊 Helpdesk MTsN 11 Majalengka

Sistem support berbasis web dengan floating button widget dan admin dashboard untuk manajemen ticket support.

## 🎯 Fitur Utama

### Untuk Pengguna
- ✅ Floating button yang selalu tersedia di website
- ✅ Chat interface seperti WhatsApp
- ✅ Automatic ticket number generation
- ✅ Resume chat dengan nomor ticket
- ✅ Real-time message updates
- ✅ Responsive di mobile dan desktop

### Untuk Admin
- ✅ Dashboard dengan statistik
- ✅ Manajemen ticket secara real-time
- ✅ Chat interface dengan pengguna
- ✅ Management FAQ/Knowledge Base
- ✅ Authentication & Authorization
- ✅ Ticket status tracking

---

## 🚀 Quick Start

### Prerequisites
- PHP 7.4+
- MySQL 5.7+
- Web Server (Apache/Nginx)

### Installation

1. **Clone Repository**
   ```bash
   git clone https://github.com/atadityas13/helpdesk.git
   cd helpdesk
   ```

2. **Setup Database**
   ```bash
   # Import database.sql ke MySQL
   mysql -u username -p database_name < database.sql
   ```

3. **Configure Database Connection**
   - Edit `src/config/database.php`
   - Update host, user, password, dan database name

4. **Access Application**
   - Admin Login: `http://your-server.com/helpdesk/login.php`
   - Default Credentials: 
     - Username: `admin`
     - Password: `password123`

---

## 📁 File Structure

```
helpdesk/
├── 📄 Core Files
│   ├── index.php                 Landing page
│   ├── login.php                 Admin login page
│   ├── database.sql              Database schema & initial data
│
├── 📂 src/
│   ├── config/
│   │   ├── database.php          Database connection
│   │   └── config.example.php    Config template
│   │
│   ├── admin/
│   │   ├── dashboard.php         Admin dashboard
│   │   ├── manage-tickets.php    Ticket management & chat
│   │   └── faqs.php              FAQ management
│   │
│   ├── api/
│   │   ├── create-ticket.php     Create new ticket
│   │   ├── get-messages.php      Fetch messages
│   │   └── send-message.php      Send new message
│   │
│   ├── helpers/
│   │   ├── functions.php         Utility functions
│   │   └── ticket.php            Ticket functions
│   │
│   └── middleware/
│       └── auth.php              Authentication & authorization
│
├── 📂 public/
│   ├── js/
│   │   └── widget.js             Floating button widget
│   │
│   └── css/
│       ├── widget.css            Widget styling
│       └── dashboard.css         Dashboard styling
│
├── 📂 logs/                       Log files
│
└── 📄 Documentation
    ├── README.md                 Main documentation
    ├── INSTALLATION.md           Installation guide
    └── API.md                    API documentation
```

---

## 🔌 API Endpoints

### Create Ticket
```
POST /src/api/create-ticket.php
Body: {
  "name": "Customer Name",
  "email": "customer@email.com",
  "phone": "62812345678",
  "subject": "Issue Title",
  "message": "Issue Description"
}
Response: { "ticket_number": "TK-20251129-XXXXX" }
```

### Get Messages
```
GET /src/api/get-messages.php?ticket_number=TK-20251129-XXXXX
Response: {
  "ticket": { "id", "ticket_number", "subject", "status", ... },
  "messages": [ { "sender_type", "message", "created_at", ... } ]
}
```

### Send Message
```
POST /src/api/send-message.php
Body: {
  "ticket_number": "TK-20251129-XXXXX",
  "sender_type": "admin|customer",
  "sender_id": 1,
  "message": "Message content"
}
Response: { "success": true, "message_id": 123 }
```

---

## 🌐 Widget Integration

Tambahkan code berikut ke website Anda untuk menampilkan floating button:

```html
<!-- Helpdesk Widget -->
<link rel="stylesheet" href="http://helpdesk.mtsn11majalengka.sch.id/public/css/widget.css">
<script src="http://helpdesk.mtsn11majalengka.sch.id/public/js/widget.js"></script>

<script>
  // Initialize widget
  const widget = new HelpdeskWidget({
    serverUrl: 'http://helpdesk.mtsn11majalengka.sch.id',
    apiUrl: 'http://helpdesk.mtsn11majalengka.sch.id/src/api',
    buttonPosition: 'bottom-right'
  });
  widget.init();
</script>
```

---

## 🗄️ Database Schema

### Tables
- **customers** - Data pengguna yang membuat ticket
- **tickets** - Semua support requests
- **messages** - Chat messages antara customer dan admin
- **admins** - Admin users untuk dashboard
- **faqs** - FAQ/Knowledge base

### Relationships
```
customers
  ↓ (1:N)
tickets
  ↓ (1:N)
messages
```

---

## 🔐 Security Features

- ✅ Prepared statements (SQL injection prevention)
- ✅ Bcrypt password hashing
- ✅ Session-based authentication
- ✅ Input validation & sanitization
- ✅ CORS headers (API protection)
- ✅ Unique ticket number generation

---

## 📊 Technology Stack

| Component | Technology |
|-----------|-----------|
| Backend | PHP 7.4+ |
| Database | MySQL 5.7+ |
| Frontend | HTML5, CSS3, JavaScript (ES6+) |
| Hashing | bcrypt (PASSWORD_BCRYPT) |
| Authentication | Session-based |
| API | RESTful |
| Styling | Custom CSS3 |

---

## 🤝 Support

Untuk masalah atau pertanyaan, silakan:
1. Check dokumentasi di file `README.md` dan `INSTALLATION.md`
2. Review API documentation di file `API.md`
3. Contact development team

---

## 📝 License

Copyright © 2025 MTsN 11 Majalengka. All rights reserved.

---

## 🚀 Deployment

**Live Server:** http://helpdesk.mtsn11majalengka.sch.id

**GitHub Repository:** https://github.com/atadityas13/helpdesk

**Last Updated:** November 29, 2025
