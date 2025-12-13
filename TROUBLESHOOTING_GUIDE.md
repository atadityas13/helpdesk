# Admin Chat Click - Complete Troubleshooting Guide

## Masalah
Tiket tidak bisa diklik di halaman manage-tickets.php

## Root Cause Analysis
Kemungkinan penyebab:
1. **Tidak ada data tiket dalam database** - Query tidak mengembalikan hasil
2. **HTML tidak di-render** - Ticket items tidak ditampilkan di page
3. **JavaScript tidak berjalan** - selectTicket() function tidak terpanggil
4. **API error** - fetch ke API gagal

## Langkah Debugging

### Step 1: Check Database & Tickets
```
1. Buka: http://yoursite/helpdesk/quick-test.php
2. Lihat statistik:
   - Total Tickets: Harus > 0
   - Active Tickets: Harus > 0
   - First Ticket: Harus ada
   
JIKA TIDAK ADA TICKET:
   → Buka index.php dan buat ticket baru
   → Kembali ke quick-test.php dan refresh
```

### Step 2: Verify Ticket Rendering
```
1. Buka: http://yoursite/helpdesk/src/admin/manage-tickets.php
2. Lihat sidebar kiri:
   - Apakah ada daftar tiket ditampilkan?
   - Apakah bisa di-hover (berubah warna)?
   - Apakah judul dan status terlihat?

JIKA TIKET TIDAK TERLIHAT:
   → Problem: HTML tidak di-render
   → Solution: Check database query (sebelumnya di-check dengan quick-test.php)
```

### Step 3: Check Browser Console
```
1. Tekan F12 untuk buka DevTools
2. Go to Console tab
3. Refresh halaman manage-tickets.php
4. Lihat apakah ada log:
   "manage-tickets.php loaded"
   "Current path: /helpdesk/src/admin/manage-tickets.php"

JIKA TIDAK ADA LOG:
   → Problem: JavaScript tidak load
   → Solution: Check apakah CDN links bekerja (Bootstrap, SweetAlert, dll)
```

### Step 4: Test Click Handler
```
1. Sudah di manage-tickets.php dengan DevTools Console terbuka
2. Klik pada salah satu ticket di sidebar
3. Lihat di Console, seharusnya muncul:
   
   ✓ selectTicket called with ID: [number]
   ✓ Type of ticketId: number
   ✓ Ticket item highlighted
   ✓ Chat input area shown
   ✓ Loading ticket details for ID: [number]
   ✓ API response status: 200
   ✓ Ticket data: {...}
   ✓ Loading ticket messages for ID: [number]
   ✓ Messages API response status: 200
   ✓ Messages data: {...}

JIKA TIDAK ADA LOG:
   → Click handler tidak trigger
   → Check: Apakah ticket item HTML-nya OK?
   → Try: Reload browser dengan Ctrl+Shift+R (hard refresh)
```

### Step 5: Check UI Response
```
Ketika ticket diklik, yang seharusnya terjadi:
□ Ticket item mendapat highlight (background warna) dan left border
□ Chat input area menjadi visible di sisi kanan
□ Ticket title menampilkan nomor tiket (e.g., "TK-20251213-XXXXX")
□ Ticket subtitle menampilkan nama customer dan subject
□ Chat messages area mulai memuat pesan (atau "Belum ada pesan")

JIKA TIDAK TERJADI:
   → selectTicket() tidak berfungsi dengan benar
   → Check console untuk error messages
```

### Step 6: API Connectivity Test
```
1. Buka: http://yoursite/helpdesk/admin-diagnostic.php
2. Lihat "Available Tickets for Testing"
3. Klik salah satu ticket, kemudian klik "Test API Endpoints"
4. Lihat di output apakah API respond dengan success (200)

JIKA API ERROR:
   → Problem: Server-side API issue
   → Check: Apakah sudah login sebagai admin?
   → Check: Apakah session masih valid?
```

## Checklist Lengkap

### Database Level
- [ ] MySQL/database server running
- [ ] Database `helpdesk` atau sesuai .env ada
- [ ] Table `tickets` ada dan punya data
- [ ] Table `customers` ada dengan data
- [ ] Table `messages` ada
- [ ] Foreign keys configured dengan benar

### Application Level  
- [ ] File: src/admin/manage-tickets.php ada
- [ ] File: src/api/get-ticket.php ada
- [ ] File: src/api/get-ticket-messages.php ada
- [ ] Session middleware berfungsi
- [ ] Admin sudah login (session valid)

