<?php
/**
 * Admin Manage Tickets
 * Handle ticket management and chat
 */

// Load configuration FIRST (before any output)
require_once dirname(__DIR__) . '/config/.env.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/middleware/session.php';
require_once dirname(__DIR__) . '/middleware/auth.php';
require_once dirname(__DIR__) . '/helpers/api-response.php';
require_once dirname(__DIR__) . '/helpers/functions.php';

// Initialize session and check authentication
initSession();
requireAdminLogin();

// Get database connection
$db = Database::getInstance();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tickets - Helpdesk Admin</title>
    <style>
        /* ===== CSS Custom Properties / Variables ===== */
        :root {
            --primary: #667eea;
            --primary-light: #7c8ef0;
            --primary-dark: #5568d3;
            --secondary: #764ba2;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --light: #f9fafb;
            --lighter: #f3f4f6;
            --border: #e5e7eb;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-light: #9ca3af;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
                'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
            background: var(--lighter);
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* ===== Dashboard Layout ===== */
        .dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            height: 100vh;
            overflow: hidden;
            background: var(--lighter);
        }

        /* ===== Sidebar ===== */
        .sidebar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 25px 20px;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .sidebar h2 {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .sidebar-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            display: block;
            padding: 12px 15px;
            border-radius: var(--radius);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(4px);
        }

        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: 600;
        }

        /* ===== Main Content ===== */
        .main-content {
            display: grid;
            grid-template-columns: 320px 1fr;
            overflow: hidden;
            background: white;
            gap: 0;
        }

        /* ===== Ticket List ===== */
        .ticket-list {
            border-right: 1px solid var(--border);
            overflow-y: auto;
            overflow-x: hidden;
            background: var(--light);
            max-height: 100%;
            display: flex;
            flex-direction: column;
        }

        .ticket-list-header {
            padding: 18px 16px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-bottom: 1px solid var(--border);
            font-weight: 700;
            color: var(--text-primary);
            font-size: 15px;
            letter-spacing: -0.3px;
            flex-shrink: 0;
        }

        #ticketListContainer {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .ticket-item {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            transition: all 0.2s ease;
            background: white;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .ticket-item:hover {
            background: var(--lighter);
            transform: translateX(3px);
        }

        .ticket-item.active {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-left: 3px solid var(--primary);
            padding-left: 13px;
            box-shadow: inset -2px 0 8px rgba(102, 126, 234, 0.1);
        }

        .ticket-item-number {
            font-weight: 700;
            color: var(--primary);
            font-size: 14px;
            letter-spacing: -0.3px;
        }

        .ticket-item-subject {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        .ticket-item-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: fit-content;
        }

        .badge-open {
            background: rgba(59, 130, 246, 0.1);
            color: #1d4ed8;
        }

        .badge-in-progress {
            background: rgba(245, 158, 11, 0.1);
            color: #b45309;
        }

        .badge-resolved {
            background: rgba(16, 185, 129, 0.1);
            color: #065f46;
        }

        .badge-closed {
            background: rgba(239, 68, 68, 0.1);
            color: #7f1d1d;
        }

        /* ===== Chat Window ===== */
        .chat-window {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: white;
        }

        .chat-header {
            padding: 24px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .chat-header h2 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--text-primary);
            letter-spacing: -0.3px;
        }

        .chat-header p {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            background: var(--light);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* ===== Messages ===== */
        .message {
            display: flex;
            margin-bottom: 0;
        }

        .message.admin {
            justify-content: flex-end;
        }

        .message-bubble {
            max-width: 65%;
            padding: 14px 16px;
            border-radius: 10px;
            font-size: 14px;
            line-height: 1.5;
            box-shadow: var(--shadow-sm);
            animation: slideIn 0.3s ease;
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

        .message.customer .message-bubble {
            background: white;
            color: var(--text-primary);
            border-left: 3px solid var(--info);
        }

        .message.admin .message-bubble {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .message-bubble strong {
            display: block;
            margin-bottom: 4px;
            font-weight: 700;
            font-size: 13px;
            opacity: 0.95;
        }

        .message-bubble > div:last-child {
            font-size: 12px;
            margin-top: 8px;
            opacity: 0.75;
            font-style: italic;
        }

        /* ===== Chat Input Area ===== */
        .chat-input-area {
            padding: 20px 24px;
            background: white;
            border-top: 1px solid var(--border);
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .chat-input-area > div {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: inherit;
            resize: none;
            font-size: 14px;
            color: var(--text-primary);
            transition: all 0.3s ease;
            background: var(--light);
        }

        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .btn-send {
            padding: 11px 20px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 700;
            transition: all 0.3s ease;
            font-size: 14px;
            letter-spacing: -0.3px;
            box-shadow: var(--shadow-md);
        }

        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-send:active {
            transform: translateY(0);
        }

        .status-select {
            padding: 11px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            width: 100%;
            font-size: 14px;
            background: var(--light);
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .status-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .no-ticket {
            padding: 40px 24px;
            text-align: center;
            color: var(--text-secondary);
            font-size: 14px;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ===== Scrollbar Styling ===== */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-light);
        }

        /* ===== Responsive Design ===== */
        @media (max-width: 1024px) {
            .dashboard {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                z-index: 1000;
                width: 250px;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                grid-template-columns: 1fr;
            }

            .ticket-list {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .dashboard {
                height: auto;
                min-height: 100vh;
            }

            .chat-window {
                min-height: 100vh;
            }

            .chat-messages {
                padding: 16px;
            }

            .message-bubble {
                max-width: 85%;
            }

            .chat-header {
                padding: 16px;
            }

            .chat-input-area {
                padding: 12px 16px;
            }

            textarea {
                min-height: 80px;
            }

            .btn-send {
                padding: 10px 16px;
            }
        }

        @media (max-width: 480px) {
            .sidebar {
                width: 100%;
            }

            .chat-header h2 {
                font-size: 16px;
            }

            .chat-header p {
                font-size: 12px;
            }

            .message-bubble {
                max-width: 95%;
                padding: 12px 14px;
                font-size: 13px;
            }

            .chat-messages {
                padding: 12px;
                gap: 8px;
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
