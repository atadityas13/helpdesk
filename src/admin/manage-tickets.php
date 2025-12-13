<?php
/**
 * Admin Manage Tickets - Bootstrap Design
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

// Get tickets
$tickets = [];
if ($result = $db->query("
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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tickets - Helpdesk Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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

        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .sidebar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            min-height: 100vh;
            position: fixed;
            width: 260px;
            left: 0;
            top: 0;
            z-index: 1000;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
        }

        .sidebar .navbar-brand {
            color: white !important;
            font-size: 1.5em;
            font-weight: 800;
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            padding: 12px 20px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .sidebar .nav-link:hover {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link.active {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.2);
            border-left-color: white;
            font-weight: 700;
        }

        .nav-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin: 12px 0;
        }

        .main-content {
            margin-left: 260px;
            padding: 30px;
        }

        .top-bar {
            background: white;
            padding: 20px 30px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar h1 {
            font-size: 2em;
            font-weight: 800;
            color: #1f2937;
            margin: 0;
        }

        .ticket-container {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 20px;
            height: calc(100vh - 200px);
        }

        .ticket-list-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .ticket-list-header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-bottom: 2px solid #e5e7eb;
            padding: 20px;
            font-weight: 700;
            color: #1f2937;
        }

        .ticket-list-body {
            flex: 1;
            overflow-y: auto;
        }

        .ticket-item {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .ticket-item:hover {
            background-color: #f9fafb;
        }

        .ticket-item.active {
            background-color: rgba(102, 126, 234, 0.1);
            border-left-color: var(--primary);
        }

        .ticket-number {
            font-weight: 700;
            color: var(--primary);
            font-size: 0.9em;
            margin-bottom: 6px;
        }

        .ticket-subject {
            color: #1f2937;
            font-size: 0.95em;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .ticket-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75em;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-open {
            background-color: rgba(245, 158, 11, 0.15);
            color: #92400e;
        }

        .badge-in-progress {
            background-color: rgba(59, 130, 246, 0.15);
            color: #1e40af;
        }

        .badge-resolved {
            background-color: rgba(16, 185, 129, 0.15);
            color: #065f46;
        }

        .badge-closed {
            background-color: rgba(107, 114, 128, 0.15);
            color: #374151;
        }

        .chat-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-bottom: 2px solid #e5e7eb;
            padding: 20px;
        }

        .chat-header h2 {
            font-size: 1.2em;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 4px 0;
        }

        .chat-header p {
            font-size: 0.9em;
            color: #6b7280;
            margin: 0;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background-color: #f9fafb;
        }

        .message {
            margin-bottom: 16px;
            display: flex;
            gap: 12px;
        }

        .message.admin {
            justify-content: flex-end;
        }

        .message-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.95em;
            line-height: 1.5;
            word-wrap: break-word;
        }

        .message.customer .message-bubble {
            background: white;
            color: #1f2937;
            border: 1px solid #e5e7eb;
            border-left: 3px solid var(--info);
        }

        .message.admin .message-bubble {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .message-sender {
            font-weight: 700;
            font-size: 0.85em;
            margin-bottom: 4px;
        }

        .message-time {
            font-size: 0.8em;
            opacity: 0.7;
            margin-top: 6px;
            font-style: italic;
        }

        .chat-input-area {
            border-top: 1px solid #e5e7eb;
            padding: 20px;
            background: white;
        }

        .status-select {
            width: 100%;
            margin-bottom: 12px;
        }

        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            font-size: 0.95em;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .btn-send {
            width: 100%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #6b7280;
            text-align: center;
        }

        .empty-icon {
            font-size: 3em;
            margin-bottom: 16px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                display: block;
            }

            .main-content {
                margin-left: 0;
                padding: 20px 15px;
            }

            .ticket-container {
                grid-template-columns: 1fr;
                height: auto;
            }

            .ticket-list-card {
                max-height: 300px;
            }

            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .top-bar h1 {
                font-size: 1.6em;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="navbar-brand">📊 Helpdesk</div>
        <nav class="nav flex-column p-3">
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-home me-2"></i> Dashboard
            </a>
            <a class="nav-link active" href="manage-tickets.php">
                <i class="fas fa-ticket-alt me-2"></i> Kelola Tickets
            </a>
            <a class="nav-link" href="faqs.php">
                <i class="fas fa-question-circle me-2"></i> FAQ Management
            </a>
            <div class="nav-divider"></div>
            <a class="nav-link" href="../../logout.php">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <h1>🎫 Kelola Tickets</h1>
        </div>

        <div class="ticket-container">
            <!-- Ticket List -->
            <div class="ticket-list-card">
                <div class="ticket-list-header">
                    <i class="fas fa-list me-2"></i> Tickets Aktif (<?php echo count($tickets); ?>)
                </div>
                <div class="ticket-list-body">
                    <?php if (empty($tickets)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">📭</div>
                            <p>Tidak ada ticket aktif</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($tickets as $ticket): ?>
                            <div class="ticket-item" onclick="selectTicket(<?php echo $ticket['id']; ?>)" data-ticket-id="<?php echo $ticket['id']; ?>">
                                <div class="ticket-number"><?php echo htmlspecialchars($ticket['ticket_number']); ?></div>
                                <div class="ticket-subject"><?php echo htmlspecialchars(substr($ticket['subject'], 0, 35)); ?></div>
                                <span class="ticket-status badge-<?php echo str_replace('_', '-', $ticket['status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chat Window -->
            <div class="chat-card">
                <div class="chat-header">
                    <h2 id="ticketTitle">Pilih Ticket</h2>
                    <p id="ticketSubtitle">Klik ticket di sidebar untuk mulai chat</p>
                </div>
                <div class="chat-messages" id="chatMessages">
                    <div class="empty-state">
                        <div class="empty-icon">💬</div>
                        <p>Pilih ticket untuk melihat pesan</p>
                    </div>
                </div>
                <div class="chat-input-area" id="chatInputArea" style="display: none;">
                    <select class="form-select status-select" id="statusSelect" onchange="updateTicketStatus()">
                        <option value="">Ubah Status...</option>
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                    <textarea class="form-control" id="messageInput" placeholder="Ketik respons..."></textarea>
                    <button class="btn-send" onclick="sendAdminMessage()">
                        <i class="fas fa-paper-plane me-2"></i> Kirim
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentTicketId = null;
        let messageRefreshInterval = null;

        function selectTicket(ticketId) {
            currentTicketId = ticketId;
            loadTicketMessages(ticketId);
            loadTicketDetails(ticketId);

            document.querySelectorAll('.ticket-item').forEach(el => {
                el.classList.remove('active');
            });
            document.querySelector(`[data-ticket-id="${ticketId}"]`).classList.add('active');

            document.getElementById('chatInputArea').style.display = 'block';

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
            if (messages.length === 0) {
                container.innerHTML = '<div class="empty-state"><div class="empty-icon">💬</div><p>Belum ada pesan</p></div>';
                return;
            }

            container.innerHTML = messages.map(msg => `
                <div class="message ${msg.sender_type}">
                    <div class="message-bubble">
                        <div class="message-sender">${msg.sender_name}</div>
                        ${msg.message}
                        <div class="message-time">${msg.created_at_formatted}</div>
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

        window.addEventListener('beforeunload', () => {
            if (messageRefreshInterval) clearInterval(messageRefreshInterval);
        });
    </script>
</body>
</html>
