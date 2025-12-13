<?php
/**
 * Admin - Manage Tickets (Improved)
 * Helpdesk MTsN 11 Majalengka
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/session.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/ticket.php';

requireAdminLogin();

$ticketId = $_GET['ticket'] ?? null;
$selectedTicket = null;
$messages = [];

// Get ticket with error handling
if ($ticketId) {
    $ticketQuery = "SELECT t.*, c.name, c.email, c.phone 
                    FROM tickets t
                    JOIN customers c ON t.customer_id = c.id
                    WHERE t.id = ?";
    
    $stmt = $conn->prepare($ticketQuery);
    if ($stmt) {
        $stmt->bind_param("i", $ticketId);
        $stmt->execute();
        $selectedTicket = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($selectedTicket) {
            $messages = getTicketMessages($conn, $ticketId) ?? [];
        }
    }
}

// Get unread message count with error handling
$unreadQuery = "SELECT COUNT(*) as unread FROM messages WHERE sender_type = 'customer' AND is_read = 0";
$unreadResult = $conn->query($unreadQuery);
$unreadCount = $unreadResult ? $unreadResult->fetch_assoc()['unread'] : 0;

// Get all tickets with error handling
$allTicketsQuery = "SELECT t.id, t.ticket_number, t.subject, t.status, t.customer_id, t.created_at, t.updated_at, c.name, COUNT(m.id) as message_count
                    FROM tickets t
                    JOIN customers c ON t.customer_id = c.id
                    LEFT JOIN messages m ON t.id = m.ticket_id
                    GROUP BY t.id, t.ticket_number, t.subject, t.status, t.customer_id, t.created_at, t.updated_at, c.name
                    ORDER BY t.updated_at DESC";

$ticketsResult = $conn->query($allTicketsQuery);
if (!$ticketsResult) {
    error_log("Tickets query error: " . $conn->error);
    $allTickets = [];
} else {
    $allTickets = $ticketsResult->fetch_all(MYSQLI_ASSOC) ?? [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tickets - Helpdesk MTsN 11 Majalengka</title>
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Tickets Layout Improvements */
        .tickets-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 0;
            height: calc(100vh - 180px);
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .tickets-list {
            border-right: 1px solid #e0e0e0;
            overflow-y: auto;
            background: #f8f9fa;
        }

        .ticket-item {
            display: block;
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: #333;
        }

        .ticket-item:hover {
            background: #eee;
            border-left: 4px solid #667eea;
            padding-left: 11px;
        }

        .ticket-item.active {
            background: white;
            border-left: 4px solid #667eea;
            padding-left: 11px;
            box-shadow: -2px 0 4px rgba(102, 126, 234, 0.1);
        }

        .ticket-item-number {
            font-size: 12px;
            color: #667eea;
            font-weight: 600;
            text-transform: uppercase;
        }

        .ticket-item-customer {
            font-size: 14px;
            font-weight: 500;
            margin: 4px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ticket-item-subject {
            font-size: 12px;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Chat Panel */
        .chat-panel {
            display: flex;
            flex-direction: column;
            background: white;
        }

        .chat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .chat-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

        .customer-info h3 {
            margin: 0;
            font-size: 16px;
        }

        .customer-info p {
            margin: 2px 0 0;
            font-size: 12px;
            opacity: 0.9;
        }

        .chat-header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .ticket-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ticket-number {
            font-size: 12px;
            opacity: 0.9;
        }

        .ticket-status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            background: rgba(255,255,255,0.2);
            text-transform: uppercase;
        }

        .status-buttons {
            display: flex;
            gap: 6px;
        }

        .status-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s ease;
        }

        .status-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .status-btn.active {
            background: rgba(255,255,255,0.5);
            font-weight: bold;
        }

        /* Chat Messages */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: #f9f9f9;
        }

        .chat-message {
            display: flex;
            flex-direction: column;
            gap: 4px;
            max-width: 70%;
            animation: slideIn 0.2s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .chat-message.customer {
            align-self: flex-start;
            margin-right: auto;
        }

        .chat-message.admin {
            align-self: flex-end;
            margin-left: auto;
        }

        .chat-message-sender {
            font-size: 11px;
            font-weight: 600;
            color: #666;
            padding: 0 8px;
        }

        .chat-message-content {
            background: white;
            padding: 10px 12px;
            border-radius: 8px;
            word-wrap: break-word;
            border: 1px solid #e0e0e0;
        }

        .chat-message.customer .chat-message-content {
            background: #e3f2fd;
            border-color: #bbdefb;
        }

        .chat-message.admin .chat-message-content {
            background: #c8e6c9;
            border-color: #a5d6a7;
        }

        .chat-message-attachment {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 8px;
            transition: transform 0.2s ease;
        }

        .chat-message-attachment:hover {
            transform: scale(1.05);
        }

        .chat-message-time {
            font-size: 10px;
            color: #999;
            padding: 0 8px;
        }

        /* Typing Indicator */
        .typing-indicator {
            display: flex;
            gap: 4px;
            align-items: center;
            padding: 8px 12px;
        }

        .typing-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #999;
            animation: typing 1.4s infinite;
        }

        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typing {
            0%, 60%, 100% { opacity: 0.3; transform: translateY(0); }
            30% { opacity: 1; transform: translateY(-8px); }
        }

        /* Chat Input */
        .chat-input {
            border-top: 1px solid #e0e0e0;
            padding: 15px;
            background: white;
        }

        .preview-area-admin {
            display: none;
            margin-bottom: 10px;
            position: relative;
        }

        .preview-area-admin.show {
            display: block;
        }

        .preview-image-admin {
            max-height: 150px;
            border-radius: 4px;
        }

        .remove-file-admin {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff5252;
            color: white;
            border: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-form {
            display: flex;
            flex-direction: column;
        }

        .input-row {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }

        #adminMessageInput {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
            resize: none;
            max-height: 100px;
        }

        #adminMessageInput:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .icon-btn-admin,
        .file-input-label-admin {
            background: #f0f0f0;
            border: none;
            padding: 10px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.2s ease;
        }

        .icon-btn-admin:hover,
        .file-input-label-admin:hover {
            background: #e0e0e0;
        }

        .file-input-label-admin {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .file-input-label-admin input {
            display: none;
        }

        .btn-send {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-send:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .no-ticket {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #999;
            font-size: 16px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .tickets-layout {
                grid-template-columns: 1fr;
                height: auto;
            }

            .tickets-list {
                border-right: none;
                border-bottom: 1px solid #e0e0e0;
                max-height: 200px;
            }

            .chat-panel {
                height: calc(100vh - 380px);
            }

            .chat-message {
                max-width: 85%;
            }

            .chat-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .chat-header-right {
                align-self: flex-start;
            }

            .status-buttons {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-logo">
                <h2><i class="fas fa-headset"></i> Helpdesk</h2>
                <div class="sidebar-subtitle">MTsN 11 Majalengka</div>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <span><i class="fas fa-tachometer-alt"></i> Dashboard</span>
                </a>
                <a href="manage-tickets.php" class="nav-item active">
                    <span><i class="fas fa-ticket-alt"></i> Kelola Tickets</span>
                    <?php if ($unreadCount > 0): ?>
                        <span class="notification-badge"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </a>
                <a href="faqs.php" class="nav-item">
                    <span><i class="fas fa-question-circle"></i> FAQ</span>
                </a>
                <a href="../../logout.php" class="nav-item logout">
                    <span><i class="fas fa-sign-out-alt"></i> Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <!-- Header -->
            <div class="page-header">
                <h1><i class="fas fa-comments"></i> Kelola Tickets <span class="admin-label"><?php echo $_SESSION['admin_username']; ?></span></h1>
                <div class="header-actions">
                    <button class="btn-refresh" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- Tickets Layout -->
            <div class="tickets-layout">
                <!-- Tickets List -->
                <div class="tickets-list">
                    <?php if (!empty($allTickets)): ?>
                        <?php foreach ($allTickets as $ticket): ?>
                            <a href="?ticket=<?php echo $ticket['id']; ?>" 
                               class="ticket-item <?php echo ($ticketId == $ticket['id']) ? 'active' : ''; ?>">
                                <div class="ticket-item-number"><?php echo htmlspecialchars($ticket['ticket_number']); ?></div>
                                <div class="ticket-item-customer"><?php echo htmlspecialchars($ticket['name']); ?></div>
                                <div class="ticket-item-subject"><?php echo htmlspecialchars(substr($ticket['subject'], 0, 40)); ?></div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding: 20px; text-align: center; color: #999;">
                            Tidak ada ticket
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Chat Panel -->
                <div class="chat-panel">
                    <?php if ($selectedTicket): ?>
                        <!-- Chat Header -->
                        <div class="chat-header">
                            <div class="chat-header-left">
                                <div class="customer-avatar">
                                    <?php echo strtoupper(substr($selectedTicket['name'], 0, 1)); ?>
                                </div>
                                <div class="customer-info">
                                    <h3><?php echo htmlspecialchars($selectedTicket['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($selectedTicket['email']); ?> • <?php echo htmlspecialchars($selectedTicket['phone'] ?? '-'); ?></p>
                                </div>
                            </div>
                            <div class="chat-header-right">
                                <div class="ticket-info">
                                    <span class="ticket-number"><?php echo htmlspecialchars($selectedTicket['ticket_number']); ?></span>
                                    <span class="ticket-status-badge">
                                        <?php echo strtoupper(str_replace('_', ' ', $selectedTicket['status'])); ?>
                                    </span>
                                </div>
                                <div class="status-buttons">
                                    <button class="status-btn <?php echo $selectedTicket['status'] === 'open' ? 'active' : ''; ?>" 
                                            onclick="updateStatus(<?php echo $ticketId; ?>, 'open')" title="Terbuka">
                                        <i class="fas fa-folder-open"></i>
                                    </button>
                                    <button class="status-btn <?php echo $selectedTicket['status'] === 'in_progress' ? 'active' : ''; ?>" 
                                            onclick="updateStatus(<?php echo $ticketId; ?>, 'in_progress')" title="Diproses">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                    <button class="status-btn <?php echo $selectedTicket['status'] === 'resolved' ? 'active' : ''; ?>" 
                                            onclick="updateStatus(<?php echo $ticketId; ?>, 'resolved')" title="Selesai">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    <button class="status-btn <?php echo $selectedTicket['status'] === 'closed' ? 'active' : ''; ?>" 
                                            onclick="updateStatus(<?php echo $ticketId; ?>, 'closed')" title="Ditutup">
                                        <i class="fas fa-lock"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div class="chat-messages" id="chatMessages">
                            <?php if (!empty($messages)): ?>
                                <?php foreach ($messages as $msg): ?>
                                    <div class="chat-message <?php echo htmlspecialchars($msg['sender_type']); ?>">
                                        <div class="chat-message-sender">
                                            <?php echo htmlspecialchars($msg['sender_name']); ?>
                                            <?php if ($msg['sender_type'] === 'customer'): ?>
                                                <span style="color: #4caf50; font-size: 10px; margin-left: 6px;">✓<?php echo $msg['is_read'] ? '✓' : ''; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="chat-message-content"><?php echo htmlspecialchars($msg['message']); ?></div>
                                            <?php if (!empty($msg['attachment_url'])): ?>
                                                <img src="../../<?php echo htmlspecialchars($msg['attachment_url']); ?>" 
                                                     class="chat-message-attachment" 
                                                     onclick="viewImage('../../<?php echo htmlspecialchars($msg['attachment_url']); ?>')">
                                            <?php endif; ?>
                                        </div>
                                        <div class="chat-message-time"><?php echo formatDateTime($msg['created_at']); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="text-align: center; color: #999; padding: 20px;">Belum ada pesan</div>
                            <?php endif; ?>
                        </div>

                        <!-- Input Form -->
                        <div class="chat-input">
                            <form class="chat-form" id="messageForm">
                                <div class="preview-area-admin" id="previewArea">
                                    <img id="previewImage" class="preview-image-admin" alt="Preview">
                                    <button type="button" class="remove-file-admin" onclick="removeFile()">✕</button>
                                </div>
                                <div class="input-row">
                                    <textarea id="messageInput" placeholder="Ketik pesan..." rows="2"></textarea>
                                    <button type="button" class="icon-btn-admin" onclick="document.getElementById('fileInput').click()" title="Gambar">📷</button>
                                    <input type="file" id="fileInput" accept="image/*" style="display: none;" onchange="handleFileSelect(event)">
                                    <button type="submit" class="btn-send">Kirim</button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="no-ticket">
                            <div style="text-align: center;">
                                <i class="fas fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 16px;"></i>
                                <p>Pilih ticket untuk mulai chatting</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        const ticketId = <?php echo $ticketId ?? 'null'; ?>;
        const ticketNumber = '<?php echo htmlspecialchars($selectedTicket['ticket_number'] ?? ''); ?>';
        let selectedFile = null;
        let messageRefreshInterval = null;

        document.addEventListener('DOMContentLoaded', () => {
            if (ticketId) {
                // Auto-refresh messages every 2 seconds
                messageRefreshInterval = setInterval(loadMessages, 2000);
                loadMessages();

                // Auto-scroll to bottom on new messages
                const messagesDiv = document.getElementById('chatMessages');
                if (messagesDiv) {
                    messagesDiv.scrollTop = messagesDiv.scrollHeight;
                }
            }

            // Form submit
            document.getElementById('messageForm')?.addEventListener('submit', sendMessage);

            // Auto-resize textarea
            const textarea = document.getElementById('messageInput');
            if (textarea) {
                textarea.addEventListener('input', () => {
                    textarea.style.height = 'auto';
                    textarea.style.height = Math.min(textarea.scrollHeight, 100) + 'px';
                });
            }
        });

        function loadMessages() {
            if (!ticketNumber) return;

            fetch(`../../src/api/get-messages.php?ticket_number=${encodeURIComponent(ticketNumber)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.data) {
                        displayMessages(data.data.messages);
                    }
                })
                .catch(e => console.error('Error loading messages:', e));
        }

        function displayMessages(messages) {
            const container = document.getElementById('chatMessages');
            if (!container) return;

            const shouldScroll = container.scrollTop + container.clientHeight >= container.scrollHeight - 50;
            const currentCount = container.querySelectorAll('.chat-message').length;

            if (currentCount === messages.length) return; // No new messages

            container.innerHTML = '';

            if (messages.length === 0) {
                container.innerHTML = '<div style="text-align: center; color: #999; padding: 20px;">Belum ada pesan</div>';
                return;
            }

            messages.forEach(msg => {
                const div = document.createElement('div');
                div.className = `chat-message ${msg.sender_type}`;

                let attachmentHtml = '';
                if (msg.attachment_url) {
                    attachmentHtml = `<img src="../../${msg.attachment_url}" class="chat-message-attachment" onclick="viewImage('../../${msg.attachment_url}')">`;
                }

                const time = new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const readIndicator = msg.sender_type === 'customer' ? (msg.is_read ? '✓✓' : '✓') : '';

                div.innerHTML = `
                    <div class="chat-message-sender">
                        ${msg.sender_name}
                        ${readIndicator ? `<span style="color: #4caf50; font-size: 10px; margin-left: 6px;">${readIndicator}</span>` : ''}
                    </div>
                    <div>
                        <div class="chat-message-content">${msg.message}</div>
                        ${attachmentHtml}
                    </div>
                    <div class="chat-message-time">${time}</div>
                `;
                container.appendChild(div);
            });

            if (shouldScroll) {
                container.scrollTop = container.scrollHeight;
            }
        }

        function sendMessage(e) {
            e.preventDefault();

            const message = document.getElementById('messageInput').value.trim();
            if (!message && !selectedFile) {
                Swal.fire({ icon: 'warning', text: 'Ketik pesan atau pilih gambar' });
                return;
            }

            const formData = new FormData();
            formData.append('ticket_number', ticketNumber);
            formData.append('message', message);
            formData.append('sender_type', 'admin');
            if (selectedFile) formData.append('attachment', selectedFile);

            const btn = e.target.querySelector('.btn-send');
            btn.disabled = true;

            fetch('../../src/api/send-message.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('messageInput').value = '';
                        removeFile();
                        loadMessages();
                        document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;
                    } else {
                        Swal.fire({ icon: 'error', text: data.message });
                    }
                    btn.disabled = false;
                })
                .catch(e => {
                    console.error(e);
                    Swal.fire({ icon: 'error', text: 'Gagal mengirim pesan' });
                    btn.disabled = false;
                });
        }

        function handleFileSelect(e) {
            const file = e.target.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                Swal.fire({ icon: 'error', text: 'Hanya file gambar yang diizinkan' });
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({ icon: 'error', text: 'Ukuran file maksimal 5MB' });
                return;
            }

            selectedFile = file;
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('previewImage').src = e.target.result;
                document.getElementById('previewArea').classList.add('show');
            };
            reader.readAsDataURL(file);
        }

        function removeFile() {
            selectedFile = null;
            document.getElementById('fileInput').value = '';
            document.getElementById('previewArea').classList.remove('show');
        }

        function updateStatus(id, status) {
            const statuses = {
                'open': 'Terbuka',
                'in_progress': 'Diproses',
                'resolved': 'Selesai',
                'closed': 'Ditutup'
            };

            Swal.fire({
                title: 'Ubah Status',
                text: `Ubah status menjadi "${statuses[status]}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('../../src/api/update-ticket-status.php', {
                        method: 'POST',
                        body: JSON.stringify({ ticket_id: id, status: status }),
                        headers: { 'Content-Type': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelectorAll('.status-btn').forEach(b => b.classList.remove('active'));
                            event.target.closest('.status-btn').classList.add('active');
                            Swal.fire({ icon: 'success', text: 'Status diubah' });
                        } else {
                            Swal.fire({ icon: 'error', text: data.message });
                        }
                    });
                }
            });
        }

        function viewImage(url) {
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
                cursor: pointer;
            `;
            const img = document.createElement('img');
            img.src = url;
            img.style.cssText = 'max-width: 90%; max-height: 90%; border-radius: 4px;';
            modal.appendChild(img);
            modal.onclick = () => modal.remove();
            document.body.appendChild(modal);
        }

        window.addEventListener('beforeunload', () => {
            if (messageRefreshInterval) clearInterval(messageRefreshInterval);
        });
    </script>
</body>
</html>
