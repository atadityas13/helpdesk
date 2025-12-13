-- ============================================================================
-- HELPDESK DATABASE SCHEMA
-- Database: mtsnmaja_helpdesk
-- Version: 1.0
-- Created: December 2025
-- ============================================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS mtsnmaja_helpdesk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mtsnmaja_helpdesk;

-- ============================================================================
-- TABLE: customers
-- Menyimpan data pengguna yang membuat ticket
-- ============================================================================
CREATE TABLE IF NOT EXISTS customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: tickets
-- Menyimpan data ticket support
-- Status: open → in_progress → resolved → closed
-- ============================================================================
CREATE TABLE IF NOT EXISTS tickets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ticket_number VARCHAR(50) UNIQUE NOT NULL COMMENT 'Format: TK-YYYYMMDD-XXXXX',
    customer_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_ticket_number (ticket_number),
    INDEX idx_customer_id (customer_id),
    INDEX idx_status (status),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: messages
-- Menyimpan riwayat chat antara customer dan admin
-- ============================================================================
CREATE TABLE IF NOT EXISTS messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ticket_id INT NOT NULL,
    sender_type ENUM('customer', 'admin') NOT NULL,
    sender_id INT NOT NULL,
    message LONGTEXT NOT NULL,
    attachment_url VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    INDEX idx_ticket_id (ticket_id),
    INDEX idx_sender_type (sender_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: admins
-- Menyimpan akun admin/staff support
-- ============================================================================
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL COMMENT 'bcrypt hash',
    email VARCHAR(255),
    role ENUM('admin', 'agent') DEFAULT 'agent',
    is_active BOOLEAN DEFAULT TRUE,
    last_activity TIMESTAMP,
    is_online BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_is_active (is_active),
    INDEX idx_is_online (is_online)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: faqs
-- Knowledge base untuk reduce support tickets
-- ============================================================================
CREATE TABLE IF NOT EXISTS faqs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question VARCHAR(255) NOT NULL,
    answer LONGTEXT NOT NULL,
    category VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: rate_limits
-- Menyimpan rate limit untuk login, ticket creation, dan messaging
-- ============================================================================
CREATE TABLE IF NOT EXISTS rate_limits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    action VARCHAR(50) NOT NULL COMMENT 'login, ticket, message',
    identifier VARCHAR(255) NOT NULL COMMENT 'IP atau customer_id',
    count INT DEFAULT 1,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_action_identifier (action, identifier, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: admin_viewing
-- Menyimpan status admin yang sedang melihat ticket
-- ============================================================================
CREATE TABLE IF NOT EXISTS admin_viewing (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ticket_id INT NOT NULL,
    admin_id INT NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_viewing (ticket_id, admin_id),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
    INDEX idx_ticket_id (ticket_id),
    INDEX idx_admin_id (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- DEFAULT DATA
-- ============================================================================

-- Default Admin Account
-- Username: admin
-- Password: admin123 (bcrypt: $2y$10$slYQmyNdGzin7olVN3ou2OPST9/PgBkqquzi.Ke5KuU/KSHUAXQ3i)
INSERT INTO admins (username, password, email, role, is_active) 
VALUES ('admin', '$2y$10$slYQmyNdGzin7olVN3ou2OPST9/PgBkqquzi.Ke5KuU/KSHUAXQ3i', 'admin@helpdesk.local', 'admin', TRUE)
ON DUPLICATE KEY UPDATE username=VALUES(username);

-- Sample FAQ Data
INSERT INTO faqs (question, answer, category, is_active) VALUES
('Bagaimana cara membuat ticket baru?', 'Anda dapat membuat ticket baru dengan mengklik tombol floating widget di halaman utama, kemudian isi formulir dengan informasi kontak dan deskripsi masalah Anda.', 'Support', TRUE),
('Berapa lama waktu respon support?', 'Tim support kami siap membantu dalam jam kerja. Rata-rata waktu respon adalah 1-2 jam pada jam kerja.', 'Support', TRUE),
('Bagaimana cara melacak status ticket saya?', 'Anda dapat melacak status ticket dengan memasukkan nomor ticket Anda di halaman utama, kemudian klik \"Lanjutkan Chat\".', 'Support', TRUE),
('Aplikasi apa yang bisa saya gunakan untuk akses helpdesk?', 'Helpdesk dapat diakses melalui browser web seperti Chrome, Firefox, Safari, atau Edge. Tidak perlu aplikasi khusus.', 'Teknologi', TRUE),
('Bisakah saya mengirim file melalui chat?', 'Ya, Anda dapat mengirim file dengan ukuran maksimal 5MB melalui chat support.', 'Teknologi', TRUE);

-- ============================================================================
-- INDEXES for Performance
-- ============================================================================
CREATE INDEX idx_tickets_status_created ON tickets(status, created_at DESC);
CREATE INDEX idx_messages_ticket_read ON messages(ticket_id, is_read);
CREATE INDEX idx_admin_viewing_ticket ON admin_viewing(ticket_id, last_seen_at DESC);

-- ============================================================================
-- VIEWS for Reporting
-- ============================================================================

-- View: Active Tickets Summary
CREATE OR REPLACE VIEW v_active_tickets AS
SELECT 
    t.id,
    t.ticket_number,
    t.subject,
    t.status,
    c.name as customer_name,
    c.email as customer_email,
    a.username as assigned_to,
    COUNT(m.id) as message_count,
    MAX(m.created_at) as last_message_at,
    t.created_at
FROM tickets t
LEFT JOIN customers c ON t.customer_id = c.id
LEFT JOIN admins a ON t.assigned_to = a.id
LEFT JOIN messages m ON t.id = m.ticket_id
WHERE t.status IN ('open', 'in_progress')
GROUP BY t.id, t.ticket_number, t.subject, t.status, c.name, c.email, a.username, t.created_at
ORDER BY t.created_at DESC;

-- View: Admin Statistics
CREATE OR REPLACE VIEW v_admin_statistics AS
SELECT 
    COUNT(CASE WHEN t.status = 'open' THEN 1 END) as open_tickets,
    COUNT(CASE WHEN t.status = 'in_progress' THEN 1 END) as in_progress_tickets,
    COUNT(CASE WHEN t.status = 'resolved' THEN 1 END) as resolved_tickets,
    COUNT(CASE WHEN t.status = 'closed' THEN 1 END) as closed_tickets,
    COUNT(DISTINCT c.id) as total_customers,
    COUNT(m.id) as total_messages
FROM tickets t
LEFT JOIN customers c ON t.customer_id = c.id
LEFT JOIN messages m ON t.id = m.ticket_id;

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================
