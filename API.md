# API Documentation - Helpdesk MTsN 11 Majalengka

## 📡 Base URL

```
http://localhost/helpdesk/src/api/
```

## 🔐 Authentication

Semua API endpoint tidak memerlukan authentication khusus. Untuk admin endpoints, authentication dilakukan via PHP session.

## 📤 Request Format

Semua requests menggunakan `Content-Type: application/json`

```javascript
fetch('http://localhost/helpdesk/src/api/endpoint.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        // data here
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

## 📥 Response Format

Semua responses return JSON format:

```json
{
    "success": true/false,
    "message": "Response message",
    "data": {
        // optional data
    }
}
```

---

## 🎫 Endpoints

### 1. Create Ticket

**Endpoint:** `POST /create-ticket.php`

**Description:** Membuat ticket baru dari user

**Request:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "08123456789",
    "subject": "Bantuan Login",
    "message": "Saya lupa password saya"
}
```

**Required Fields:**
- `name` (string) - Nama customer
- `email` (string) - Email customer
- `subject` (string) - Subjek ticket
- `message` (string) - Pesan awal

**Optional Fields:**
- `phone` (string) - No. telepon customer

**Response Success:**
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

**Response Error:**
```json
{
    "success": false,
    "message": "Email format tidak valid"
}
```

**Example JavaScript:**
```javascript
const createTicket = async () => {
    const response = await fetch('/helpdesk/src/api/create-ticket.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            name: 'John Doe',
            email: 'john@example.com',
            phone: '08123456789',
            subject: 'Bantuan Login',
            message: 'Saya lupa password'
        })
    });
    
    const data = await response.json();
    
    if (data.success) {
        console.log('Ticket created:', data.data.ticket_number);
    } else {
        console.error('Error:', data.message);
    }
};
```

---

### 2. Get Messages

**Endpoint:** `GET /get-messages.php`

**Description:** Mengambil semua messages untuk ticket tertentu

**Query Parameters:**
- `ticket_number` (string) - Nomor ticket

**URL Example:**
```
GET /get-messages.php?ticket_number=TK-20251129-00001
```

**Response Success:**
```json
{
    "success": true,
    "message": "Messages retrieved successfully",
    "data": {
        "ticket": {
            "id": 1,
            "ticket_number": "TK-20251129-00001",
            "customer_id": 1,
            "subject": "Bantuan Login",
            "status": "open",
            "priority": "medium",
            "created_at": "2024-11-29 10:30:00",
            "updated_at": "2024-11-29 10:35:00"
        },
        "messages": [
            {
                "id": 1,
                "ticket_id": 1,
                "sender_type": "customer",
                "sender_id": 1,
                "sender_name": "John Doe",
                "message": "Saya lupa password",
                "attachment_url": null,
                "is_read": false,
                "created_at": "2024-11-29 10:30:00"
            },
            {
                "id": 2,
                "ticket_id": 1,
                "sender_type": "admin",
                "sender_id": 1,
                "sender_name": "admin",
                "message": "Silakan cek email Anda untuk reset password",
                "attachment_url": null,
                "is_read": false,
                "created_at": "2024-11-29 10:32:00"
            }
        ]
    }
}
```

**Response Error:**
```json
{
    "success": false,
    "message": "Ticket not found"
}
```

**Example JavaScript:**
```javascript
const getMessages = async (ticketNumber) => {
    const response = await fetch(
        `/helpdesk/src/api/get-messages.php?ticket_number=${encodeURIComponent(ticketNumber)}`
    );
    
    const data = await response.json();
    
    if (data.success) {
        console.log('Ticket:', data.data.ticket);
        console.log('Messages:', data.data.messages);
        
        // Display messages
        data.data.messages.forEach(msg => {
            console.log(`${msg.sender_name}: ${msg.message}`);
        });
    } else {
        console.error('Error:', data.message);
    }
};

// Usage
getMessages('TK-20251129-00001');
```

---

### 3. Send Message

**Endpoint:** `POST /send-message.php`

**Description:** Mengirim pesan ke ticket (dari customer atau admin)

**Request:**
```json
{
    "ticket_number": "TK-20251129-00001",
    "message": "Terima kasih atas bantuannya",
    "sender_type": "customer",
    "sender_id": 1
}
```

**Required Fields:**
- `ticket_number` (string) - Nomor ticket
- `message` (string) - Isi pesan
- `sender_type` (enum) - "customer" atau "admin"

