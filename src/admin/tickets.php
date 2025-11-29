<?php
/**
 * Admin - Modern Tickets Management
 * Helpdesk MTsN 11 Majalengka
 * 
 * Modern WhatsApp-like interface with full integration
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/ticket.php';
require_once __DIR__ . '/../helpers/admin-status.php';

requireAdminLogin();

$ticketId = $_GET['ticket'] ?? null;
$selectedTicket = null;
$messages = [];

if ($ticketId) {
    $ticketQuery = "SELECT t.*, c.name, c.email, c.phone 
                    FROM tickets t
                    JOIN customers c ON t.customer_id = c.id
                    WHERE t.id = ?";
    
    $stmt = $conn->prepare($ticketQuery);
    $stmt->bind_param("i", $ticketId);
    $stmt->execute();
    $selectedTicket = $stmt->get_result()->fetch_assoc();
    
    if ($selectedTicket) {
        $messages = getTicketMessages($conn, $ticketId);
    }
}

// Get all tickets with unread count
$allTicketsQuery = "SELECT t.*, c.name, c.email,
                    COUNT(m.id) as message_count,
                    SUM(CASE WHEN m.sender_type = 'customer' AND m.is_read = 0 THEN 1 ELSE 0 END) as unread_count,
                    MAX(m.created_at) as last_message_time
                    FROM tickets t
                    JOIN customers c ON t.customer_id = c.id
                    LEFT JOIN messages m ON t.id = m.ticket_id
                    GROUP BY t.id
                    ORDER BY t.updated_at DESC";

$allTickets = $conn->query($allTicketsQuery)->fetch_all(MYSQLI_ASSOC);

// Get statistics
$statsQuery = "SELECT 
                COUNT(CASE WHEN status = 'open' THEN 1 END) as open_count,
                COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as progress_count,
                COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved_count,
                COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed_count
                FROM tickets";
$stats = $conn->query($statsQuery)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets - Helpdesk Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/emoji-mart@latest/css/emoji-mart.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #0084ff;
            --primary-dark: #0066cc;
            --primary-light: #e8f4fd;
            --secondary: #25d366;
            --danger: #e74c3c;
            --warning: #ff9800;
            --success: #4caf50;
            --text-primary: #111;
            --text-secondary: #65676b;
            --text-muted: #999;
            --bg-light: #f0f2f5;
            --bg-lighter: #fff;
            --border-color: #e5e5e5;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 2px 8px rgba(0, 0, 0, 0.12);
            --shadow-lg: 0 4px 16px rgba(0, 0, 0, 0.15);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
            background: var(--bg-light);
            color: var(--text-primary);
            overflow: hidden;
        }

        .container {
            display: flex;
            height: 100vh;
            background: var(--bg-light);
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 360px;
            background: var(--bg-lighter);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow-sm);
        }

        .sidebar-header {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(135deg, var(--primary) 0%, #00b4d8 100%);
            color: white;
        }

        .sidebar-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .sidebar-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-top: 12px;
        }

        .stat-item {
            text-align: center;
            padding: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }

        .stat-item .count {
            font-size: 18px;
            font-weight: 700;
        }

        .stat-item .label {
            font-size: 10px;
            opacity: 0.9;
            margin-top: 4px;
        }

        .search-box {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
        }

        .search-box input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            font-size: 13px;
            background: var(--bg-light);
            transition: all 0.2s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .tickets-container {
            flex: 1;
            overflow-y: auto;
            padding: 8px 0;
        }

        .ticket-item {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            gap: 12px;
            align-items: center;
            text-decoration: none;
            color: inherit;
        }

        .ticket-item:hover {
            background: var(--bg-light);
        }

        .ticket-item.active {
            background: var(--primary-light);
            border-left: 3px solid var(--primary);
        }

        .ticket-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, #00b4d8 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            flex-shrink: 0;
        }

        .ticket-info {
            flex: 1;
            min-width: 0;
        }

        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }

        .ticket-name {
            font-weight: 600;
            font-size: 13px;
            color: var(--text-primary);
        }

        .ticket-time {
            font-size: 12px;
            color: var(--text-muted);
        }

        .ticket-preview {
            font-size: 12px;
            color: var(--text-secondary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ticket-badge {
            display: inline-block;
            background: var(--primary);
            color: white;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }

        /* ===== MAIN CHAT ===== */
        .main-chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--bg-lighter);
        }

        .chat-header {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(135deg, var(--primary) 0%, #00b4d8 100%);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
        }

        .chat-header-info h2 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .chat-header-info p {
            font-size: 12px;
            opacity: 0.9;
        }

        .chat-header-actions {
            display: flex;
            gap: 8px;
        }

        .header-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.2s ease;
        }

        .header-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: #fff;
        }

        .message-group {
            display: flex;
            margin-bottom: 4px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message-group.customer {
            justify-content: flex-end;
        }

        .message-bubble {
            max-width: 60%;
            padding: 10px 14px;
            border-radius: 18px;
            font-size: 13px;
            word-wrap: break-word;
            display: flex;
            flex-direction: column;
            gap: 4px;
            box-shadow: var(--shadow-sm);
            animation: messageIn 0.3s ease-out;
        }

        @keyframes messageIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .message-group.customer .message-bubble {
            background: var(--secondary);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message-group.admin .message-bubble {
            background: var(--bg-light);
            color: var(--text-primary);
            border-bottom-left-radius: 4px;
            border: 1px solid var(--border-color);
        }

        .message-time {
            font-size: 11px;
            opacity: 0.7;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .message-status {
            font-size: 11px;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-muted);
            text-align: center;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.3;
        }

        /* ===== INPUT AREA ===== */
        .input-area {
            padding: 16px;
            border-top: 1px solid var(--border-color);
            background: var(--bg-lighter);
        }

        .input-wrapper {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }

        .input-field {
            flex: 1;
            display: flex;
            gap: 8px;
            align-items: flex-end;
            background: var(--bg-light);
            border-radius: 24px;
            padding: 0 12px;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .input-field:focus-within {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .input-field textarea {
            flex: 1;
            padding: 10px 0;
            border: none;
            background: transparent;
            font-family: inherit;
            font-size: 13px;
            resize: none;
            min-height: 36px;
            max-height: 100px;
            outline: none;
            color: var(--text-primary);
        }

        .input-field textarea::placeholder {
            color: var(--text-muted);
        }

        .icon-btn {
            width: 36px;
            height: 36px;
            padding: 0;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            color: var(--primary);
            flex-shrink: 0;
            border-radius: 50%;
        }

        .icon-btn:hover {
            transform: scale(1.1);
            background: var(--primary-light);
        }

        .send-btn {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, #00b4d8 100%);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-md);
        }

        .send-btn:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-lg);
        }

        .send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .sidebar {
                width: 300px;
            }

            .message-bubble {
                max-width: 70%;
            }
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: 50%;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
            }

            .main-chat {
                height: 50%;
            }

            .message-bubble {
                max-width: 80%;
            }

            .sidebar-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .sidebar {
                display: none;
            }

            .main-chat {
                width: 100%;
                height: 100%;
            }

            .message-bubble {
                max-width: 90%;
            }

            .chat-header-actions {
                gap: 4px;
            }

            .header-btn {
                width: 32px;
                height: 32px;
                font-size: 14px;
            }
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h1>💬 Tickets</h1>
                <div class="sidebar-stats">
                    <div class="stat-item">
                        <div class="count"><?php echo $stats['open_count']; ?></div>
                        <div class="label">Terbuka</div>
                    </div>
                    <div class="stat-item">
                        <div class="count"><?php echo $stats['progress_count']; ?></div>
                        <div class="label">Diproses</div>
                    </div>
                    <div class="stat-item">
                        <div class="count"><?php echo $stats['resolved_count']; ?></div>
                        <div class="label">Selesai</div>
                    </div>
                    <div class="stat-item">
                        <div class="count"><?php echo $stats['closed_count']; ?></div>
                        <div class="label">Ditutup</div>
                    </div>
                </div>
            </div>

            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Cari ticket atau customer...">
            </div>

            <div class="tickets-container" id="ticketsContainer">
                <?php foreach ($allTickets as $ticket): ?>
                    <a href="?ticket=<?php echo $ticket['id']; ?>" 
                       class="ticket-item <?php echo ($ticketId == $ticket['id']) ? 'active' : ''; ?>"
                       data-ticket-id="<?php echo $ticket['id']; ?>">
                        <div class="ticket-avatar">
                            <?php echo strtoupper(substr($ticket['name'], 0, 1)); ?>
                        </div>
                        <div class="ticket-info">
                            <div class="ticket-header">
                                <span class="ticket-name"><?php echo htmlspecialchars($ticket['name']); ?></span>
                                <span class="ticket-time">
                                    <?php 
                                    if ($ticket['last_message_time']) {
                                        $time = strtotime($ticket['last_message_time']);
                                        $now = time();
                                        $diff = $now - $time;
                                        
                                        if ($diff < 60) echo 'Baru saja';
                                        elseif ($diff < 3600) echo floor($diff / 60) . 'm';
                                        elseif ($diff < 86400) echo floor($diff / 3600) . 'h';
                                        else echo date('d/m', $time);
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="ticket-preview">
                                <?php echo htmlspecialchars($ticket['ticket_number']); ?> - <?php echo htmlspecialchars(substr($ticket['email'], 0, 30)); ?>
                                <?php if ($ticket['unread_count'] > 0): ?>
                                    <span class="ticket-badge"><?php echo $ticket['unread_count']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Main Chat -->
        <div class="main-chat">
            <?php if ($selectedTicket): ?>
                <!-- Chat Header -->
                <div class="chat-header">
                    <div class="chat-header-info">
                        <h2><?php echo htmlspecialchars($selectedTicket['name']); ?></h2>
                        <p><?php echo htmlspecialchars($selectedTicket['email']); ?> • <?php echo htmlspecialchars($selectedTicket['ticket_number']); ?></p>
                    </div>
                    <div class="chat-header-actions">
                        <button class="header-btn" title="Info" onclick="showTicketInfo()">ℹ️</button>
                        <button class="header-btn" title="Telepon" onclick="callCustomer()">📞</button>
                        <button class="header-btn" title="Menu">⋮</button>
                    </div>
                </div>

                <!-- Messages -->
                <div class="messages-area" id="messagesArea">
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-group <?php echo $msg['sender_type']; ?>">
                            <div class="message-bubble">
                                <div><?php echo htmlspecialchars($msg['message']); ?></div>
                                <?php if ($msg['attachment_url']): ?>
                                    <img src="../../<?php echo htmlspecialchars($msg['attachment_url']); ?>" 
                                         style="max-width: 100%; border-radius: 8px; cursor: pointer;"
                                         onclick="viewImage('../../<?php echo htmlspecialchars($msg['attachment_url']); ?>')">
                                <?php endif; ?>
                                <div class="message-time">
                                    <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                                    <?php if ($msg['sender_type'] === 'customer'): ?>
                                        <?php echo $msg['is_read'] ? '✓✓' : '✓'; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div id="typingIndicator"></div>
                </div>

                <!-- Input -->
                <div class="input-area">
                    <div class="input-wrapper">
                        <div class="input-field">
                            <textarea id="messageInput" placeholder="Ketik pesan..."></textarea>
                            <button class="icon-btn" title="Emoji" id="emojiBtn">😊</button>
                            <button class="icon-btn" title="Lampir" id="attachBtn">📎</button>
                            <input type="file" id="fileInput" accept="image/*" style="display: none;">
                        </div>
                        <button class="send-btn" id="sendBtn" onclick="sendMessage()">➤</button>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">💬</div>
                    <h2>Pilih Ticket untuk Mulai</h2>
                    <p>Pilih ticket dari daftar di sebelah kiri untuk memulai percakapan</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>
    
    <script>
        const TICKET_ID = <?php echo $ticketId ?? 'null'; ?>;
        const TICKET_NUMBER = '<?php echo htmlspecialchars($selectedTicket['ticket_number'] ?? ''); ?>';
        
        let messageRefreshInterval;
        let adminViewingInterval;
        let selectedFile = null;

        document.addEventListener('DOMContentLoaded', () => {
            if (TICKET_ID) {
                setupMessageInput();
                loadMessages();
                trackViewing(true);
                
                adminViewingInterval = setInterval(() => trackViewing(true), 10000);
                messageRefreshInterval = setInterval(loadMessages, 1500);
            }
            
            setupSearch();
        });

        function setupMessageInput() {
            const textarea = document.getElementById('messageInput');
            if (!textarea) return;
            
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 100) + 'px';
            });
            
            textarea.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
        }

        function setupSearch() {
            const searchInput = document.getElementById('searchInput');
            if (!searchInput) return;
            
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                document.querySelectorAll('.ticket-item').forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(query) ? '' : 'none';
                });
            });
        }

        function trackViewing(isViewing) {
            if (!TICKET_NUMBER) return;
            
            fetch('../../src/api/admin-viewing.php', {
                method: 'POST',
                body: JSON.stringify({
                    ticket_number: TICKET_NUMBER,
                    is_viewing: isViewing
                }),
                headers: { 'Content-Type': 'application/json' }
            }).catch(e => console.error('Error:', e));
        }

        function loadMessages() {
            if (!TICKET_NUMBER) return;
            
            fetch(`../../src/api/get-messages.php?ticket_number=${TICKET_NUMBER}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data) {
                    displayMessages(data.data.messages || []);
                }
            })
            .catch(e => console.error('Error:', e));
        }

        function displayMessages(messages) {
            const area = document.getElementById('messagesArea');
            if (!area) return;
            
            const existing = area.querySelectorAll('.message-group').length;
            if (existing === messages.length) return;
            
            area.innerHTML = '';
            
            messages.forEach(msg => {
                const group = document.createElement('div');
                group.className = `message-group ${msg.sender_type}`;
                
                const bubble = document.createElement('div');
                bubble.className = 'message-bubble';
                
                let content = `<div>${msg.message}</div>`;
                if (msg.attachment_url) {
                    content += `<img src="../../${msg.attachment_url}" style="max-width: 100%; border-radius: 8px; cursor: pointer;" onclick="viewImage('../../${msg.attachment_url}')">`;
                }
                content += `<div class="message-time">${new Date(msg.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'})} ${msg.sender_type === 'customer' ? (msg.is_read ? '✓✓' : '✓') : ''}</div>`;
                
                bubble.innerHTML = content;
                group.appendChild(bubble);
                area.appendChild(group);
            });
            
            area.scrollTop = area.scrollHeight;
        }

        function sendMessage() {
            const textarea = document.getElementById('messageInput');
            const message = textarea.value.trim();
            
            if (!message && !selectedFile) return;
            
            const btn = document.getElementById('sendBtn');
            btn.disabled = true;
            
            const form = new FormData();
            form.append('ticket_number', TICKET_NUMBER);
            form.append('message', message);
            form.append('sender_type', 'admin');
            if (selectedFile) form.append('attachment', selectedFile);
            
            fetch('../../src/api/send-message.php', { method: 'POST', body: form })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    textarea.value = '';
                    textarea.style.height = 'auto';
                    selectedFile = null;
                    loadMessages();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(e => Swal.fire('Error', 'Network error', 'error'))
            .finally(() => btn.disabled = false);
        }

        function showTicketInfo() {
            Swal.fire({
                title: 'Informasi Ticket',
                html: `<div style="text-align: left; font-size: 13px;">
                    <p><strong>Nomor:</strong> ${TICKET_NUMBER}</p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($selectedTicket['email']); ?></p>
                    <p><strong>Telepon:</strong> <?php echo htmlspecialchars($selectedTicket['phone'] ?? '-'); ?></p>
                </div>`,
                confirmButtonText: 'Tutup'
            });
        }

        function callCustomer() {
            const phone = '<?php echo htmlspecialchars($selectedTicket['phone'] ?? ''); ?>';
            if (phone) window.location.href = `tel:${phone}`;
            else Swal.fire('Info', 'Nomor telepon tidak tersedia', 'info');
        }

        function viewImage(url) {
            Swal.fire({
                imageUrl: url,
                imageAlt: 'Attachment',
                confirmButtonText: 'Tutup'
            });
        }

        window.addEventListener('beforeunload', () => {
            if (TICKET_NUMBER) {
                navigator.sendBeacon('../../src/api/admin-viewing.php', JSON.stringify({
                    ticket_number: TICKET_NUMBER,
                    is_viewing: false
                }));
            }
            clearInterval(messageRefreshInterval);
            clearInterval(adminViewingInterval);
        });
    </script>
</body>
</html>
