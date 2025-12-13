# Admin Chat Fix - Testing Guide

## Masalah
Tiket di sisi admin tidak bisa diklik untuk membuka chat.

## Solusi yang Diterapkan

### 1. Enhanced selectTicket() Function
- Ditambahkan logging untuk debug
- Added null-check untuk element
- Better error handling untuk state management

### 2. Improved Error Handling
- loadTicketDetails() sekarang menampilkan error message
- loadTicketMessages() sekarang log semua response
- Both functions sekarang handle fetch errors dengan SweetAlert2

### 3. Better HTML Structure  
- Added `role="button"` untuk accessibility
- Added `tabindex="0"` untuk keyboard navigation
- Added `return false;` untuk prevent default behavior
- Cast ticket ID ke integer dengan `(int)` untuk safety

### 4. Improved Message Display
- Try-catch wrapper untuk rendering messages
- Fallback untuk date formatting
- Better error state display

## Cara Testing

### Test 1: Check Admin Session
```
1. Buka: http://yoursite/helpdesk/admin-test.php
2. Verify "LOGGED IN" status
3. Verify database connection OK
4. Verify API endpoints respond
```

### Test 2: Test Ticket Click
```
1. Login ke admin panel
2. Go to Manage Tickets (src/admin/manage-tickets.php)
3. Open browser console (F12)
4. Click on any ticket dari list
5. Verify console menunjukkan:
   - "selectTicket called with ID: [number]"
   - "Loading ticket details for ID: [number]"
   - "API response status: 200"
   - "Ticket data: {...}"
   - "Loading ticket messages for ID: [number]"
   - "Messages API response status: 200"
   - "Messages data: {...}"
```

### Test 3: Verify UI Changes
```
1. Click ticket di list
2. Verify:
   - Ticket item mendapat class "active" (highlight)
   - Chat input area menjadi visible
   - Ticket title menampilkan nomor ticket
   - Ticket subtitle menampilkan nama customer dan subject
   - Chat messages area menampilkan pesan (atau "Belum ada pesan")
```

### Test 4: Send Message
```
1. Pastikan ticket sudah diklik
2. Type pesan di input area
3. Click "Kirim" button
4. Verify:
   - Pesan muncul di chat dengan style admin (right-aligned, gradient)
   - Success notification muncul
   - Input field clear
5. Go to customer side
6. Verify customer sees message dalam 2 detik
```

## Files Modified

| File | Changes |
|------|---------|
| src/admin/manage-tickets.php | Added debug logging, improved error handling, better HTML attributes |
| admin-test.php | Created for testing admin access and APIs |

## Browser Console Log Format

Ketika ticket diklik, console akan menampilkan:

```
selectTicket called with ID: 1
Loading ticket details for ID: 1
API response status: 200
Ticket data: {success: true, data: {...}}
Loading ticket messages for ID: 1
Messages API response status: 200
Messages data: {success: true, data: {messages: [...]}}
```

## Troubleshooting

### Jika ticket tidak bisa diklik:

1. **Check browser console** - Cari error messages
2. **Verify session** - Buka admin-test.php, pastikan logged in
3. **Verify API** - Check di admin-test.php apakah API respond dengan status 200
4. **Check network tab** - Lihat apakah fetch request ke API berhasil atau error

### Jika console log tidak muncul:

- JavaScript error mungkin terjadi sebelumnya
- Check browser console untuk error yang lebih awal
- Try refresh page dengan Ctrl+Shift+R (hard refresh)

### Jika API returns error:

- Buka API URL langsung di browser: `src/api/get-ticket.php?id=1`
- Pastikan sudah login (tidak ada redirect ke login.php)
- Check database apakah ada data

## Next Steps

Setelah testing berhasil:
1. Test message flow end-to-end
2. Test dengan multiple tickets simultaneously
3. Verify real-time updates setiap 2 detik
4. Test message sending dari admin
5. Verify customer menerima pesan

## Emergency Debug

Jika masih ada issue, add ini ke manage-tickets.php untuk more detailed logging:

```javascript
// Add ini di top of script tag
const OriginalFetch = fetch;
window.fetch = function(...args) {
    console.log('FETCH:', args[0], args[1]);
    return OriginalFetch(...args).then(response => {
        console.log('RESPONSE:', response.status, response.statusText);
        return response;
    });
};
```

---

**Status**: ✅ All debugging enhancements applied. Ready for testing!
