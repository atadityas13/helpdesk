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
        }
        
        .customer .message-time {
            text-align: right;
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
        }
        
        .message-input textarea {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            resize: vertical;
            min-height: 45px;
            max-height: 100px;
        }
        
        .message-input textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
            align-self: flex-end;
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
        <div class="input-area">
            <form id="messageForm" onsubmit="sendMessage(event)">
                <div class="message-input">
                    <textarea id="messageInput" placeholder="Ketik pesan Anda di sini..." required></textarea>
                    <button type="submit" class="send-btn">📤 Kirim</button>
                </div>
            </form>
        </div>
    </div>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <script>
        const TICKET_NUMBER = '<?php echo htmlspecialchars($ticketNumber); ?>';
        let messageRefreshInterval;

        // Load messages on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadMessages();
            // Refresh messages every 3 seconds
            messageRefreshInterval = setInterval(loadMessages, 3000);
        });

        function loadMessages() {
            fetch(`src/api/get-messages.php?ticket_number=${TICKET_NUMBER}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    displayMessages(data.data);
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

            // Clear messages area
            messagesArea.innerHTML = '';

            if (messages.length === 0) {
                messagesArea.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">📝</div>
                        <p>Belum ada pesan. Silakan kirim pesan Anda.</p>
                    </div>
                `;
                return;
            }

            // Display messages
            messages.forEach(msg => {
                const isCustomer = msg.sender_type === 'customer';
                const messageEl = document.createElement('div');
                messageEl.className = `message ${isCustomer ? 'customer' : 'admin'}`;
                
                const time = formatTime(msg.created_at);
                messageEl.innerHTML = `
                    <div>
                        <div class="message-bubble">${escapeHtml(msg.message)}</div>
                        <div class="message-time">${time}</div>
                    </div>
                `;
                
                messagesArea.appendChild(messageEl);
            });

            // Scroll to bottom
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }

        function updateTicketInfo(ticket) {
            const statusBadge = document.getElementById('statusBadge');
            const statusClass = `status-${ticket.status}`;
            const statusLabel = getStatusLabel(ticket.status);
            
            statusBadge.textContent = statusLabel;
            statusBadge.className = `status-badge ${statusClass}`;
        }

        function sendMessage(event) {
            event.preventDefault();
            const input = document.getElementById('messageInput');
            const message = input.value.trim();

            if (!message) return;

            const btn = event.target.querySelector('button');
            btn.disabled = true;
            btn.textContent = '⏳ Mengirim...';

            const messageData = {
                ticket_number: TICKET_NUMBER,
                message: message
            };

            fetch('src/api/send-message.php', {
                method: 'POST',
                body: JSON.stringify(messageData),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    loadMessages(); // Refresh messages immediately
                    
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
                btn.disabled = false;
                btn.innerHTML = '📤 Kirim';
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
            if (messagesArea.innerHTML.includes('Memuat percakapan')) {
                messagesArea.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">❌</div>
                        <p>${message}</p>
                        <p style="font-size: 12px; margin-top: 10px;">Ticket tidak ditemukan atau terjadi kesalahan</p>
                    </div>
                `;
            }
        }

        // Cleanup interval when leaving page
        window.addEventListener('beforeunload', () => {
            if (messageRefreshInterval) {
                clearInterval(messageRefreshInterval);
            }
        });
    </script>
</body>
</html>
