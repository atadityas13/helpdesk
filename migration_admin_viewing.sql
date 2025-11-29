CREATE TABLE IF NOT EXISTS admin_viewing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    ticket_number VARCHAR(20) NOT NULL,
    last_view TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_admin_ticket (admin_id, ticket_number),
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
    INDEX idx_ticket_number (ticket_number),
    INDEX idx_last_view (last_view)
);

-- Cleanup records older than 30 seconds (optional)
-- DELETE FROM admin_viewing WHERE last_view < DATE_SUB(NOW(), INTERVAL 30 SECOND);
