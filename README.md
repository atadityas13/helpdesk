# 🎟️ Helpdesk System - MTsN 11 Majalengka

**Status**: ✅ Production Ready | **Version**: 1.0 | **Build**: December 2025

---

## 📋 Daftar Isi

1. [Overview](#overview)
2. [Features](#features)
3. [Technology Stack](#technology-stack)
4. [Quick Start](#quick-start)
5. [Project Structure](#project-structure)
6. [Database Schema](#database-schema)
7. [API Documentation](#api-documentation)
8. [Security Features](#security-features)
9. [Troubleshooting](#troubleshooting)
10. [Support & Maintenance](#support--maintenance)

---

## 📝 Overview

Sistem ticketing support online berbasis web untuk **MTsN 11 Majalengka** yang memungkinkan sivitas akademika (siswa, guru, admin) untuk:

✅ Membuat ticket support  
✅ Chat real-time dengan staff IT  
✅ Lacak status ticket  
✅ Akses knowledge base (FAQ)  
✅ Upload file attachments  

### Target Users

- **Customers**: Siswa, Guru, Staf Administrasi
- **Admins**: Staff IT dan Support Team

---

## ✨ Features

### 👥 Customer Features

1. **Landing Page**
   - Informasi layanan
   - FAQ knowledge base
   - Quick access buttons

2. **Create Ticket**
   - Form lengkap (nama, email, phone, subject, message)
   - Priority selection (low/medium/high)
   - Auto-ticket number generation (TK-YYYYMMDD-XXXXX)

3. **Real-time Chat**
   - Chat interface yang intuitif
   - Message history
   - Typing indicators
   - File attachment support (up to 5MB)
   - Auto-refresh messages

4. **Continue Previous Chat**
   - Masukkan nomor ticket
   - Reload conversation history
   - Resume support session

5. **FAQ Search**
   - Self-service knowledge base
   - Reduce ticket volume
   - Categories (Support, Teknologi, Umum, Lainnya)

### 👨‍💼 Admin Features

1. **Dashboard**
   - Statistics (open, in-progress, resolved, closed)
   - Recent activity feed
   - Quick metrics
   - Total customers & messages

2. **Ticket Management**
   - List semua tickets dengan status
   - Real-time chat interface
   - 2-column layout (tickets list + chat)
   - Status update (open → in_progress → resolved → closed)
   - Message history
   - Assign tickets

3. **FAQ Management**
   - Create/Read/Update/Delete FAQs
   - Category management
   - Track views
   - Active/inactive toggle

4. **Session Management**
   - Auto-logout on inactivity (1 hour)
   - Secure authentication
   - Session timeout warnings

---

## 🛠️ Technology Stack

### Backend
```
PHP 7.4+
MySQL 5.7+ / MariaDB 10.3+
```

### Frontend
```
HTML5
CSS3 (Responsive)
Vanilla JavaScript (No jQuery)
```

### Server
```
Apache 2.4+ / Nginx
```

### Security
```
bcrypt (Password hashing)
CSRF tokens
Prepared statements (SQL Injection prevention)
htmlspecialchars (XSS prevention)
Rate limiting
Session management
```

---

## 🚀 Quick Start

### Prerequisites
- MySQL/MariaDB installed
- PHP 7.4+ installed
- Web server (Apache/Nginx)
- Text editor
- Terminal access

### Setup (15 minutes)

#### 1. Create Database
```bash
mysql -u root -p

# In MySQL prompt:
CREATE DATABASE mtsnmaja_helpdesk CHARACTER SET utf8mb4;
USE mtsnmaja_helpdesk;
SOURCE database.sql;
SHOW TABLES;  # Verify
```

#### 2. Configure Environment
```bash
# Copy .env.example to .env
cp .env.example .env

# Edit .env with your database credentials
# DB_HOST=localhost
# DB_USER=root
# DB_PASS=your_password
# DB_NAME=mtsnmaja_helpdesk
```

#### 3. Set Permissions
```bash
# Linux/Mac
chmod 755 public/uploads
chmod 755 logs

# Windows: Right-click folder → Properties → Security → Full Control
```

#### 4. Test Installation
```
Landing:  http://localhost/helpdesk/
Login:    http://localhost/helpdesk/login.php
Username: admin
Password: admin123
```

---

## 📁 Project Structure

```
helpdesk/
├── .env.example                      # Environment template
├── .gitignore                       # Git ignore rules
├── database.sql                     # Database schema + seed data
├── README.md                        # This file
├── README_SETUP.md                  # Setup guide
│
├── index.php                        # Landing page
├── login.php                        # Admin login page
├── logout.php                       # Logout script
├── chat.php                         # Customer chat page
│
├── src/
│   ├── config/
│   │   ├── .env.php                 # Environment loader
│   │   └── database.php             # DB connection (Singleton)
│   │
│   ├── middleware/
│   │   ├── session.php              # Session management (3600s timeout)
│   │   ├── csrf.php                 # CSRF protection
│   │   ├── auth.php                 # Authentication & authorization
│   │   └── rate-limit.php           # Rate limiting (login, ticket, message)
│   │
│   ├── helpers/
│   │   ├── functions.php            # General utilities
│   │   ├── validator.php            # Input validation
│   │   ├── ticket.php               # Ticket operations
│   │   ├── admin-status.php         # Admin status checker
│   │   └── api-response.php         # Standardized JSON responses
│   │
│   ├── api/
│   │   ├── login.php                # Admin login
│   │   ├── create-ticket.php        # POST: Create ticket
│   │   ├── send-message.php         # POST: Send message (customer)
│   │   ├── send-admin-message.php   # POST: Send message (admin)
│   │   ├── get-messages.php         # GET: Fetch messages
│   │   ├── get-ticket.php           # GET: Get ticket details
│   │   ├── get-ticket-messages.php  # GET: Get ticket messages (admin)
│   │   ├── update-ticket-status.php # POST: Update ticket status
│   │   ├── get-faqs.php             # GET: Get FAQs
│   │   ├── create-faq.php           # POST: Create FAQ
│   │   ├── update-faq.php           # POST: Update FAQ
│   │   ├── get-faq.php              # GET: Get FAQ details
│   │   └── delete-faq.php           # POST: Delete FAQ
│   │
│   └── admin/
│       ├── dashboard.php            # Admin dashboard
│       ├── manage-tickets.php       # Ticket management
│       └── faqs.php                 # FAQ management
│
├── public/
│   ├── css/
│   │   └── style.css                # Global styles
│   ├── js/
│   │   └── widget.js                # Widget (optional)
│   └── uploads/                     # User file uploads
│
└── logs/
    └── activity.log                 # Activity tracking
```

---

## 🗄️ Database Schema

### Tables

1. **customers** - User data
   - id, name, email, phone, created_at

2. **tickets** - Support tickets
   - id, ticket_number (UNIQUE), customer_id (FK), subject, status, priority, assigned_to, created_at, updated_at
   - Status: open → in_progress → resolved → closed

3. **messages** - Chat history
   - id, ticket_id (FK), sender_type, sender_id, message, attachment_url, is_read, created_at

4. **admins** - Staff accounts
   - id, username (UNIQUE), password (bcrypt), email, role, is_active, is_online, created_at

5. **faqs** - Knowledge base
   - id, question, answer, category, is_active, views, created_at, updated_at

6. **rate_limits** - Rate limiting
   - id, action, identifier, count, expires_at

7. **admin_viewing** - Admin viewing status
   - id, ticket_id, admin_id, started_at, last_seen_at

---

## 📡 API Documentation

### Customer APIs

#### POST /src/api/create-ticket.php
Create new support ticket
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "08123456789",
  "subject": "Masalah Login",
  "message": "Saya tidak bisa login ke sistem",
  "priority": "high"
}
```

#### POST /src/api/send-message.php
Send customer message
```json
{
  "ticket_number": "TK-20241213-12345",
  "message": "Sudah coba reset password?",
  "attachment": "file" (optional)
}
```

#### GET /src/api/get-messages.php
Get ticket messages
```
?ticket_number=TK-20241213-12345
```

#### GET /src/api/get-faqs.php
Get all FAQs

### Admin APIs

#### POST /src/api/login.php
Admin authentication
```json
{
  "username": "admin",
  "password": "admin123"
}
```

#### POST /src/api/send-admin-message.php
Send admin response (requires auth)
```json
{
  "ticket_id": 1,
  "message": "Silakan coba....",
  "csrf_token": "token"
}
```

#### POST /src/api/update-ticket-status.php
Update ticket status (requires auth)
```json
{
  "ticket_id": 1,
  "status": "resolved",
  "csrf_token": "token"
}
```

#### FAQ APIs
- POST /src/api/create-faq.php
- POST /src/api/update-faq.php
- POST /src/api/delete-faq.php
- GET /src/api/get-faq.php

---

## 🔐 Security Features

### 1. Authentication
- Bcrypt password hashing (cost=10)
- Session-based authentication
- Auto-logout on inactivity (3600s)

### 2. CSRF Protection
- Token generation per session
- Token validation on POST requests
- hash_equals() for timing-safe comparison

### 3. Input Validation
- Email validation (FILTER_VALIDATE_EMAIL)
- Phone validation (regex pattern)
- Length validation (min/max)
- Type validation (integer, numeric)
- Sanitization (htmlspecialchars)

### 4. SQL Injection Prevention
- Prepared statements (mysqli)
- Parameter binding
- Escape user input

### 5. XSS Protection
- htmlspecialchars() encoding
- Output escaping
- Content-Security-Policy headers

### 6. Rate Limiting
- Login attempts: 5 per 15 minutes
- Ticket creation: 3 per hour
- Message sending: 10 per 5 minutes
- IP-based and identifier-based limiting

### 7. Session Security
- Secure session cookies
- HttpOnly flag
- SameSite=Lax
- Session timeout tracking

### 8. File Upload Security
- File type validation (whitelist)
- File size validation (max 5MB)
- Random filename generation
- Separate uploads directory

---

## 🔍 Troubleshooting

### Database Connection Error
```
✓ Check .env configuration
✓ Verify MySQL service running
✓ Test connection: mysql -u root -p
✓ Check port (default 3306)
✓ Verify database exists: SHOW DATABASES;
```

### Login Failed
```
✓ Check admin exists: SELECT * FROM admins;
✓ Verify password hash correct
✓ Clear browser cache
✓ Check Network tab (F12)
```

### Chat Messages Not Showing
```
✓ Verify ticket_number format (TK-YYYYMMDD-XXXXX)
✓ Check database: SELECT * FROM messages;
✓ Verify get-messages.php response
✓ Check browser console (F12 → Console)
```

### File Upload Failed
```
✓ Check permissions: chmod 777 public/uploads
✓ Check file size limit
✓ Check allowed extensions
✓ Verify disk space
```

### Session Timeout Issues
```
✓ Check SESSION_TIMEOUT in .env (default 3600)
✓ Verify session.save_path writable
✓ Clear PHP session files
✓ Check system clock/timezone
```

---

## 📊 Performance Optimization

### Database
- Indexed columns: ticket_number, status, created_at, customer_id
- Prepared statements (prevent SQL injection)
- Connection pooling (Singleton pattern)
- Query optimization with JOINs

### Frontend
- Vanilla JavaScript (no heavy libraries)
- Auto-refresh intervals (2-3 seconds)
- Lazy loading (optional)
- Minified CSS

### Caching
- Browser cache for static assets
- Session caching for user data
- Database query results

---

## 📈 Scaling Considerations

### For 1000+ Users
- Add database read replicas
- Implement caching layer (Redis)
- Use load balancer
- Optimize queries
- Archive old tickets

### For Real-time Features
- WebSocket implementation
- Message queue (RabbitMQ)
- Live notification system
- Typing indicators

---

## 📋 Default Credentials

| Item | Value |
|------|-------|
| Admin Username | admin |
| Admin Password | admin123 |
| Database User | root |
| Database Name | mtsnmaja_helpdesk |
| Session Timeout | 3600 seconds (1 hour) |

⚠️ **IMPORTANT**: Change admin password immediately after setup!

---

## 🔄 Maintenance

### Daily
- Monitor error logs
- Check disk space
- Verify backup completion

### Weekly
- Review activity logs
- Check for unresolved tickets
- Update FAQ if needed
- Performance monitoring

### Monthly
- Database optimization
- Security patches
- Backup verification
- User management review

### Quarterly
- Full security audit
- Feature review
- Performance tuning
- Disaster recovery testing

---

## 📚 Documentation Files

1. **README.md** (This file) - Project overview
2. **README_SETUP.md** - Detailed setup guide
3. **QUICK_START.md** - Quick reference (10 min)
4. **PANDUAN_PEMBUATAN_ULANG.md** - Complete implementation guide
5. **DOKUMENTASI_TEKNIS.md** - Technical deep dive

---

## 💡 Tips & Best Practices

1. **Regular Backups**
   ```bash
   mysqldump -u root -p mtsnmaja_helpdesk > backup.sql
   ```

2. **Monitor Logs**
   ```bash
   tail -f logs/activity.log
   ```

3. **Update FAQ Regularly**
   - Based on common issues
   - Reduce ticket volume
   - Improve customer satisfaction

4. **Archive Old Tickets**
   - Move closed tickets after 3 months
   - Reduce database size
   - Improve query performance

5. **User Training**
   - Show how to create tickets
   - Explain priority levels
   - Guide through FAQ

---

## 🤝 Support

For issues or questions:
1. Check documentation files
2. Review logs in `/logs/`
3. Test with sample data
4. Check browser console (F12)

---

## 📄 License

Internal use only for MTsN 11 Majalengka

---

## 📞 Contact

**Developer**: IT Support Team  
**Email**: support@mtsnmaja.sch.id  
**Version**: 1.0  
**Last Updated**: December 2025  
**Status**: ✅ Production Ready

---

**🎉 Thank you for using Helpdesk System!**
