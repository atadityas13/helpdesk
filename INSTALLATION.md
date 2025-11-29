# 🚀 Installation Guide - Helpdesk MTsN 11 Majalengka

## 📋 Prerequisites

Sebelum memulai, pastikan Anda memiliki:
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi (atau MariaDB equivalent)
- Web Server (Apache, Nginx, atau built-in PHP server)
- Browser modern (Chrome, Firefox, Safari, Edge)

## 📥 Step 1: Download/Clone Project

```bash
# Clone dari git (jika menggunakan git)
git clone <repository-url> helpdesk

# Atau extract file ZIP ke folder helpdesk
cd helpdesk
```

## 🗄️ Step 2: Setup Database

### Option A: Menggunakan Command Line

```bash
# Masuk ke MySQL
mysql -u root -p

# Di prompt MySQL, jalankan:
CREATE DATABASE helpdesk_mtsn11;
USE helpdesk_mtsn11;
source database.sql;
```

### Option B: Menggunakan phpMyAdmin

1. Buka phpMyAdmin (biasanya di `http://localhost/phpmyadmin`)
2. Click "New" untuk create database baru
3. Nama database: `helpdesk_mtsn11`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"
6. Setelah database dibuat, click "Import"
7. Upload file `database.sql`
8. Click "Go"

### Verify Database Import

```bash
# Di MySQL, check tables yang sudah dibuat:
mysql -u root helpdesk_mtsn11 -e "SHOW TABLES;"

# Output yang diharapkan:
# +---------------------------+
# | Tables_in_helpdesk_mtsn11 |
# +---------------------------+
# | admins                    |
# | customers                 |
# | faqs                      |
# | messages                  |
# | tickets                   |
# +---------------------------+
```

## ⚙️ Step 3: Konfigurasi Database

Edit file `src/config/database.php`:

```php
<?php
define('DB_HOST', 'localhost');    // Ubah jika database di server lain
define('DB_USER', 'root');         // Ubah sesuai username MySQL Anda
define('DB_PASS', '');             // Ubah sesuai password MySQL Anda
define('DB_NAME', 'helpdesk_mtsn11');
?>
```

**Contoh jika menggunakan password:**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password_here');
define('DB_NAME', 'helpdesk_mtsn11');
```

## 🔐 Step 4: Ubah Default Admin Password

### Via MySQL

```bash
mysql -u root helpdesk_mtsn11 -e \
"UPDATE admins SET password = PASSWORD('new_password_here') WHERE username = 'admin';"
```

### Via PHP (Manual)

1. Buat file `temp-hash.php` di root project:

```php
<?php
$password = 'your_new_password';
echo password_hash($password, PASSWORD_BCRYPT);
?>
```

2. Jalankan file di browser atau terminal:
```bash
php temp-hash.php
```

3. Copy output hash, lalu update di database:

```bash
mysql -u root helpdesk_mtsn11 -e \
"UPDATE admins SET password = 'PASTE_HASH_HERE' WHERE username = 'admin';"
```

4. Hapus file `temp-hash.php`

## 🌐 Step 5: Setup Web Server

### Option A: Apache dengan XAMPP/WAMP

1. Copy folder `helpdesk` ke `htdocs` (XAMPP) atau `www` (WAMP)
2. Start Apache dari Control Panel
3. Buka: `http://localhost/helpdesk`

### Option B: Built-in PHP Server

```bash
cd helpdesk
php -S localhost:8000
```

Akses: `http://localhost:8000`

### Option C: Nginx

Konfigurasi nginx.conf:

```nginx
server {
    listen 80;
    server_name helpdesk.local;
    root /var/www/helpdesk;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🔑 Step 6: Login Admin

1. Buka: `http://localhost/helpdesk/login.php`
2. Username: `admin`
3. Password: `password123` (atau password baru yang Anda set)
4. Click "Login"

## ✅ Step 7: Verification

Setelah login, verify fitur:

- [ ] Dashboard menampilkan statistik
- [ ] Dapat melihat "Tickets Terbaru" di dashboard
- [ ] Menu "Kelola Tickets" dapat diakses
- [ ] FAQ management dapat diakses
- [ ] Logout berfungsi

## 🔧 Step 8: Widget Integration

### Test di Project Sendiri

