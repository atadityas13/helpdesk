# ✅ Login Fix - Helpdesk

## 🔧 Masalah yang Diperbaiki

**Issue**: Halaman login tidak bisa masuk

**Root Cause**: Mismatch antara form dan API:
- Form mengirim: `email` + `password`
- API mengharapkan: `username` + `password`
- Function `authenticateAdmin()` hanya cek `username`

## ✨ Solusi yang Diterapkan

### 1. Fix API Login Handler
**File**: `src/api/login.php`
- Ubah parameter dari `$_POST['username']` → `$_POST['email']`
- Update validation message untuk konsistensi

### 2. Update Authentication Function
**File**: `src/middleware/auth.php`
- Update function signature: `authenticateAdmin($identifier, $password)` 
- Query sekarang support login dengan **email OR username**:
  ```sql
  WHERE username = ? OR email = ?
  ```
- Update error message: "Email/Username atau password salah"

## 🔑 Default Credentials

Gunakan credentials ini untuk login:

| Item | Value |
|------|-------|
| **Email** | admin |
| **Username** | admin |
| **Password** | admin123 |

❌ **PENTING**: Ganti password immediately setelah login!

## 🧪 Testing

### Cara 1: Otomatis via Test Page
1. Buka browser → `http://localhost/helpdesk/test-login.php`
2. Page akan show status database dan admin accounts
3. Test login dengan form di halaman (opsional)

### Cara 2: Manual Testing
1. Buka `http://localhost/helpdesk/login.php`
2. Isi email: `admin`
3. Isi password: `admin123`
4. Klik Login
5. Seharusnya redirect ke dashboard

## 🐛 Troubleshooting

### Kalau masih tidak bisa login:

**1. Check database connection**
```
Buka: test-login.php
Lihat: "Database Status" section
```

**2. Check admin account exists**
```
Buka: test-login.php
Lihat: "Admin Accounts Found"
Jika 0, buat admin baru via SQL
```

**3. Check browser console**
```
Tekan F12 → Console tab
Lihat ada error JavaScript apa tidak
```

**4. Check network request**
```
Tekan F12 → Network tab
Klik login dan lihat response dari src/api/login.php
```

**5. Password mungkin error**
```
Reset password admin:
php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"

Kemudian:
UPDATE admins SET password = '[HASH_RESULT]' WHERE username = 'admin';
```

## 📊 Files Modified

| File | Change |
|------|--------|
| `src/api/login.php` | Line 21-32: Ubah username → email |
| `src/middleware/auth.php` | Line 15-45: Support login dengan email OR username |
| `login.php` | ✓ Tidak ada perubahan (sudah benar) |

## 📝 Summary

✅ Login form sekarang bisa terima baik **email** maupun **username**
✅ API login fixed untuk menerima email parameter
✅ Authentication function di-update untuk support keduanya
✅ Error messages konsisten
✅ Test page dibuat untuk debugging

Sekarang login seharusnya berfungsi dengan baik!

---

**Jika ada pertanyaan atau masalah**, buka `test-login.php` untuk diagnosis lengkap.
