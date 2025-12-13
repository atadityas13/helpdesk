-- ============================================================================
-- CLEANUP EVENTS - Auto-cleanup untuk rate_limits dan expired sessions
-- ============================================================================

-- Cleanup expired rate limits setiap jam
CREATE EVENT IF NOT EXISTS cleanup_rate_limits
ON SCHEDULE EVERY 1 HOUR
STARTS CURRENT_TIMESTAMP
DO
  DELETE FROM rate_limits WHERE expires_at < NOW();

-- Cleanup old admin viewing records setiap 30 menit
CREATE EVENT IF NOT EXISTS cleanup_admin_viewing
ON SCHEDULE EVERY 30 MINUTE
STARTS CURRENT_TIMESTAMP
DO
  DELETE FROM admin_viewing WHERE last_seen_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE);

-- Update offline status untuk admins yang inactive > 30 menit
CREATE EVENT IF NOT EXISTS update_admin_offline_status
ON SCHEDULE EVERY 5 MINUTE
STARTS CURRENT_TIMESTAMP
DO
  UPDATE admins
  SET is_online = FALSE
  WHERE is_online = TRUE
  AND (last_activity IS NULL OR last_activity < DATE_SUB(NOW(), INTERVAL 30 MINUTE));

-- ============================================================================
-- ENABLE EVENTS (jika diperlukan)
-- ============================================================================

-- Uncomment untuk enable events di server
-- SET GLOBAL event_scheduler = ON;

-- Verify events
-- SHOW EVENTS;
