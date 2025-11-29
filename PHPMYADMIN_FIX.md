# 🔐 Fix Admin Password via phpMyAdmin

## Langkah-Langkah:

### 1. Generate Hash Baru
Akses file ini terlebih dahulu untuk generate hash:
- URL: `http://helpdesk.mtsn11majalengka.sch.id/fix_password.php`
- Atau: `http://helpdesk.mtsn11majalengka.sch.id/apply_password_fix.php`

Salin hash yang di-generate (contoh: `$2y$10$...`)

### 2. Buka phpMyAdmin
- URL: `http://helpdesk.mtsn11majalengka.sch.id/phpmyadmin/` atau sesuai dengan hosting
- Login dengan credentials hosting

### 3. Pilih Database
- Pilih database: **mtsnmaja_helpdesk**

### 4. Buka Tab SQL
- Klik tab **SQL** di bagian atas

### 5. Jalankan Perintah Update
Paste perintah ini dan replace `[HASH_DARI_STEP_1]` dengan hash yang sudah disalin:

```sql
UPDATE admins SET password = '[HASH_DARI_STEP_1]' WHERE username = 'admin';
```

**Contoh lengkap:**
```sql
UPDATE admins SET password = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86.jL5rKlLa' WHERE username = 'admin';
```

### 6. Klik GO
- Klik tombol **GO** untuk execute perintah

### 7. Verifikasi
Jalankan query untuk cek:
```sql
SELECT id, username, email, password FROM admins WHERE username = 'admin';
```

Harus terlihat password hash yang baru (dimulai dengan `$2y$10$...`)

### 8. Test Login
- Buka: `http://helpdesk.mtsn11majalengka.sch.id/login.php`
- Username: **admin**
- Password: **password123**
- Klik Login

Jika berhasil, akan redirect ke dashboard admin ✅

---

## ⚠️ Penting:
- Setiap kali access `fix_password.php` atau `apply_password_fix.php`, hash yang di-generate BERBEDA (ini normal, bcrypt feature)
- Gunakan HASH TERBARU dari file tersebut
- Pastikan COPY hash dengan benar (jangan ada spasi di awal/akhir)
- Setelah update, tunggu 5-10 detik baru coba login

## 🆘 Jika Masih Error:
1. Jalankan `debug_credentials.php` untuk cek status
2. Pastikan database connection bekerja
3. Cek apakah update benar-benar tereksekusi di database