1. Edit file `index.php`
2. Uncomment script di bagian bawah:

```html
<!-- Uncomment untuk test widget:
<script src="public/js/widget.js"></script>
-->
```

3. Menjadi:

```html
<!-- Uncomment untuk test widget: -->
<script src="public/js/widget.js"></script>
```

4. Buka `http://localhost/helpdesk` di browser
5. Widget harus muncul di bottom-right corner

### Integrasi ke Website Lain

Di website Anda, tambahkan sebelum closing `</body>`:

```html
<!-- Helpdesk Widget -->
<script src="http://your-helpdesk-server.com/helpdesk/public/js/widget.js"></script>
```

**Contoh lengkap:**

```html
<!DOCTYPE html>
<html>
<head>
    <title>My Website</title>
</head>
<body>
    <h1>Welcome to My Website</h1>
    <p>Your content here...</p>

    <!-- Helpdesk Widget -->
    <script src="http://localhost/helpdesk/public/js/widget.js"></script>
</body>
</html>
```

## 🧪 Step 9: Test Flow

### Test User Chat

1. Klik floating button (icon chat di bottom-right)
2. Pilih "Ticket Baru"
3. Isi form:
   - Nama: John Doe
   - Email: john@example.com
   - No. Telepon: 08123456789
   - Subjek: Test Ticket
   - Pesan: Ini adalah test message
4. Click "Buat Ticket"
5. Widget akan menampilkan chat window
6. Catat nomor ticket yang di-generate

### Test Admin Reply

1. Login ke admin: `http://localhost/helpdesk/login.php`
2. Buka "Kelola Tickets"
3. Pilih ticket yang baru saja dibuat
4. Ketik reply di chat input
5. Click "Kirim"

### Test Resume Chat

1. Di website, klik floating button
2. Pilih "Lanjutkan Chat"
3. Masukkan nomor ticket yang sebelumnya
4. Chat history harus muncul

## 📁 Folder Permissions

Pastikan folder uploads dapat ditulis:

```bash
# Linux/Mac
chmod -R 755 public/uploads/
chmod -R 755 logs/

# Windows (biasanya otomatis)
```

## 🐛 Troubleshooting

### Error: "Connection failed"

**Solusi:**
- Check database credentials di `src/config/database.php`
- Pastikan MySQL running
- Pastikan database `helpdesk_mtsn11` sudah dibuat

### Error: "Table doesn't exist"

**Solusi:**
- Pastikan database.sql sudah di-import dengan benar
- Check tables dengan command: `SHOW TABLES;`

### Widget tidak muncul

**Solusi:**
- Check console browser (F12) untuk error messages
- Pastikan path ke `widget.js` benar
- Clear browser cache (Ctrl+Shift+R)

### Login tidak berfungsi

**Solusi:**
- Check username & password di database:
  ```sql
  SELECT username FROM admins;
  ```
- Reset password dengan command di Step 4
- Clear browser cookies

### CSS tidak loading

**Solusi:**
- Check file `public/css/widget.css` ada
- Check console untuk CSS errors
- Verify URL path ke CSS file

## 🚀 Production Deployment

### Checklist sebelum go live:

- [ ] Update `APP_ENV` menjadi 'production' di config
- [ ] Ganti semua default credentials (username, password)
- [ ] Enable HTTPS
- [ ] Setup proper error logging
- [ ] Backup database regularly
- [ ] Monitor server resources
- [ ] Setup email notifications
- [ ] Test semua fitur di production environment
- [ ] Document any customizations
- [ ] Setup monitoring/alerting

### Security Checklist:

- [ ] Database credentials di file terpisah (tidak di-commit)
- [ ] Disable PHP error display di production
- [ ] Enable SQL strict mode
- [ ] Setup firewall rules
- [ ] Regular security updates
- [ ] Monitor for suspicious activity
- [ ] Implement rate limiting untuk APIs
- [ ] Setup CORS properly

## 📞 Support

Jika mengalami masalah:

1. Check documentation di `README.md`
2. Check SETUP_SUMMARY.md
3. Review error logs di browser console (F12)
4. Check database connection
5. Try clearing cache & cookies

---

**Installation Complete!** 🎉

Selamat menggunakan Helpdesk MTsN 11 Majalengka!
