<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Support - Helpdesk</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        .chat-container {
            max-width: 800px;
            margin: 0 auto;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: white;
        }
        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .chat-header h1 {
            margin: 0;
            font-size: 1.5em;
        }
        .chat-header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
            font-size: 0.9em;
        }
        .chat-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f9f9f9;
        }
        .message-group {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .message-group.customer {
            justify-content: flex-end;
        }
        .message-bubble {
            max-width: 70%;
            padding: 12px 15px;
            border-radius: 10px;
            word-wrap: break-word;
        }
        .message-bubble.customer {
            background: #667eea;
            color: white;
            border-bottom-right-radius: 0;
        }
        .message-bubble.admin {
            background: white;
            color: #333;
            border: 1px solid #ddd;
            border-bottom-left-radius: 0;
        }
        .message-time {
            font-size: 0.8em;
            opacity: 0.7;
            margin-top: 5px;
        }
        .chat-input {
            padding: 20px;
            border-top: 1px solid #ddd;
            background: white;
            display: flex;
            gap: 10px;
        }
        .chat-input textarea {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
            font-size: 1em;
            resize: vertical;
            max-height: 120px;
        }
        .chat-input textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }
        .btn-send {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
        }
        .btn-send:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .loading {
            text-align: center;
            color: #999;
            padding: 20px;
        }
        .error {
            padding: 15px;
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            margin: 20px;
        }
        .ticket-info {
            background: #e7f3ff;
            padding: 15px;
            border-left: 4px solid #667eea;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .ticket-info strong {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h1>💬 Chat Support</h1>
            <p id="ticketInfo">Loading...</p>
        </div>

        <div class="chat-body" id="chatBody">
            <div class="loading">Loading messages...</div>
        </div>

        <div class="chat-input">
            <textarea id="messageInput" placeholder="Ketik pesan Anda..." rows="3"></textarea>
            <button class="btn-send" id="sendBtn" onclick="sendMessage()">Kirim</button>
        </div>
    </div>

    <script>
        let ticketNumber = null;
        let ticketId = null;
        let customerId = null;
        let autoRefresh = null;

        function getTicketNumberFromURL() {
            const params = new URLSearchParams(window.location.search);
            return params.get('ticket_number') || params.get('ticket_id');
        }

        function loadMessages() {
            if (!ticketNumber) {
                document.getElementById('chatBody').innerHTML = '<div class="error">❌ Nomor ticket tidak ditemukan</div>';
                return;
            }

            fetch(`src/api/get-messages.php?ticket_number=${encodeURIComponent(ticketNumber)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        ticketId = data.data.ticket_id;
                        displayMessages(data.data.messages);
                        scrollToBottom();
                    } else {
                        document.getElementById('chatBody').innerHTML = '<div class="error">❌ ' + data.message + '</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function displayMessages(messages) {
            const chatBody = document.getElementById('chatBody');
            chatBody.innerHTML = '';

            if (messages.length === 0) {
                chatBody.innerHTML = '<div class="loading">Tidak ada pesan. Mulai percakapan sekarang.</div>';
                return;
            }

            messages.forEach(msg => {
                const msgDiv = document.createElement('div');
                msgDiv.className = `message-group ${msg.sender_type}`;
                
                const bubble = document.createElement('div');
                bubble.className = `message-bubble ${msg.sender_type}`;
                bubble.innerHTML = `
                    <strong>${msg.sender_name}</strong><br>
                    ${msg.message}
                    <div class="message-time">${msg.created_at_formatted}</div>
                `;
                
                msgDiv.appendChild(bubble);
                chatBody.appendChild(msgDiv);
            });
        }

        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();

            if (!message) {
                alert('Pesan tidak boleh kosong');
                return;
            }

            const sendBtn = document.getElementById('sendBtn');
            sendBtn.disabled = true;

            const formData = new FormData();
            formData.append('ticket_number', ticketNumber);
            formData.append('message', message);

            fetch('src/api/send-message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    loadMessages();
                } else {
                    alert('Error: ' + data.message);
                }
                sendBtn.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: ' + error.message);
                sendBtn.disabled = false;
            });
        }

        function scrollToBottom() {
            const chatBody = document.getElementById('chatBody');
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        function initChat() {
            const param = getTicketNumberFromURL();
            
            if (param.startsWith('TK-')) {
                ticketNumber = param;
            } else {
                ticketNumber = 'TK-' + param;
            }

            document.getElementById('ticketInfo').textContent = 'Ticket: ' + ticketNumber;
            
            loadMessages();
            
            // Auto-refresh messages every 3 seconds
            autoRefresh = setInterval(loadMessages, 3000);

            // Allow Enter key to send message
            document.getElementById('messageInput').addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (autoRefresh) clearInterval(autoRefresh);
        });

        // Initialize
        window.addEventListener('load', initChat);
    </script>
</body>
</html>
