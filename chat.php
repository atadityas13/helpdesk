<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Support - Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #5568d3;
            --secondary: #764ba2;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .chat-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 16px 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-title h1 {
            font-size: 1.5em;
            font-weight: 800;
            margin: 0;
        }

        .header-title p {
            font-size: 0.9em;
            opacity: 0.9;
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .btn-small {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
            font-size: 0.9em;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Chat Container */
        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
            background: white;
        }

        /* Chat Messages */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            background: linear-gradient(135deg, rgba(248, 249, 250, 1) 0%, rgba(243, 244, 246, 1) 100%);
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .message {
            display: flex;
            margin-bottom: 8px;
        }

        .message.customer {
            justify-content: flex-start;
        }

        .message.admin {
            justify-content: flex-end;
        }

        .message-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 12px;
            line-height: 1.5;
            word-wrap: break-word;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .message.customer .message-bubble {
            background: white;
            color: #1f2937;
            border-left: 3px solid var(--info);
        }

        .message.admin .message-bubble {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .message-sender {
            font-size: 0.85em;
            font-weight: 700;
            margin-bottom: 6px;
            opacity: 0.8;
        }

        .message-time {
            font-size: 0.8em;
            opacity: 0.6;
            margin-top: 6px;
            font-style: italic;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #9ca3af;
            text-align: center;
        }

        .empty-icon {
            font-size: 3.5em;
            margin-bottom: 16px;
        }

        /* Chat Input */
        .chat-input-area {
            padding: 20px 24px;
            border-top: 1px solid #e5e7eb;
            background: white;
        }

        .input-form {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }

        .form-group {
            flex: 1;
        }

        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.95em;
            resize: none;
            min-height: 44px;
            max-height: 100px;
            transition: all 0.3s ease;
        }

        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-send {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 100px;
            height: 44px;
        }

        .btn-send:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
        }

        .btn-send:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Ticket Info */
        .ticket-info {
            padding: 16px 24px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-bottom: 1px solid #e5e7eb;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .info-label {
            font-size: 0.9em;
            color: #6b7280;
            font-weight: 600;
        }

        .info-value {
            font-size: 0.95em;
            color: #1f2937;
            font-weight: 700;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85em;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-open {
            background: rgba(245, 158, 11, 0.15);
            color: #92400e;
        }

        .status-in-progress {
            background: rgba(59, 130, 246, 0.15);
            color: #1e40af;
        }

        .status-resolved {
            background: rgba(16, 185, 129, 0.15);
            color: #065f46;
        }

        .status-closed {
            background: rgba(107, 114, 128, 0.15);
            color: #374151;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            border-left: 3px solid #ef4444;
        }

        @media (max-width: 768px) {
            .chat-messages {
                padding: 16px;
            }

            .message-bubble {
                max-width: 90%;
                padding: 10px 14px;
            }

            .header-title h1 {
                font-size: 1.2em;
            }

            .input-form {
                flex-direction: column;
            }

            .btn-send {
                width: 100%;
            }

            .ticket-info {
                grid-template-columns: 1fr;
                gap: 12px;
            }
        }

        /* Scrollbar */
        .chat-messages::-webkit-scrollbar {
            width: 8px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }

        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="chat-header">
        <div class="header-content">
            <div class="header-title">
                <i class="fas fa-comments"></i>
                <div>
                    <h1>Chat Support</h1>
                    <p id="headerSubtitle">Menghubungkan ke support...</p>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn-small btn-back" onclick="window.location.href='index.php'">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </button>
            </div>
        </div>
    </div>

    <!-- Ticket Info -->
    <div class="ticket-info" id="ticketInfo" style="display: none;">
        <div class="info-item">
            <span class="info-label">Nomor Ticket:</span>
            <span class="info-value" id="ticketNumber">-</span>
        </div>
        <div class="info-item">
            <span class="info-label">Status:</span>
            <span class="status-badge" id="ticketStatus">-</span>
        </div>
        <div class="info-item">
            <span class="info-label">Dibuat:</span>
            <span class="info-value" id="ticketCreated">-</span>
        </div>
    </div>

    <!-- Chat Container -->
    <div class="chat-container">
        <!-- Messages -->
        <div class="chat-messages" id="chatMessages">
            <div class="empty-state">
                <div class="empty-icon">🔄</div>
                <p>Memuat percakapan...</p>
            </div>
        </div>

        <!-- Input Area -->
        <div class="chat-input-area">
            <div id="errorMessage"></div>
            <div class="input-form">
                <textarea 
                    id="messageInput" 
                    placeholder="Ketik pesan Anda di sini... (Enter + Shift untuk baris baru)"
                    maxlength="5000"
                ></textarea>
                <button class="btn-send" id="sendBtn" onclick="sendMessage()">
                    <i class="fas fa-paper-plane me-2"></i>Kirim
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        let currentTicketId = null;
        let messageRefreshInterval = null;
        const urlParams = new URLSearchParams(window.location.search);
        const ticketNumber = urlParams.get('ticket');

        if (!ticketNumber) {
            showError('Nomor ticket tidak ditemukan');
        } else {
            loadTicket();
        }

        function loadTicket() {
            fetch(`src/api/get-ticket-by-number.php?ticket_number=${encodeURIComponent(ticketNumber)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentTicketId = data.data.ticket.id;
                        updateTicketInfo(data.data.ticket);
                        loadMessages();
                        
                        // Auto-refresh messages every 2 seconds
                        if (messageRefreshInterval) clearInterval(messageRefreshInterval);
                        messageRefreshInterval = setInterval(loadMessages, 2000);
                    } else {
                        showError(data.message || 'Ticket tidak ditemukan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Gagal memuat ticket');
                });
        }

        function updateTicketInfo(ticket) {
            document.getElementById('ticketNumber').textContent = ticket.ticket_number;
            document.getElementById('ticketCreated').textContent = new Date(ticket.created_at).toLocaleDateString('id-ID');
            
            const statusBadge = document.getElementById('ticketStatus');
            statusBadge.textContent = ticket.status.toUpperCase().replace('_', ' ');
            statusBadge.className = 'status-badge status-' + ticket.status;
            
            document.getElementById('headerSubtitle').textContent = ticket.subject;
            document.getElementById('ticketInfo').style.display = 'grid';
        }

        function loadMessages() {
            if (!currentTicketId) return;

            fetch(`src/api/get-customer-messages.php?ticket_id=${currentTicketId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayMessages(data.data.messages);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function displayMessages(messages) {
            const container = document.getElementById('chatMessages');
            
            if (messages.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">💬</div>
                        <p>Belum ada pesan. Mulai percakapan Anda!</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = messages.map(msg => {
                const date = new Date(msg.created_at);
                const timeStr = date.toLocaleTimeString('id-ID', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
                const messageClass = msg.sender_type === 'admin' ? 'admin' : 'customer';

                return `
                    <div class="message ${messageClass}">
                        <div class="message-bubble">
                            <div class="message-sender">${msg.sender_name || 'Unknown'}</div>
                            <div>${msg.message}</div>
                            <div class="message-time">${timeStr}</div>
                        </div>
                    </div>
                `;
            }).join('');

            container.scrollTop = container.scrollHeight;
        }

        function sendMessage() {
            const message = document.getElementById('messageInput').value.trim();
            
            if (!message || !currentTicketId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pesan Kosong',
                    text: 'Silakan ketik pesan sebelum mengirim',
                    confirmButtonColor: '#667eea'
                });
                return;
            }

            const sendBtn = document.getElementById('sendBtn');
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';

            const formData = new FormData();
            formData.append('ticket_id', currentTicketId);
            formData.append('message', message);

            fetch('src/api/send-customer-message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('messageInput').value = '';
                    document.getElementById('errorMessage').innerHTML = '';
                    loadMessages();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Pesan Terkirim',
                        text: 'Pesan Anda sudah dikirim ke tim support',
                        timer: 2000,
                        showConfirmButton: false,
                        position: 'top-end',
                        toast: true
                    });
                } else {
                    showError(data.message || 'Gagal mengirim pesan');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Terjadi kesalahan saat mengirim pesan');
            })
            .finally(() => {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Kirim';
            });
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan',
                text: message,
                confirmButtonColor: '#667eea'
            });
        }

        // Allow sending with Ctrl+Enter
        document.getElementById('messageInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (messageRefreshInterval) clearInterval(messageRefreshInterval);
        });
    </script>
</body>
</html>
