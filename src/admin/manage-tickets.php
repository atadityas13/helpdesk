<?php
/**
 * Admin - Manage Tickets
 * Helpdesk MTsN 11 Majalengka
 */

require_once '../../src/config/database.php';
require_once '../../src/middleware/auth.php';
require_once '../../src/helpers/functions.php';
require_once '../../src/helpers/ticket.php';

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

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $ticketId) {
    $message = sanitizeInput($_POST['message'] ?? '');
    
    if (!empty($message)) {
        addMessageToTicket($conn, $ticketId, 'admin', $_SESSION['admin_id'], $message);
        
        // Update status if needed
        if ($selectedTicket['status'] === 'open') {
            updateTicketStatus($conn, $ticketId, 'in_progress');
        }
        
        header("Location: manage-tickets.php?ticket={$ticketId}");
        exit;
    }
}

// Get all tickets
$allTicketsQuery = "SELECT t.*, c.name, COUNT(m.id) as message_count
                    FROM tickets t
                    JOIN customers c ON t.customer_id = c.id
                    LEFT JOIN messages m ON t.id = m.ticket_id
                    GROUP BY t.id
                    ORDER BY t.updated_at DESC";

$allTickets = $conn->query($allTicketsQuery)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tickets - Helpdesk MTsN 11 Majalengka</title>
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <style>
        .tickets-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
            min-height: calc(100vh - 200px);
        }

        .tickets-list {
            background: white;
            border-radius: 8px;
            overflow-y: auto;
            max-height: 600px;
        }

        .ticket-item {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .ticket-item:hover {
            background: #f5f5f5;
        }

        .ticket-item.active {
            background: #f0f0ff;
            border-left: 3px solid #667eea;
        }

        .ticket-item-number {
            font-weight: 600;
            color: #667eea;
            font-size: 13px;
        }

        .ticket-item-customer {
            font-size: 13px;
            color: #333;
            margin-top: 4px;
        }

        .ticket-item-subject {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-panel {
            background: white;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            padding: 16px;
            border-bottom: 1px solid #f0f0f0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px 8px 0 0;
        }

        .chat-header h3 {
            margin: 0;
            font-size: 16px;
        }

        .chat-header p {
            margin: 4px 0 0 0;
            font-size: 12px;
            opacity: 0.9;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            background: #f8f9fa;
        }

        .chat-message {
            margin-bottom: 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .chat-message.customer {
            align-items: flex-end;
        }

        .chat-message.admin {
            align-items: flex-start;
        }

        .chat-message-sender {
            font-size: 12px;
            color: #999;
            padding: 0 8px;
        }

        .chat-message-content {
            max-width: 70%;
            padding: 10px 12px;
            border-radius: 12px;
            word-wrap: break-word;
            font-size: 13px;
        }

        .chat-message.customer .chat-message-content {
            background: #667eea;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .chat-message.admin .chat-message-content {
            background: #e5e5e5;
            color: #333;
            border-bottom-left-radius: 4px;
        }

        .chat-message-time {
            font-size: 11px;
            color: #999;
            padding: 0 8px;
        }

        .chat-input {
            padding: 12px;
            border-top: 1px solid #f0f0f0;
        }

        .chat-form {
            display: flex;
            gap: 8px;
        }

        .chat-form textarea {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
            font-family: inherit;
            resize: none;
            max-height: 100px;
        }

        .chat-form textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-send {
            padding: 10px 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            align-self: flex-end;
        }

        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .no-ticket {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 400px;
            color: #999;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-logo">
                <h2>🎓 Helpdesk</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <span>📊 Dashboard</span>
                </a>
                <a href="manage-tickets.php" class="nav-item active">
                    <span>🎫 Kelola Tickets</span>
                </a>
                <a href="faqs.php" class="nav-item">
                    <span>❓ FAQ</span>
                </a>
                <a href="../../login.php" class="nav-item logout">
                    <span>🚪 Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <!-- Header -->
            <header class="admin-header">
                <h1>Kelola Tickets</h1>
                <div class="admin-user">
                    <span><?php echo $_SESSION['admin_username']; ?></span>
                </div>
            </header>

            <!-- Tickets Layout -->
            <div class="tickets-layout">
                <!-- Tickets List -->
                <div class="tickets-list">
                    <?php foreach ($allTickets as $ticket): ?>
                        <a href="?ticket=<?php echo $ticket['id']; ?>" 
                           class="ticket-item <?php echo ($ticketId == $ticket['id']) ? 'active' : ''; ?>">
                            <div class="ticket-item-number"><?php echo $ticket['ticket_number']; ?></div>
                            <div class="ticket-item-customer"><?php echo $ticket['name']; ?></div>
                            <div class="ticket-item-subject"><?php echo $ticket['subject']; ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Chat Panel -->
                <div class="chat-panel">
                    <?php if ($selectedTicket): ?>
                        <div class="chat-header">
                            <h3><?php echo $selectedTicket['ticket_number']; ?></h3>
                            <p><?php echo $selectedTicket['name']; ?> (<?php echo $selectedTicket['email']; ?>)</p>
                        </div>

                        <div class="chat-messages">
                            <?php foreach ($messages as $msg): ?>
                                <div class="chat-message <?php echo $msg['sender_type']; ?>">
                                    <div class="chat-message-sender"><?php echo $msg['sender_name']; ?></div>
                                    <div class="chat-message-content"><?php echo $msg['message']; ?></div>
                                    <div class="chat-message-time"><?php echo formatDateTime($msg['created_at']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="chat-input">
                            <form method="POST" class="chat-form">
                                <textarea name="message" placeholder="Ketik pesan..." rows="3" required></textarea>
                                <button type="submit" class="btn-send">Kirim</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="no-ticket">
                            Pilih ticket untuk mulai chatting
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