**Optional Fields:**
- `sender_id` (integer) - ID pengirim
- `attachment_url` (string) - URL file attachment

**Response Success:**
```json
{
    "success": true,
    "message": "Message sent successfully",
    "data": {
        "message_id": 2
    }
}
```

**Response Error:**
```json
{
    "success": false,
    "message": "Ticket not found"
}
```

**Example JavaScript (Customer):**
```javascript
const sendMessage = async (ticketNumber, message) => {
    const response = await fetch('/helpdesk/src/api/send-message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            ticket_number: ticketNumber,
            message: message,
            sender_type: 'customer'
        })
    });
    
    const data = await response.json();
    
    if (data.success) {
        console.log('Message sent, ID:', data.data.message_id);
        // Refresh messages
        getMessages(ticketNumber);
    } else {
        console.error('Error:', data.message);
    }
};

// Usage
sendMessage('TK-20251129-00001', 'Terima kasih!');
```

**Example PHP (Admin):**
```php
<?php
// In admin panel, after user sends message
require_once 'src/helpers/ticket.php';

$ticketId = 1;
$message = "Admin reply here";
$adminId = $_SESSION['admin_id']; // From session

$result = addMessageToTicket($conn, $ticketId, 'admin', $adminId, $message);

if ($result['success']) {
    echo "Message sent successfully";
} else {
    echo "Error: " . $result['message'];
}
?>
```

---

## 🔄 API Flow

### User Creating Ticket & Chat

```
1. User clicks floating button
   ↓
2. POST /create-ticket.php
   - Create customer (jika baru)
   - Create ticket with unique ticket_number
   - Create first message
   ↓
3. Response contains ticket_number
   - Save to localStorage
   - Open chat window
   ↓
4. GET /get-messages.php?ticket_number=TK-xxx
   - Fetch all messages for this ticket
   ↓
5. Display messages in chat window
   ↓
6. User types & sends message
   - POST /send-message.php
   ↓
7. Auto-refresh GET /get-messages.php setiap 3 detik
   - Get updated messages including admin replies
```

### Admin Replying to Customer

```
1. Admin logs in
   ↓
2. View Manage Tickets
   - GET tickets dari database
   ↓
3. Select ticket
   - GET /get-messages.php untuk ticket tersebut
   ↓
4. Admin types reply & submit
   - POST /send-message.php dengan sender_type='admin'
   - Update ticket status ke 'in_progress' (jika open)
   ↓
5. Auto-refresh untuk customer widget
   - GET /get-messages.php
   - Display admin reply
```

---

## 🧪 Test API dengan cURL

### Create Ticket
```bash
curl -X POST http://localhost/helpdesk/src/api/create-ticket.php \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "phone": "08123456789",
    "subject": "Test Subject",
    "message": "This is a test message"
  }'
```

### Get Messages
```bash
curl "http://localhost/helpdesk/src/api/get-messages.php?ticket_number=TK-20251129-00001"
```

### Send Message
```bash
curl -X POST http://localhost/helpdesk/src/api/send-message.php \
  -H "Content-Type: application/json" \
  -d '{
    "ticket_number": "TK-20251129-00001",
    "message": "This is a reply",
    "sender_type": "customer"
  }'
```

---

## 🚨 Error Codes

| Code | Message | Meaning |
|------|---------|---------|
| 400 | Invalid request method | Gunakan method yang benar (POST/GET) |
| 400 | All fields are required | Ada field yang kosong |
| 400 | Invalid email format | Format email tidak benar |
| 404 | Ticket not found | Nomor ticket tidak ada di database |
| 500 | Database error | Error di database, check logs |

---

## 📊 Response Time Guidelines

- Create Ticket: ~100-200ms
- Get Messages: ~50-100ms
- Send Message: ~80-150ms

---

## 🔒 Security Notes

1. **Input Validation**: Semua input di-validate & di-sanitize
2. **SQL Injection**: Menggunakan prepared statements
3. **XSS Protection**: Output di-escape
4. **Rate Limiting**: Belum diimplementasikan (untuk fase 2)

---

## 🚀 Future API Enhancements

- [ ] Attachment upload endpoint
- [ ] Search messages endpoint
- [ ] Close ticket endpoint
- [ ] Rate ticket endpoint
- [ ] Get FAQ endpoint
- [ ] Get ticket history endpoint
- [ ] Analytics endpoint
- [ ] Webhook support

---

## 📝 API Version

Current Version: **1.0**

Last Updated: 2024-11-29
