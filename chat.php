<?php
/**
 * Helpdesk Chat - User Chat Interface (Professional & Complete)
 */

require_once 'src/config/database.php';
require_once 'src/helpers/functions.php';
require_once 'src/helpers/ticket.php';

$ticketNumber = isset($_GET['ticket']) ? trim($_GET['ticket']) : null;

if (!$ticketNumber || !preg_match('/^TK-\d{8}-\d{5}$/', $ticketNumber)) {
    header('Location: index.php');
    exit;
}

// Get ticket and customer info
$ticket = getTicketByNumber($conn, $ticketNumber);
$customerName = '';
$customerEmail = '';
$customerPhone = '';
$ticketSubject = '';
$ticketPriority = '';
$createdAt = '';

if ($ticket) {
    $ticketSubject = $ticket['subject'];
    $ticketPriority = $ticket['priority'];
    $createdAt = $ticket['created_at'];
    
    // Get customer info
    $customerQuery = "SELECT name, email, phone FROM customers WHERE id = ?";
    $stmt = $conn->prepare($customerQuery);
    $stmt->bind_param("i", $ticket['customer_id']);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();
    
    if ($customer) {
        $customerName = $customer['name'];
        $customerEmail = $customer['email'];
        $customerPhone = $customer['phone'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Ticket <?php echo htmlspecialchars($ticketNumber); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/emoji-mart@latest/css/emoji-mart.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary: #25d366;
            --primary-light: #e8f8f5;
            --primary-dark: #1a9d56;
            --secondary: #0084ff;
            --secondary-light: #e8f4fd;
            --secondary-dark: #0066cc;
            --customer-bg: #dcf8c6;
            --admin-bg: #e3f2fd;
            --text-primary: #000;
            --text-secondary: #666;
            --text-muted: #999;
            --border-color: #e0e0e0;
            --bg-light: #f5f7fa;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.12);
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
            background: #fff;
            min-height: 100vh;
            overflow: hidden;
        }
        
        .chat-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        }
        
        /* ===== HEADER ===== */
        .chat-header {
            background: linear-gradient(135deg, #0084ff 0%, #00b4d8 100%);
            padding: 12px 16px;
            border-bottom: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-md);
            color: white;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            min-width: 0;
        }
        
        .header-info {
            min-width: 0;
        }
        
        .header-info .customer-name {
            font-size: 14px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.95);
            margin: 0 0 4px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .header-info h1 {
            font-size: 13px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
            margin: 0 0 2px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .header-info .ticket-subject {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.7);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .header-info p {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.8);
            margin: 4px 0 0 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 10px;
            color: white;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .status-badge.open { background: #ff9800; }
        .status-badge.in_progress { background: #2196f3; }
        .status-badge.resolved { background: #4caf50; }
        .status-badge.closed { background: #9e9e9e; }
        
        .admin-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            padding: 3px 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }
        
        .admin-status.connected::before {
            content: '';
            width: 8px;
            height: 8px;
            background: #4caf50;
            border-radius: 50%;
            display: inline-block;
            animation: pulse 2s infinite;
        }
        
        .admin-status.disconnected::before {
            content: '';
            width: 8px;
            height: 8px;
            background: #999;
            border-radius: 50%;
            display: inline-block;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .header-actions {
            display: flex;
            gap: 4px;
            align-items: center;
        }
        
        .header-btn {
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
            border-radius: 6px;
            backdrop-filter: blur(10px);
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .header-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }
        
        .header-btn.close-btn {
            background: rgba(76, 175, 80, 0.2);
            border-color: rgba(76, 175, 80, 0.3);
        }
        
        .header-btn.close-btn:hover {
            background: rgba(76, 175, 80, 0.3);
        }
        
        /* ===== MESSAGES AREA ===== */
        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: #fff;
        }
        
        .messages-area::-webkit-scrollbar {
            width: 6px;
        }
        
        .messages-area::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .messages-area::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }
        
        .messages-area::-webkit-scrollbar-thumb:hover {
            background: #999;
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
            max-width: 75%;
            padding: 10px 14px;
            border-radius: 18px;
            line-height: 1.4;
            font-size: 13px;
            word-wrap: break-word;
            display: flex;
            flex-direction: column;
            gap: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        
        .customer .message-bubble {
            background: var(--customer-bg);
            color: var(--text-primary);
            border-bottom-right-radius: 4px;
            border: 1px solid rgba(37, 211, 102, 0.1);
        }
        
        .admin .message-bubble {
            background: var(--admin-bg);
            color: var(--text-primary);
            border-bottom-left-radius: 4px;
            border: 1px solid rgba(0, 132, 255, 0.1);
        }
        
        /* ===== EXISTING STYLES ===== */
        .message-content {
            word-break: break-word;
            line-height: 1.5;
        }
        
        .message-time {
            font-size: 11px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 4px;
            justify-content: flex-end;
            margin-top: 2px;
        }
        
        .message-status {
            font-size: 11px;
            line-height: 1;
        }
        
        .status-sent { color: #0084ff; }
        .status-read { color: #0084ff; font-weight: bold; }
        
        .message-attachment {
            max-width: 100%;
            max-height: 280px;
            border-radius: 12px;
            margin-top: 4px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .message-attachment:hover {
            transform: scale(1.02);
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            color: var(--text-muted);
            padding: 40px 20px;
            text-align: center;
            height: 100%;
        }
        
        .empty-state-icon {
            font-size: 64px;
            opacity: 0.2;
        }
        
        .empty-state p {
            font-size: 14px;
        }
        
        .typing-indicator {
            display: flex;
            gap: 4px;
            padding: 10px 14px;
            background: var(--admin-bg);
            border-radius: 18px;
            width: fit-content;
            border-bottom-left-radius: 4px;
            border: 1px solid rgba(0, 132, 255, 0.1);
        }
        
        .typing-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #0084ff;
            animation: typing 1.4s infinite;
        }
        
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }
        
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-8px); }
        }
        
        .closed-notice {
            background: linear-gradient(135deg, #fff3cd 0%, #ffe8a8 100%);
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px 16px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 12px;
            box-shadow: 0 2px 4px rgba(255, 193, 7, 0.1);
        }
        
        .input-area {
            padding: 12px 16px 16px;
            background: #fff;
            border-top: 1px solid var(--border-color);
        }
        
        .input-area.disabled {
            background: var(--bg-light);
            opacity: 0.6;
            pointer-events: none;
        }
        
        .message-input {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }
        
        .input-wrapper {
            flex: 1;
            display: flex;
            gap: 6px;
            align-items: flex-end;
            background: var(--bg-light);
            border-radius: 24px;
            padding: 0 12px;
            border: 2px solid transparent;
            transition: all 0.2s ease;
        }
        
        .input-wrapper:focus-within {
            border-color: var(--secondary);
            background: var(--secondary-light);
        }
        
        .message-input textarea {
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
        
        .message-input textarea::placeholder {
            color: var(--text-muted);
        }
        
        .icon-btn, .send-btn {
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
            color: var(--secondary);
            flex-shrink: 0;
            border-radius: 50%;
        }
        
        .icon-btn:hover, .send-btn:hover {
            transform: scale(1.1);
            background: var(--secondary-light);
        }
        
        .send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .file-input-label {
            position: relative;
            overflow: hidden;
        }
        
        .file-input-label input[type=file] {
            position: absolute;
            left: -9999px;
        }
        
        .preview-area {
            display: none;
            padding: 8px;
            background: var(--bg-light);
            border-radius: 8px;
            position: relative;
            margin-bottom: 8px;
            border: 2px dashed var(--border-color);
        }
        
        .preview-area.show {
            display: block;
            animation: slideIn 0.3s ease-out;
        }
        
        .preview-image {
            max-width: 140px;
            max-height: 140px;
            border-radius: 6px;
        }
        
        .remove-file {
            position: absolute;
            top: 4px;
            right: 4px;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            cursor: pointer;
            font-size: 14px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .remove-file:hover {
            transform: scale(1.1);
        }
        
        .emoji-picker-wrapper {
            position: relative;
            display: inline-block;
        }
        
        .emoji-mart {
            position: absolute !important;
            bottom: 50px !important;
            right: 0 !important;
            z-index: 1000 !important;
            max-height: 320px !important;
            box-shadow: var(--shadow-md) !important;
            border-radius: 12px !important;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .message-bubble {
                max-width: 85%;
                padding: 8px 12px;
            }
            
            .chat-header {
                padding: 10px 12px;
            }
            
            .header-info .customer-name {
                font-size: 12px;
            }
            
            .header-info h1 {
                font-size: 12px;
            }
            
            .header-info .ticket-subject {
                font-size: 10px;
            }
            
            .header-btn {
                padding: 6px 10px;
                font-size: 12px;
            }
            
            .messages-area {
                padding: 12px;
            }
            
            .input-area {
                padding: 10px 12px 12px;
            }
            
            .message-input textarea {
                font-size: 14px;
                min-height: 32px;
            }
            
            .icon-btn, .send-btn {
                width: 32px;
                height: 32px;
                font-size: 16px;
            }
        }
        
        @media (max-width: 480px) {
            .message-bubble {
                max-width: 90%;
                font-size: 12px;
                padding: 7px 10px;
            }
            
            .chat-header {
                padding: 8px 10px;
            }
            
            .header-info .customer-name {
                font-size: 13px;
            }
            
            .header-info h1 {
                font-size: 11px;
            }
            
            .header-info .ticket-subject {
                font-size: 9px;
            }
            
            .header-actions {
                gap: 2px;
            }
            
            .header-btn {
                padding: 5px 8px;
                font-size: 11px;
            }
            
            .messages-area {
                padding: 8px;
                gap: 4px;
            }
            
            .input-area {
                padding: 8px 10px 10px;
            }
            
            .input-wrapper {
                padding: 0 10px;
            }
            
            .icon-btn, .send-btn {
                width: 30px;
                height: 30px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <!-- Header -->
        <div class="chat-header">
            <div class="header-left">
                <div class="header-info">
                    <p class="customer-name">👤 <?php echo htmlspecialchars($customerName); ?></p>
                    <h1><?php echo htmlspecialchars($ticketNumber); ?></h1>
                    <p class="ticket-subject">📋 <?php echo htmlspecialchars($ticketSubject); ?></p>
                    <p>
                        <span class="status-badge" id="statusBadge">Memuat...</span>
                        <span class="admin-status disconnected" id="adminStatus">Mencari Admin...</span>
                    </p>
                </div>
            </div>
            <div class="header-actions">
                <button class="header-btn" title="Informasi" onclick="showTicketInfo()">ℹ️ Info</button>
                <button class="header-btn close-btn" title="Akhiri Bantuan" onclick="closeTicket()">✓ Selesai</button>
            </div>
        </div>

        <!-- Messages Area -->
        <div class="messages-area" id="messagesArea">
            <div class="empty-state">
                <div class="empty-state-icon">💬</div>
                <p>Memuat percakapan...</p>
            </div>
        </div>

        <!-- Input Area -->
        <div class="input-area" id="inputArea">
            <div id="closedNotice"></div>
            
            <div class="preview-area" id="previewArea">
                <img id="previewImage" class="preview-image" alt="Preview">
                <button type="button" class="remove-file" onclick="removeFile()">✕</button>
            </div>
            
            <div class="message-input">
                <div class="input-wrapper">
                    <textarea id="messageInput" placeholder="Ketik pesan..." rows="1"></textarea>
                    <div class="emoji-picker-wrapper">
                        <button type="button" class="icon-btn" id="emojiBtn" title="Emoji">😊</button>
                        <div id="emojiMart"></div>
                    </div>
                </div>
                
                <label class="icon-btn file-input-label" title="Lampir Foto">
                    📷
                    <input type="file" id="fileInput" accept="image/*" onchange="handleFileSelect(event)">
                </label>
                
                <button type="button" onclick="sendMessage(event)" class="send-btn" title="Kirim">➤</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>
    
    <script>
        const TICKET_NUMBER = '<?php echo htmlspecialchars($ticketNumber); ?>';
        const CUSTOMER_EMAIL = '<?php echo htmlspecialchars($customerEmail); ?>';
        const CUSTOMER_PHONE = '<?php echo htmlspecialchars($customerPhone); ?>';
        const TICKET_SUBJECT = '<?php echo htmlspecialchars($ticketSubject); ?>';
        const TICKET_PRIORITY = '<?php echo htmlspecialchars($ticketPriority); ?>';
        const CREATED_AT = '<?php echo htmlspecialchars($createdAt); ?>';
        
        let messageRefreshInterval;
        let adminStatusInterval;
        let selectedFile = null;
        let emojiPickerOpen = false;
        let currentTicketStatus = 'open';
        let currentAdminName = null;

        const textarea = document.getElementById('messageInput');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });

        document.addEventListener('DOMContentLoaded', () => {
            initEmojiPicker();
            loadMessages();
            updateAdminStatus();
            
            textarea.addEventListener('input', () => sendTypingStatus(true));
            textarea.addEventListener('blur', () => sendTypingStatus(false));
            
            // Refresh messages setiap 1.5 detik
            messageRefreshInterval = setInterval(loadMessages, 1500);
            
            // Update admin status setiap 2 detik (lebih sering untuk deteksi real-time)
            adminStatusInterval = setInterval(updateAdminStatus, 2000);
        });

        function trackAdminViewing(isViewing) {
            // Note: Ini untuk admin di manage-tickets.php, bukan untuk customer
            // Hanya untuk reference, tidak akan berfungsi di chat.php customer
        }

        function initEmojiPicker() {
            const emojiBtn = document.getElementById('emojiBtn');
            
            emojiBtn.addEventListener('click', function(e) {
                e.preventDefault();
                emojiPickerOpen = !emojiPickerOpen;
                const emojiMart = document.getElementById('emojiMart');
                
                if (emojiPickerOpen) {
                    const div = document.createElement('div');
                    emojiMart.innerHTML = '';
                    emojiMart.appendChild(div);
                    
                    try {
                        new EmojiMart.Picker({
                            onEmojiSelect: (emoji) => {
                                textarea.value += emoji.native;
                                textarea.focus();
                                textarea.dispatchEvent(new Event('input'));
                                emojiPickerOpen = false;
                                emojiMart.innerHTML = '';
                            },
                            theme: 'light',
                            set: 'native',
                            previewPosition: 'none',
                            perLine: 8
                        }).then(picker => {
                            div.appendChild(picker);
                        }).catch(e => {
                            console.error('Emoji picker error:', e);
                            emojiMart.innerHTML = '<p style="color:#999;">Error loading emoji picker</p>';
                        });
                    } catch (error) {
                        console.error('Emoji error:', error);
                    }
                } else {
                    emojiMart.innerHTML = '';
                }
            });
            
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.emoji-picker-wrapper')) {
                    emojiPickerOpen = false;
                    document.getElementById('emojiMart').innerHTML = '';
                }
            });
        }

        function sendTypingStatus(isTyping) {
            fetch('src/api/typing-status.php', {
                method: 'POST',
                body: JSON.stringify({
                    ticket_number: TICKET_NUMBER,
                    is_typing: isTyping,
                    sender_type: 'customer'
                }),
                headers: { 'Content-Type': 'application/json' }
            }).catch(e => console.error('Error:', e));
        }

        function loadMessages() {
            fetch(`src/api/get-messages.php?ticket_number=${TICKET_NUMBER}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data) {
                    displayMessages(data.data);
                    checkTypingStatus();
                }
            })
            .catch(e => console.error('Error:', e));
        }

        function updateAdminStatus() {
            fetch(`src/api/get-admin-status.php?ticket_number=${TICKET_NUMBER}`)
            .then(r => r.json())  // Fix: hapus space sebelum r
            .then(data => {
                if (data.success && data.data) {
                    const adminStatus = document.getElementById('adminStatus');
                    const { admin_name, is_connected } = data.data;
                    
                    if (admin_name && is_connected) {
                        adminStatus.innerHTML = `<span style="font-weight:500;">${admin_name}</span>`;
                        adminStatus.className = 'admin-status connected';
                        currentAdminName = admin_name;
                    } else if (admin_name && !is_connected) {
                        adminStatus.innerHTML = `<span>${admin_name}</span>`;
                        adminStatus.className = 'admin-status disconnected';
                    } else {
                        adminStatus.innerHTML = 'Belum Ada Admin';
                        adminStatus.className = 'admin-status disconnected';
                    }
                }
            })
            .catch(e => console.error('Error:', e));
        }

        function displayMessages(ticketData) {
            const ticket = ticketData.ticket;
            const messages = ticketData.messages || [];
            const messagesArea = document.getElementById('messagesArea');
            
            currentTicketStatus = ticket.status;
            updateTicketInfo(ticket);
            updateInputAreaStatus(ticket.status);

            const existingMessages = messagesArea.querySelectorAll('.message-group');
            
            if (existingMessages.length !== messages.length || !existingMessages.length) {
                const typingEl = messagesArea.querySelector('.typing-indicator')?.parentElement;
                if (typingEl) typingEl.remove();

                messagesArea.innerHTML = '';

                if (!messages.length) {
                    messagesArea.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-state-icon">💬</div>
                            <p>Belum ada pesan. Silakan mulai percakapan.</p>
                        </div>
                    `;
                    return;
                }

                messages.forEach(msg => {
                    if (!msg.sender_type) return;
                    
                    const senderType = String(msg.sender_type).toLowerCase().trim();
                    const isCustomer = (senderType === 'customer');
                    
                    const group = document.createElement('div');
                    group.className = `message-group ${isCustomer ? 'customer' : 'admin'}`;
                    
                    const bubble = document.createElement('div');
                    bubble.className = 'message-bubble';
                    
                    const time = formatTime(msg.created_at);
                    let status = '';
                    
                    if (isCustomer) {
                        status = msg.is_read 
                            ? '<span class="message-status status-read">✓✓</span>' 
                            : '<span class="message-status status-sent">✓</span>';
                    }
                    
                    let content = `<div class="message-content">${escapeHtml(msg.message)}</div>`;
                    if (msg.attachment_url) {
                        content += `<img src="${escapeHtml(msg.attachment_url)}" class="message-attachment" onclick="viewImage('${escapeHtml(msg.attachment_url)}')">`;
                    }
                    content += `<div class="message-time">${time} ${status}</div>`;
                    
                    bubble.innerHTML = content;
                    group.appendChild(bubble);
                    messagesArea.appendChild(group);
                });

                messagesArea.scrollTop = messagesArea.scrollHeight;
                // Hanya tandai sebagai terbaca jika ada admin yang sedang viewing
                if (currentAdminName) {
                    markMessagesAsRead();
                }
            } else {
                messagesArea.querySelectorAll('.message-group').forEach((el, idx) => {
                    if (messages[idx]) {
                        const msg = messages[idx];
                        const senderType = String(msg.sender_type).toLowerCase().trim();
                        const isCustomer = (senderType === 'customer');
                        
                        if (isCustomer) {
                            const statusEl = el.querySelector('.message-status');
                            if (statusEl) {
                                statusEl.className = msg.is_read 
                                    ? 'message-status status-read' 
                                    : 'message-status status-sent';
                                statusEl.textContent = msg.is_read ? '✓✓' : '✓';
                            }
                        }
                    }
                });
            }
        }
        
        function updateInputAreaStatus(status) {
            const inputArea = document.getElementById('inputArea');
            const closedNotice = document.getElementById('closedNotice');
            const form = inputArea.querySelector('.message-input');
            
            if (status === 'closed') {
                closedNotice.innerHTML = `<div class="closed-notice">✓ Tiket ditutup. Terima kasih telah menghubungi kami!</div>`;
                inputArea.classList.add('disabled');
                form.style.pointerEvents = 'none';
            } else {
                closedNotice.innerHTML = '';
                inputArea.classList.remove('disabled');
                form.style.pointerEvents = 'auto';
            }
        }
        
        function markMessagesAsRead() {
            fetch('src/api/mark-read.php', {
                method: 'POST',
                body: JSON.stringify({ ticket_number: TICKET_NUMBER, viewer_type: 'customer' }),
                headers: { 'Content-Type': 'application/json' }
            }).catch(e => console.error('Error:', e));
        }

        function checkTypingStatus() {
            fetch(`src/api/typing-status.php?ticket_number=${TICKET_NUMBER}`)
            .then(r => r.json())
            .then(data => {
                const messagesArea = document.getElementById('messagesArea');
                if (!data.success || !data.data) {
                    messagesArea.querySelector('.typing-indicator')?.parentElement?.remove();
                    return;
                }
                
                const { sender_type, is_typing } = data.data;
                const typingContainer = messagesArea.querySelector('.typing-indicator')?.parentElement;
                
                if (is_typing && sender_type === 'admin' && !typingContainer) {
                    const el = document.createElement('div');
                    el.className = 'message-group admin';
                    el.innerHTML = `
                        <div class="message-bubble">
                            <div class="typing-indicator">
                                <div class="typing-dot"></div>
                                <div class="typing-dot"></div>
                                <div class="typing-dot"></div>
                            </div>
                        </div>
                    `;
                    messagesArea.appendChild(el);
                    messagesArea.scrollTop = messagesArea.scrollHeight;
                } else if (!is_typing && typingContainer) {
                    typingContainer.remove();
                }
            })
            .catch(e => console.error('Error:', e));
        }

        function updateTicketInfo(ticket) {
            const badge = document.getElementById('statusBadge');
            const labels = { 'open': 'Terbuka', 'in_progress': 'Diproses', 'resolved': 'Selesai', 'closed': 'Ditutup' };
            badge.textContent = labels[ticket.status] || ticket.status;
            badge.className = `status-badge ${ticket.status}`;
        }

        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                Swal.fire({ icon: 'error', title: 'File Tidak Valid', text: 'Hanya gambar yang diizinkan' });
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({ icon: 'error', title: 'File Terlalu Besar', text: 'Maksimal 5MB' });
                return;
            }

            const reader = new FileReader();
            reader.onload = e => {
                selectedFile = file;
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

        function sendMessage(event) {
            event.preventDefault();
            
            if (currentTicketStatus === 'closed') {
                Swal.fire({ icon: 'warning', title: 'Tiket Ditutup', text: 'Tidak bisa mengirim pesan' });
                return;
            }
            
            const message = textarea.value.trim();
            if (!message && !selectedFile) return;

            const btn = event.target;
            btn.disabled = true;

            const form = new FormData();
            form.append('ticket_number', TICKET_NUMBER);
            form.append('message', message);
            form.append('sender_type', 'customer');
            if (selectedFile) form.append('attachment', selectedFile);

            fetch('src/api/send-message.php', { method: 'POST', body: form })
            .then(r => r.json())  // Fix: hapus space sebelum r
            .then(data => {
                if (data.success) {
                    textarea.value = '';
                    textarea.style.height = 'auto';
                    removeFile();
                    loadMessages();
                    sendTypingStatus(false);
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message });
                }
            })
            .catch(e => Swal.fire({ icon: 'error', title: 'Error!', text: 'Jaringan error' }))
            .finally(() => btn.disabled = false);
        }

        function closeTicket() {
            if (currentTicketStatus === 'closed') {
                Swal.fire({ icon: 'info', title: 'Tiket Sudah Ditutup', text: 'Terima kasih telah menghubungi kami' });
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Akhiri Bantuan?',
                text: 'Apakah Anda yakin ingin menutup tiket ini?',
                showCancelButton: true,
                confirmButtonText: '✓ Ya, Tutup',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#4caf50',
                cancelButtonColor: '#999'
            }).then(result => {
                if (result.isConfirmed) updateTicketStatus('closed');
            });
        }

        function updateTicketStatus(status) {
            fetch('src/api/update-ticket-status.php', {
                method: 'POST',
                body: JSON.stringify({ ticket_number: TICKET_NUMBER, status }),
                headers: { 'Content-Type': 'application/json' }
            })
            .then(r => r.json())  // Fix: hapus space sebelum r
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Tiket Ditutup!',
                        text: 'Terima kasih telah menggunakan layanan kami',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => loadMessages());
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message });
                }
            })
            .catch(e => Swal.fire({ icon: 'error', title: 'Error!', text: 'Jaringan error' }));
        }

        function showTicketInfo() {
            const priorityColor = {
                'low': '#4caf50',
                'medium': '#ff9800',
                'high': '#e74c3c'
            };
            
            const priorityLabel = {
                'low': 'Rendah',
                'medium': 'Sedang',
                'high': 'Tinggi'
            };

            Swal.fire({
                icon: 'info',
                title: 'Informasi Tiket Lengkap',
                html: `
                    <div style="text-align:left; font-size:13px; line-height:1.8;">
                        <p><strong>📋 Nomor Tiket:</strong> ${TICKET_NUMBER}</p>
                        <p><strong>📝 Subjek:</strong> ${TICKET_SUBJECT}</p>
                        <p><strong>👤 Email:</strong> ${CUSTOMER_EMAIL}</p>
                        <p><strong>📱 Telepon:</strong> ${CUSTOMER_PHONE || 'Tidak ada'}</p>
                        <p><strong>🎯 Prioritas:</strong> <span style="color:${priorityColor[TICKET_PRIORITY] || '#999'}">● ${priorityLabel[TICKET_PRIORITY] || TICKET_PRIORITY}</span></p>
                        <p><strong>📅 Dibuat:</strong> ${new Date(CREATED_AT).toLocaleString('id-ID')}</p>
                        <p><strong>👨‍💼 Admin:</strong> ${currentAdminName || 'Belum ada'}</p>
                    </div>
                `,
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#0084ff'
            });
        }

        function viewImage(url) {
            Swal.fire({ imageUrl: url, imageAlt: 'Lampiran', confirmButtonText: 'Tutup', showCloseButton: true });
        }

        function formatTime(date) {
            const d = new Date(date);
            return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        window.addEventListener('beforeunload', () => {
            clearInterval(messageRefreshInterval);
            clearInterval(adminStatusInterval);
            sendTypingStatus(false);
        });
    </script>
</body>
</html>
