<?php
/**
 * Helpdesk Chat - User Chat Interface
 */

// Get ticket number from URL parameter
$ticketNumber = isset($_GET['ticket']) ? trim($_GET['ticket']) : null;

// Validate ticket number format
if (!$ticketNumber || !preg_match('/^TK-\d{8}-\d{5}$/', $ticketNumber)) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Ticket <?php echo htmlspecialchars($ticketNumber); ?></title>
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- EmojiMart CSS -->
    <link href="https://cdn.jsdelivr.net/npm/emoji-mart@latest/css/emoji-mart.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }
        
        .chat-container {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: white;
        }
        
        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .chat-header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .chat-header p {
            font-size: 13px;
            opacity: 0.9;
        }
        
        .chat-info {
            background: #f9f9f9;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .ticket-info {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .info-item {
            font-size: 13px;
        }
        
        .info-item strong {
            color: #333;
        }
        
        .info-item span {
            color: #666;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            color: white;
        }
        
        .status-open { background: #FFA500; }
        .status-in_progress { background: #2196F3; }
        .status-resolved { background: #28a745; }
        .status-closed { background: #6c757d; }
        
        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .message {
            display: flex;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message.customer {
            justify-content: flex-end;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 12px;
            line-height: 1.5;
            font-size: 14px;
            word-break: break-word;
        }
        
        .customer .message-bubble {
            background: #667eea;
            color: white;
            border-bottom-right-radius: 2px;
        }
        
        .admin .message-bubble {
            background: #f0f0f0;
            color: #333;
            border-bottom-left-radius: 2px;
        }
        
        .message-time {
            font-size: 11px;
            color: #999;
            margin-top: 4px;
            padding: 0 16px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .customer .message-time {
            text-align: right;
            justify-content: flex-end;
        }
        
        .message-status {
            display: inline-flex;
            font-size: 12px;
        }
        
        .status-sending { color: #999; }
        .status-sent { color: #667eea; font-weight: bold; }
        .status-read { color: #28a745; font-weight: bold; }
        
        .closed-notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: 600;
        }
        
        .input-area-disabled {
            background: #f5f5f5;
        }
        
        .input-area-disabled textarea {
            background: #f9f9f9;
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        .input-area-disabled button,
        .input-area-disabled label {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }
        
        .typing-indicator {
            display: flex;
            gap: 4px;
            padding: 12px 16px;
            background: #f0f0f0;
            border-radius: 12px;
            width: fit-content;
        }
        
        .typing-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #999;
            animation: typing 1.4s infinite;
        }
        
        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 15px;
            color: #999;
            padding: 40px;
            text-align: center;
        }
        
        .empty-state-icon {
            font-size: 48px;
        }
        
        .input-area {
            padding: 20px;
            border-top: 1px solid #eee;
            background: white;
        }
        
        .message-input {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        
        .input-wrapper {
            flex: 1;
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }
        
        .message-input textarea {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            resize: none;
            min-height: 45px;
            max-height: 100px;
        }
        
        .message-input textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .icon-btn {
            width: 45px;
            height: 45px;
            padding: 0;
            background: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .icon-btn:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .send-btn {
            padding: 12px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            height: 45px;
            display: flex;
            align-items: center;
        }
        
        .send-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .back-btn {
            color: white;
            text-decoration: none;
            font-size: 13px;
            margin-top: 5px;
            opacity: 0.9;
            transition: opacity 0.3s;
        }
        
        .back-btn:hover {
            opacity: 1;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .emoji-picker-wrapper {
            position: relative;
            display: inline-block;
        }
        
        .emoji-mart {
            position: absolute;
            bottom: 60px !important;
            right: 0 !important;
            z-index: 1000;
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
            margin-top: 10px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 6px;
            position: relative;
        }
        
        .preview-area.show {
            display: block;
        }
        
        .preview-image {
            max-width: 150px;
            max-height: 150px;
            border-radius: 6px;
        }
        
        .remove-file {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 16px;
            line-height: 1;
        }
        
        @media (max-width: 768px) {
            .chat-container {
                height: auto;
                min-height: 100vh;
            }
            
            .message-bubble {
                max-width: 90%;
            }
            
            .chat-info {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .ticket-info {
                width: 100%;
            }
            
            .emoji-mart {
                max-height: 300px !important;
            }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <!-- Header -->
        <div class="chat-header">
            <div class="header-top">
                <div>
                    <h1>💬 Chat Bantuan</h1>
                    <p id="ticketDisplay">Ticket: <?php echo htmlspecialchars($ticketNumber); ?></p>
                </div>
                <a href="index.php" class="back-btn">← Kembali</a>
            </div>
        </div>

        <!-- Ticket Info -->
        <div class="chat-info" id="ticketInfo">
            <div class="ticket-info" id="ticketDetails">
                <div class="info-item">
                    <strong>Status:</strong> <span class="status-badge" id="statusBadge">Memuat...</span>
                </div>
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
            
            <form id="messageForm">
                <div class="preview-area" id="previewArea">
                    <img id="previewImage" class="preview-image" alt="Preview">
                    <button type="button" class="remove-file" onclick="removeFile()">✕</button>
                </div>
                
                <div class="message-input">
                    <div class="input-wrapper">
                        <textarea id="messageInput" placeholder="Ketik pesan Anda di sini..." rows="1"></textarea>
                        
                        <div class="emoji-picker-wrapper">
                            <button type="button" class="icon-btn" id="emojiBtn" title="Tambah emoji">😊</button>
                            <div id="emojiMart"></div>
                        </div>
                    </div>
                    
                    <label class="icon-btn file-input-label" title="Lampirkan gambar">
                        📎
                        <input type="file" id="fileInput" accept="image/*" onchange="handleFileSelect(event)">
                    </label>
                    
                    <button type="button" onclick="sendMessage(event)" class="send-btn">📤 Kirim</button>
                </div>
            </form>
        </div>
    </div>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <!-- EmojiMart JS -->
    <script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>
    
    <script>
        const TICKET_NUMBER = '<?php echo htmlspecialchars($ticketNumber); ?>';
        let messageRefreshInterval;
        let lastMessageId = 0;
        let selectedFile = null;
        let emojiPickerOpen = false;

        // Auto resize textarea
        const textarea = document.getElementById('messageInput');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });

        // Load messages on page load
        document.addEventListener('DOMContentLoaded', () => {
            initEmojiPicker();
            loadMessages();
            
            // Send typing indicator when user starts typing
            textarea.addEventListener('input', () => {
                sendTypingStatus(true);
            });
            
            textarea.addEventListener('blur', () => {
                sendTypingStatus(false);
            });
            
            // Auto-refresh messages every 2 seconds to update status and typing indicator
            messageRefreshInterval = setInterval(() => {
                loadMessages();
            }, 2000);
        });

        function initEmojiPicker() {
            const emojiBtn = document.getElementById('emojiBtn');
            
            // Prevent default form submission when clicking emoji button
            emojiBtn.addEventListener('click', function(e) {
                e.preventDefault();
                emojiPickerOpen = !emojiPickerOpen;
                const emojiMart = document.getElementById('emojiMart');
                
                if (emojiPickerOpen) {
                    // Initialize EmojiMart
                    const div = document.createElement('div');
                    emojiMart.innerHTML = '';
                    emojiMart.appendChild(div);
                    
                    new EmojiMart.Picker({
                        onEmojiSelect: (emoji) => {
                            const currentText = textarea.value;
                            textarea.value = currentText + emoji.native;
                            textarea.focus();
                            textarea.dispatchEvent(new Event('input'));
                            emojiPickerOpen = false;
                            emojiMart.innerHTML = '';
                        },
                        theme: 'light',
                        set: 'native'
                    }).then(picker => {
                        div.appendChild(picker);
                    });
                } else {
                    emojiMart.innerHTML = '';
                }
            });
            
            // Close emoji picker when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.emoji-picker-wrapper')) {
                    emojiPickerOpen = false;
                    document.getElementById('emojiMart').innerHTML = '';
                }
            });
        }

        function sendTypingStatus(isTyping) {
            // Send typing status to server
            fetch('src/api/typing-status.php', {
                method: 'POST',
                body: JSON.stringify({
                    ticket_number: TICKET_NUMBER,
                    is_typing: isTyping,
                    sender_type: 'customer'
                }),
                headers: {
                    'Content-Type': 'application/json'
                }
            }).catch(error => console.error('Error:', error));
        }

        function loadMessages() {
            fetch(`src/api/get-messages.php?ticket_number=${TICKET_NUMBER}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    displayMessages(data.data);
                    checkTypingStatus();
                } else {
                    showMessagesError('Gagal memuat pesan');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessagesError('Terjadi kesalahan jaringan');
            });
        }

        function displayMessages(ticketData) {
            const ticket = ticketData.ticket;
            const messages = ticketData.messages || [];
            const messagesArea = document.getElementById('messagesArea');

            // Update ticket info
            updateTicketInfo(ticket);
            
            // Update input area based on ticket status
            updateInputAreaStatus(ticket.status);

            // Check if there are existing messages to avoid flickering
            const existingMessages = messagesArea.querySelectorAll('.message');
            const hasTypingIndicator = messagesArea.querySelector('.typing-indicator');
            
            // Only clear and rebuild if message count changed or first load
            if (existingMessages.length !== messages.length || existingMessages.length === 0) {
                // Remove typing indicator temporarily
                if (hasTypingIndicator) {
                    hasTypingIndicator.remove();
                }

                messagesArea.innerHTML = '';

                if (messages.length === 0) {
                    messagesArea.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-state-icon">📝</div>
                            <p>Belum ada pesan. Silakan kirim pesan Anda.</p>
                        </div>
                    `;
                    markMessagesAsRead();
                    return;
                }

                // Display messages
                messages.forEach((msg, idx) => {
                    // Validate sender_type - STRICT CHECK
                    if (!msg.sender_type) {
                        console.error('Message missing sender_type:', msg);
                        return;
                    }
                    
                    const senderType = String(msg.sender_type).toLowerCase().trim();
                    const isCustomer = (senderType === 'customer');
                    const messageEl = document.createElement('div');
                    messageEl.className = `message ${isCustomer ? 'customer' : 'admin'}`;
                    
                    const time = formatTime(msg.created_at);
                    let statusIcon = '';
                    
                    // Show status only for customer messages
                    if (isCustomer) {
                        if (msg.is_read) {
                            statusIcon = '<span class="message-status status-read">✓✓</span>';
                        } else {
                            statusIcon = '<span class="message-status status-sent">✓</span>';
                        }
                    }
                    
                    let bubbleContent = `<div class="message-bubble">${escapeHtml(msg.message)}`;
                    
                    if (msg.attachment_url) {
                        bubbleContent += `<br><img src="${escapeHtml(msg.attachment_url)}" class="message-attachment" onclick="viewImage('${escapeHtml(msg.attachment_url)}')">`;
                    }
                    
                    bubbleContent += '</div>';
                    
                    messageEl.innerHTML = `
                        <div>
                            ${bubbleContent}
                            <div class="message-time">${time}${statusIcon}</div>
                        </div>
                    `;
                    
                    messagesArea.appendChild(messageEl);
                    lastMessageId = Math.max(lastMessageId, msg.id || 0);
                });

                // Scroll to bottom
                messagesArea.scrollTop = messagesArea.scrollHeight;
                
                // Mark messages as read
                markMessagesAsRead();
            } else {
                // Just update status icons
                const messageElements = messagesArea.querySelectorAll('.message');
                messageElements.forEach((el, idx) => {
                    if (messages[idx]) {
                        const msg = messages[idx];
                        const senderType = String(msg.sender_type).toLowerCase().trim();
                        const isCustomer = (senderType === 'customer');
                        
                        const expectedClass = isCustomer ? 'customer' : 'admin';
                        if (!el.classList.contains(expectedClass)) {
                            el.className = `message ${expectedClass}`;
                        }
                        
                        if (isCustomer) {
                            const statusEl = el.querySelector('.message-status');
                            if (statusEl) {
                                if (msg.is_read) {
                                    statusEl.className = 'message-status status-read';
                                    statusEl.textContent = '✓✓';
                                } else {
                                    statusEl.className = 'message-status status-sent';
                                    statusEl.textContent = '✓';
                                }
                            }
                        }
                    }
                });
            }
        }
        
        function updateInputAreaStatus(status) {
            const inputArea = document.getElementById('inputArea');
            const closedNotice = document.getElementById('closedNotice');
            const messageForm = document.getElementById('messageForm');
            
            if (status === 'closed') {
                closedNotice.innerHTML = `
                    <div class="closed-notice">
                        🔒 Ticket ini sudah ditutup. Anda tidak bisa mengirim pesan lagi.
                    </div>
                `;
                inputArea.classList.add('input-area-disabled');
                messageForm.style.pointerEvents = 'none';
                messageForm.style.opacity = '0.6';
            } else {
                closedNotice.innerHTML = '';
                inputArea.classList.remove('input-area-disabled');
                messageForm.style.pointerEvents = 'auto';
                messageForm.style.opacity = '1';
            }
        }
        
        function markMessagesAsRead() {
            fetch('src/api/mark-read.php', {
                method: 'POST',
                body: JSON.stringify({
                    ticket_number: TICKET_NUMBER
                }),
                headers: {
                    'Content-Type': 'application/json'
                }
            }).catch(error => console.error('Error:', error));
        }

        function checkTypingStatus() {
            // Check if someone is typing
            fetch(`src/api/typing-status.php?ticket_number=${TICKET_NUMBER}`)
            .then(response => response.json())
            .then(data => {
                const messagesArea = document.getElementById('messagesArea');
                
                // Jangan lakukan apapun jika tidak ada data atau empty state ditampilkan
                if (!data.success || !data.data) {
                    // Remove typing indicator jika ada
                    const existingTyping = messagesArea.querySelector('.typing-indicator');
                    if (existingTyping) {
                        existingTyping.parentElement.remove();
                    }
                    return;
                }
                
                const senderType = data.data.sender_type;
                const isTyping = data.data.is_typing;
                
                // Cari container typing indicator (parent dari typing-indicator div)
                const typingContainer = messagesArea.querySelector('.typing-indicator')?.parentElement;
                
                if (isTyping && senderType === 'admin') {
                    // Hanya tampilkan jika admin yang mengetik
                    if (!typingContainer) {
                        const typingEl = document.createElement('div');
                        typingEl.className = 'message admin';
                        typingEl.innerHTML = `
                            <div>
                                <div class="typing-indicator">
                                    <div class="typing-dot"></div>
                                    <div class="typing-dot"></div>
                                    <div class="typing-dot"></div>
                                </div>
                                <div class="message-time">Admin Support sedang mengetik...</div>
                            </div>
                        `;
                        messagesArea.appendChild(typingEl);
                        messagesArea.scrollTop = messagesArea.scrollHeight;
                    }
                } else {
                    // Remove typing indicator jika admin tidak mengetik
                    if (typingContainer) {
                        typingContainer.remove();
                    }
                }
            })
            .catch(error => console.error('Error checking typing:', error));
        }

        function updateTicketInfo(ticket) {
            const statusBadge = document.getElementById('statusBadge');
            const statusClass = `status-${ticket.status}`;
            const statusLabel = getStatusLabel(ticket.status);
            
            statusBadge.textContent = statusLabel;
            statusBadge.className = `status-badge ${statusClass}`;
        }

        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file type
            if (!file.type.startsWith('image/')) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Tidak Valid',
                    text: 'Hanya file gambar yang diizinkan (JPG, PNG, GIF, WebP)'
                });
                return;
            }

            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Terlalu Besar',
                    text: 'Ukuran file maksimal 5MB'
                });
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                selectedFile = file;
                const preview = document.getElementById('previewImage');
                preview.src = e.target.result;
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
            
            // Check if ticket is closed
            const statusBadge = document.getElementById('statusBadge');
            if (statusBadge && statusBadge.textContent.includes('Ditutup')) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Ticket Ditutup',
                    text: 'Anda tidak bisa mengirim pesan untuk ticket yang sudah ditutup'
                });
                return;
            }
            
            const input = document.getElementById('messageInput');
            const message = input.value.trim();

            if (!message && !selectedFile) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pesan Kosong',
                    text: 'Silakan ketik pesan atau pilih gambar'
                });
                return;
            }

            const btn = event.target.closest('button') || document.querySelector('.send-btn');
            if (btn) {
                btn.disabled = true;
                btn.textContent = '⏳ Mengirim...';
            }

            // Use FormData for file upload
            const formData = new FormData();
            formData.append('ticket_number', TICKET_NUMBER);
            formData.append('message', message);
            formData.append('sender_type', 'customer');
            
            if (selectedFile) {
                formData.append('attachment', selectedFile);
            }

            fetch('src/api/send-message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    input.style.height = 'auto';
                    removeFile();
                    loadMessages(); // Refresh messages immediately
                    sendTypingStatus(false);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Pesan Terkirim!',
                        text: 'Pesan Anda telah dikirim',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message || 'Gagal mengirim pesan'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan!',
                    text: 'Terjadi kesalahan jaringan'
                });
            })
            .finally(() => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '📤 Kirim';
                }
            });
        }

        function viewImage(url) {
            Swal.fire({
                imageUrl: url,
                imageAlt: 'Lampiran',
                confirmButtonText: 'Tutup',
                showCloseButton: true
            });
        }

        function getStatusLabel(status) {
            const labels = {
                'open': 'Terbuka',
                'in_progress': 'Sedang Diproses',
                'resolved': 'Terselesaikan',
                'closed': 'Ditutup'
            };
            return labels[status] || status;
        }

        function formatTime(dateString) {
            const date = new Date(dateString);
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${hours}:${minutes}`;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function showMessagesError(message) {
            const messagesArea = document.getElementById('messagesArea');
            messagesArea.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">❌</div>
                    <p>${message}</p>
                    <p style="font-size: 12px; margin-top: 10px;">Ticket tidak ditemukan atau terjadi kesalahan</p>
                </div>
            `;
        }

        // Cleanup interval when leaving page
        window.addEventListener('beforeunload', () => {
            if (messageRefreshInterval) {
                clearInterval(messageRefreshInterval);
            }
            sendTypingStatus(false);
        });
    </script>
</body>
</html>
