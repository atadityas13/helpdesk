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
    $attachmentUrl = null;
    
    // Handle file attachment
    if (!empty($_FILES['attachment'])) {
        $file = $_FILES['attachment'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (in_array($file['type'], $allowedTypes) && $file['size'] <= 5 * 1024 * 1024) {
            $uploadsDir = __DIR__ . '/../../public/uploads';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }
            
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'attachment_' . $ticketId . '_' . time() . '.' . $ext;
            $filepath = $uploadsDir . '/' . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $attachmentUrl = 'public/uploads/' . $filename;
            }
        }
    }
    
    if (!empty($message) || $attachmentUrl) {
        addMessageToTicket($conn, $ticketId, 'admin', $_SESSION['admin_id'], $message, $attachmentUrl);
        
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
    <!-- EmojiMart CSS -->
    <link href="https://cdn.jsdelivr.net/npm/emoji-mart@latest/css/emoji-mart.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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

        .chat-message-attachment {
            max-width: 200px;
            border-radius: 6px;
            margin-top: 6px;
            cursor: pointer;
        }

        .typing-indicator {
            display: flex;
            gap: 4px;
            padding: 10px 12px;
            background: #e5e5e5;
            border-radius: 12px;
            width: fit-content;
        }

        .typing-dot {
            width: 6px;
            height: 6px;
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
            30% { transform: translateY(-8px); }
        }

        .chat-input {
            padding: 12px;
            border-top: 1px solid #f0f0f0;
        }

        .chat-form {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .input-row {
            display: flex;
            gap: 8px;
            align-items: flex-end;
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
            min-height: 45px;
        }

        .chat-form textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .icon-btn-admin {
            width: 40px;
            height: 40px;
            padding: 0;
            background: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .icon-btn-admin:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
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
        }

        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .emoji-picker-wrapper-admin {
            position: relative;
            display: inline-block;
        }

        .emoji-mart {
            position: absolute !important;
            bottom: 50px !important;
            right: 0 !important;
            z-index: 1000;
            max-height: 300px !important;
        }

        .file-input-label-admin {
            position: relative;
            overflow: hidden;
        }

        .file-input-label-admin input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .preview-area-admin {
            display: none;
            margin-bottom: 8px;
            padding: 8px;
            background: #f9f9f9;
            border-radius: 4px;
            position: relative;
        }

        .preview-area-admin.show {
            display: block;
        }

        .preview-image-admin {
            max-width: 120px;
            max-height: 120px;
            border-radius: 4px;
        }

        .remove-file-admin {
            position: absolute;
            top: 4px;
            right: 4px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
            font-size: 14px;
            line-height: 1;
        }

        .chat-message-time {
            font-size: 11px;
            color: #999;
            padding: 0 8px;
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
                <a href="../../logout.php" class="nav-item logout">
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
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h3><?php echo $selectedTicket['ticket_number']; ?></h3>
                                    <p><?php echo $selectedTicket['name']; ?> (<?php echo $selectedTicket['email']; ?>)</p>
                                </div>
                                <div style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end;">
                                    <?php
                                    $statuses = [
                                        'open' => ['label' => 'Terbuka', 'emoji' => '📂'],
                                        'in_progress' => ['label' => 'Sedang Diproses', 'emoji' => '⏳'],
                                        'resolved' => ['label' => 'Terselesaikan', 'emoji' => '✅'],
                                        'closed' => ['label' => 'Ditutup', 'emoji' => '🔒']
                                    ];
                                    
                                    foreach ($statuses as $st => $info):
                                        $isActive = ($selectedTicket['status'] === $st) ? 'active' : '';
                                    ?>
                                        <button type="button" 
                                                onclick="updateTicketStatus(<?php echo $ticketId; ?>, '<?php echo $st; ?>')"
                                                style="padding: 6px 12px; background: <?php echo ($selectedTicket['status'] === $st) ? '#667eea' : '#f0f0f0'; ?>; color: <?php echo ($selectedTicket['status'] === $st) ? 'white' : '#333'; ?>; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.3s;">
                                            <?php echo $info['emoji']; ?> <?php echo $info['label']; ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="chat-messages">
                            <?php foreach ($messages as $msg): ?>
                                <div class="chat-message <?php echo $msg['sender_type']; ?>">
                                    <div class="chat-message-sender">
                                        <?php echo $msg['sender_name']; ?>
                                        <?php if ($msg['sender_type'] === 'customer' && $msg['is_read']): ?>
                                            <span style="color: #28a745; font-size: 10px; margin-left: 6px;">✓✓ Dibaca</span>
                                        <?php elseif ($msg['sender_type'] === 'customer'): ?>
                                            <span style="color: #999; font-size: 10px; margin-left: 6px;">✓ Terkirim</span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="chat-message-content"><?php echo htmlspecialchars($msg['message']); ?></div>
                                        <?php if ($msg['attachment_url']): ?>
                                            <img src="../../<?php echo htmlspecialchars($msg['attachment_url']); ?>" 
                                                 class="chat-message-attachment" 
                                                 onclick="viewImage('../../<?php echo htmlspecialchars($msg['attachment_url']); ?>')">
                                        <?php endif; ?>
                                    </div>
                                    <div class="chat-message-time"><?php echo formatDateTime($msg['created_at']); ?></div>
                                </div>
                            <?php endforeach; ?>
                            <div id="typingIndicatorAdmin"></div>
                        </div>

                        <div class="chat-input">
                            <form id="adminMessageForm" class="chat-form">
                                <div class="preview-area-admin" id="previewAreaAdmin">
                                    <img id="previewImageAdmin" class="preview-image-admin" alt="Preview">
                                    <button type="button" class="remove-file-admin" onclick="removeFileAdmin()">✕</button>
                                </div>

                                <div class="input-row">
                                    <textarea id="adminMessageInput" name="message" placeholder="Ketik pesan..." rows="3"></textarea>
                                    
                                    <div class="emoji-picker-wrapper-admin">
                                        <button type="button" class="icon-btn-admin" id="emojiAdminBtn" title="Emoji">😊</button>
                                        <div id="emojiMartAdmin"></div>
                                    </div>
                                    
                                    <label class="icon-btn-admin file-input-label-admin" title="Lampirkan gambar">
                                        📎
                                        <input type="file" id="fileInputAdmin" accept="image/*" onchange="handleFileSelectAdmin(event)">
                                    </label>
                                    
                                    <button type="button" onclick="sendAdminMessage(event)" class="btn-send">Kirim</button>
                                </div>
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

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <!-- EmojiMart JS -->
    <script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>
    
    <script>
        let selectedFileAdmin = null;
        let emojiPickerOpenAdmin = false;
        const ticketIdAdmin = <?php echo $ticketId ?? 'null'; ?>;
        let typingTimeoutAdmin;

        const adminTextarea = document.getElementById('adminMessageInput');
        if (adminTextarea) {
            adminTextarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 100) + 'px';
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (ticketIdAdmin) {
                initEmojiPickerAdmin();
                startTypingIndicator();
                
                adminTextarea?.addEventListener('input', () => {
                    sendTypingStatusAdmin(true);
                    clearTimeout(typingTimeoutAdmin);
                    typingTimeoutAdmin = setTimeout(() => {
                        sendTypingStatusAdmin(false);
                    }, 3000);
                });
            }
        });

        function initEmojiPickerAdmin() {
            const emojiBtnAdmin = document.getElementById('emojiAdminBtn');
            if (!emojiBtnAdmin) return;
            
            emojiBtnAdmin.addEventListener('click', function(e) {
                e.preventDefault();
                emojiPickerOpenAdmin = !emojiPickerOpenAdmin;
                const emojiMartAdmin = document.getElementById('emojiMartAdmin');
                
                if (emojiPickerOpenAdmin) {
                    const divAdmin = document.createElement('div');
                    emojiMartAdmin.innerHTML = '';
                    emojiMartAdmin.appendChild(divAdmin);
                    
                    new EmojiMart.Picker({
                        onEmojiSelect: (emoji) => {
                            const currentText = adminTextarea.value;
                            adminTextarea.value = currentText + emoji.native;
                            adminTextarea.focus();
                            adminTextarea.dispatchEvent(new Event('input'));
                            emojiPickerOpenAdmin = false;
                            emojiMartAdmin.innerHTML = '';
                        },
                        theme: 'light',
                        set: 'native'
                    }).then(picker => {
                        divAdmin.appendChild(picker);
                    });
                } else {
                    emojiMartAdmin.innerHTML = '';
                }
            });
            
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.emoji-picker-wrapper-admin')) {
                    emojiPickerOpenAdmin = false;
                    document.getElementById('emojiMartAdmin').innerHTML = '';
                }
            });
        }

        function sendTypingStatusAdmin(isTyping) {
            if (!ticketIdAdmin) return;
            
            const ticketNumberEl = document.querySelector('.chat-header h3');
            const ticketNumber = ticketNumberEl?.textContent.trim();
            
            if (!ticketNumber) return;
            
            fetch('../../src/api/typing-status.php', {
                method: 'POST',
                body: JSON.stringify({
                    ticket_number: ticketNumber,
                    is_typing: isTyping
                }),
                headers: {
                    'Content-Type': 'application/json'
                }
            }).catch(error => console.error('Error:', error));
        }

        function startTypingIndicator() {
            if (!ticketIdAdmin) return;
            
            const ticketNumberEl = document.querySelector('.chat-header h3');
            const ticketNumber = ticketNumberEl?.textContent.trim();
            
            if (!ticketNumber) return;
            
            setInterval(() => {
                fetch(`../../src/api/typing-status.php?ticket_number=${ticketNumber}`)
                .then(response => response.json())
                .then(data => {
                    const typingIndicator = document.getElementById('typingIndicatorAdmin');
                    if (!typingIndicator) return;
                    
                    if (data.success && data.data && data.data.is_typing) {
                        if (!typingIndicator.innerHTML) {
                            typingIndicator.innerHTML = `
                                <div class="chat-message customer">
                                    <div class="typing-indicator">
                                        <div class="typing-dot"></div>
                                        <div class="typing-dot"></div>
                                        <div class="typing-dot"></div>
                                    </div>
                                    <div class="chat-message-time">Customer sedang mengetik...</div>
                                </div>
                            `;
                            const messagesArea = document.querySelector('.chat-messages');
                            if (messagesArea) messagesArea.scrollTop = messagesArea.scrollHeight;
                        }
                    } else {
                        typingIndicator.innerHTML = '';
                    }
                })
                .catch(error => console.error('Error checking typing:', error));
            }, 1000);
        }

        function handleFileSelectAdmin(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                alert('Hanya file gambar yang diizinkan');
                return;
            }

            if