### JavaScript Level
- [ ] CDN links loading (Bootstrap, SweetAlert2, Font Awesome)
- [ ] selectTicket() function defined
- [ ] loadTicketDetails() function defined
- [ ] loadTicketMessages() function defined
- [ ] displayMessages() function defined
- [ ] Event handler onclick="return selectTicket(...)" ada

### HTML/CSS Level
- [ ] Ticket items rendered dalam .ticket-list-body
- [ ] .ticket-item CSS styling applied (cursor: pointer, etc)
- [ ] .chat-input-area CSS styling applied
- [ ] Icons dari Font Awesome terlihat

## Quick Fixes

### Fix 1: Hard Refresh Browser
```
Ctrl + Shift + R (Windows)
Cmd + Shift + R (Mac)
```

### Fix 2: Clear Browser Cache
```
1. Open DevTools (F12)
2. Right-click refresh button
3. Select "Empty cache and hard refresh"
```

### Fix 3: Check Console for Errors
```
Jika ada error di console seperti:
- "Uncaught SyntaxError"
- "$ is not defined"
- "fetch is not a function"

→ Problem di JavaScript
→ Check CDN links dan script tags
```

### Fix 4: Verify Session
```
1. Buka: http://yoursite/helpdesk/admin-test.php
2. Harus menunjukkan "LOGGED IN"
3. Jika "NOT LOGGED IN": 
   → Go to login.php dan login lagi
```

## Testing Flow

```
START
  ↓
1. quick-test.php → Check DB & Statistics
  ↓
  Tickets ada? → NO → Create ticket di index.php → Balik ke step 1
  ↓ YES
2. manage-tickets.php → Check UI rendering
  ↓
  Tiket terlihat? → NO → Problem: DB atau HTML rendering
  ↓ YES
3. Open DevTools Console
  ↓
4. Klik ticket
  ↓
  "selectTicket called" log? → NO → Problem: onClick handler
  ↓ YES
5. "API response 200" log? → NO → Problem: API atau session
  ↓ YES
6. Chat interface muncul? → NO → Problem: UI update
  ↓ YES
✓ EVERYTHING WORKS
```

## Files Modified

| File | Purpose |
|------|---------|
| src/admin/manage-tickets.php | Enhanced selectTicket() dengan better logging dan error handling |
| admin-diagnostic.php | Diagnostic page untuk test specific aspects |
| quick-test.php | Quick overview of system status |
| ADMIN_CHAT_FIX.md | Documentation |

## Still Not Working?

Jika sudah follow semua steps dan masih tidak berfungsi:

1. **Check server logs**
   ```
   Lihat: error_log file atau server PHP error log
   Cari: error messages yang related
   ```

2. **Manual API Test**
   ```
   Buka di browser:
   http://yoursite/helpdesk/src/api/get-ticket.php?id=1
   
   Harus return JSON:
   {"success": true, "data": {...}}
   ```

3. **Check Database Query**
   ```
   Di phpMyAdmin atau MySQL client, run:
   SELECT t.id, t.ticket_number, c.name 
   FROM tickets t 
   JOIN customers c ON t.customer_id = c.id 
   WHERE t.status != 'closed';
   
   Harus return data
   ```

4. **Test dengan Vanilla JavaScript**
   ```
   Di browser console, coba:
   
   fetch('src/api/get-ticket.php?id=1')
     .then(r => r.json())
     .then(d => console.log(d));
   
   Harus log JSON response
   ```

## Expected Behavior

Ketika semuanya berfungsi dengan baik:

1. **Halaman Load** → Console: "manage-tickets.php loaded"
2. **Lihat Tickets** → List tiket visible di sidebar
3. **Hover Ticket** → Background berubah warna
4. **Click Ticket** → 
   - Ticket highlight dengan border/background
   - Chat input muncul
   - Ticket info load di atas chat
   - Messages load di chat area
   - Console: "selectTicket called with ID: [number]"
5. **Type Message** → Bisa ketik
6. **Send Message** → Success notification muncul
7. **Message Appear** → Pesan muncul di chat (styled as admin)
8. **Auto-Refresh** → Pesan auto-refresh setiap 2 detik

---

**Next Action**: 
1. Buka quick-test.php
2. Follow langkah testing step by step
3. Report di console output apakah ada error
4. Share console logs untuk debugging lebih lanjut

