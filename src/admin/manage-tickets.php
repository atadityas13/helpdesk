<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tickets - Helpdesk Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        .dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            width: 250px;
            overflow-y: auto;
        }
        .sidebar h2 {
            margin-bottom: 30px;
            text-align: center;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            padding-bottom: 15px;
        }
        .sidebar-menu {
            list-style: none;
        }
        .sidebar-menu li {
            margin-bottom: 10px;
        }
        .sidebar-menu a {
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            display: block;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.3);
            font-weight: bold;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
        }
        .ticket-list {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            height: fit-content;
        }
        .ticket-list-header {
            background: #667eea;
            color: white;
            padding: 15px;
            font-weight: bold;
        }
        .ticket-item {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .ticket-item:hover {
            background: #f5f5f5;
        }
        .ticket-item.active {
            background: #e8ecf1;
            border-left: 4px solid #667eea;
        }
        .ticket-item-number {
            font-weight: bold;
            color: #667eea;
            font-size: 0.9em;
        }
        .ticket-item-subject {
            margin: 3px 0;
            color: #333;
            font-size: 0.95em;
        }
        .ticket-item-status {
            display: inline-block;
            font-size: 0.75em;
            padding: 3px 8px;
            border-radius: 3px;
            margin-top: 5px;
        }
        .chat-window {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: grid;
            grid-template-rows: auto 1fr auto;
            height: 80vh;
        }
        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 10px 10px 0 0;
        }
        .chat-header h2 {
            margin: 0 0 5px 0;
            font-size: 1.2em;
        }
        .chat-header p {
            margin: 0;
            font-size: 0.9em;
            opacity: 0.9;
        }
        .chat-messages {
            padding: 15px;
            overflow-y: auto;
            background: #f9f9f9;
        }
        .message {
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
        }
        .message.customer {
            justify-content: flex-end;
        }
        .message-bubble {
            max-width: 70%;
            padding: 10px 12px;
            border-radius: 8px;
            word-wrap: break-word;
        }
        .message.customer .message-bubble {
            background: #667eea;
            color: white;
        }
        .message.admin .message-bubble {
            background: white;
            border: 1px solid #ddd;
            color: #333;
        }
        .chat-input-area {
            padding: 15px;
            border-top: 1px solid #ddd;
            background: white;
            border-radius: 0 0 10px 10px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
        }
        .chat-input-area textarea {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
            resize: vertical;
            max-height: 100px;
        }
        .btn-send {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.2s ease;
        }
        .btn-send:hover {
            background: #5568d3;
        }
        .status-select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
            margin-bottom: 10px;
        }
        .no-ticket {
            padding: 30px;
            text-align: center;
            color: #999;
        }
        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            .ticket-list {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php
    require_once __DIR__ . '/../middleware/session.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../helpers/functions.php';
    require_once __DIR__ . '/../helpers/ticket.php';

    // Require admin login
    requireAdminLogin();

    $adminUsername = getAdminUsername();
    $adminRole = getAdminRole();

    // Get tickets
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $tickets = [];
    if ($result = $conn->query("
        SELECT t.*, c.name, c.email,
               COUNT(m.id) as message_count
        FROM tickets t
        JOIN customers c ON t.customer_id = c.id
        LEFT JOIN messages m ON t.id = m.ticket_id
        WHERE t.status != 'closed'
        GROUP BY t.id
        ORDER BY t.updated_at DESC
    ")) {
        while ($row = $result->fetch_assoc()) {
            $tickets[] = $row;
        }
    }
    ?>

    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>📊 Admin</h2>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php">🏠 Dashboard</a></li>
                <li><a href="manage-tickets.php" class="active">🎫 Kelola Tickets</a></li>
                <li><a href="faqs.php">❓ FAQ Management</a></li>
                <li><hr style="border: none; border-top: 1px solid rgba(255, 255, 255, 0.2); margin: 15px 0;"></li>
                <li><a href="../../logout.php">🚪 Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Ticket List -->
            <div class="ticket-list">
                <div class="ticket-list-header">
                    🎫 Tickets Aktif (<?php echo count($tickets); ?>)
                </div>
                <div id="ticketListContainer">
                    <?php if (empty($tickets)): ?>
                        <div class="no-ticket">Tidak ada ticket aktif</div>
                    <?php else: ?>
                        <?php foreach ($tickets as $ticket): ?>
                            <div class="ticket-item" onclick="selectTicket(<?php echo $ticket['id']; ?>)">
                                <div class="ticket-item-number"><?php echo $ticket['ticket_number']; ?></div>
                                <div class="ticket-item-subject"><?php echo htmlspecialchars(truncateText($ticket['subject'], 25)); ?></div>
                                <span class="ticket-item-status badge-<?php echo str_replace('_', '-', $ticket['status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chat Window -->
            <div class="chat-window">
                <div class="chat-header">
                    <h2 id="ticketTitle">Pilih Ticket</h2>
                    <p id="ticketSubtitle">Klik ticket di sidebar untuk mulai chat</p>
                </div>
                <div class="chat-messages" id="chatMessages">
                    <div class="no-ticket">Pilih ticket untuk melihat pesan</div>
                </div>
                <div class="chat-input-area" id="chatInputArea" style="display: none;">
                    <div style="display: grid; gap: 10px;">
                        <select class="status-select" id="statusSelect" onchange="updateTicketStatus()">
                            <option value="">Ubah Status...</option>
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                        </select>
                        <textarea id="messageInput" placeholder="Ketik respons..." rows="3"></textarea>
                    </div>
                    <button class="btn-send" onclick="sendAdminMessage()">Kirim</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentTicketId = null;
        let messageRefreshInterval = null;

        function selectTicket(ticketId) {
            currentTicketId = ticketId;
            loadTicketMessages(ticketId);
            loadTicketDetails(ticketId);

            // Update active state
            document.querySelectorAll('.ticket-item').forEach(el => {
                el.classList.remove('active');
            });
            event.target.closest('.ticket-item').classList.add('active');

            // Show chat input
            document.getElementById('chatInputArea').style.display = 'grid';

            // Start auto-refresh
            if (messageRefreshInterval) clearInterval(messageRefreshInterval);
            messageRefreshInterval = setInterval(() => loadTicketMessages(ticketId), 2000);
        }

        function loadTicketDetails(ticketId) {
            fetch(`../api/get-ticket.php?id=${ticketId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const ticket = data.data;
                        document.getElementById('ticketTitle').textContent = ticket.ticket_number;
                        document.getElementById('ticketSubtitle').textContent = `${ticket.name} | ${ticket.subject}`;
                        document.getElementById('statusSelect').value = ticket.status;
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function loadTicketMessages(ticketId) {
            fetch(`../api/get-ticket-messages.php?ticket_id=${ticketId}`)
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
            container.innerHTML = messages.map(msg => `
                <div class="message ${msg.sender_type}">
                    <div class="message-bubble">
                        <strong>${msg.sender_name}</strong><br>
                        ${msg.message}
                        <div style="font-size: 0.8em; margin-top: 5px; opacity: 0.7;">
                            ${msg.created_at_formatted}
                        </div>
                    </div>
                </div>
            `).join('');
            container.scrollTop = container.scrollHeight;
        }

        function sendAdminMessage() {
            const message = document.getElementById('messageInput').value.trim();
            if (!message || !currentTicketId) {
                alert('Pesan tidak boleh kosong');
                return;
            }

            const formData = new FormData();
            formData.append('ticket_id', currentTicketId);
            formData.append('message', message);
            formData.append('csrf_token', '<?php echo getCsrfToken(); ?>');

            fetch('../api/send-admin-message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('messageInput').value = '';
                    loadTicketMessages(currentTicketId);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function updateTicketStatus() {
            const status = document.getElementById('statusSelect').value;
            if (!status || !currentTicketId) return;

            const formData = new FormData();
            formData.append('ticket_id', currentTicketId);
            formData.append('status', status);
            formData.append('csrf_token', '<?php echo getCsrfToken(); ?>');

            fetch('../api/update-ticket-status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Status updated');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Cleanup on unload
        window.addEventListener('beforeunload', () => {
            if (messageRefreshInterval) clearInterval(messageRefreshInterval);
        });
    </script>
</body>
</html>
