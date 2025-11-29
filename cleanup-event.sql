-- Buat event untuk cleanup otomatis (1x per menit)
CREATE EVENT IF NOT EXISTS cleanup_admin_viewing
ON SCHEDULE EVERY 1 MINUTE
DO
  DELETE FROM admin_viewing 
  WHERE last_view < DATE_SUB(NOW(), INTERVAL 35 SECOND);

-- Cek apakah event sudah ada
SHOW EVENTS LIKE 'cleanup_admin_viewing';

-- Jika ingin disable
-- ALTER EVENT cleanup_admin_viewing DISABLE;

-- Jika ingin enable kembali
-- ALTER EVENT cleanup_admin_viewing ENABLE;
