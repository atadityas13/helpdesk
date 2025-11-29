<?php
/**
 * Admin - Manage Tickets
 * Helpdesk MTsN 11 Majalengka
 */

require_once '../../src/config/database.php';
require_once '../../src/middleware/auth.php';
require_once '../../src/helpers/functions.php';

requireAdminLogin();

// Ambil semua ticket
$tickets = $conn->query("
    SELECT t.*, c.name AS customer_name, c.email AS customer_email,
           (SELECT message FROM messages WHERE ticket_id = t.id ORDER BY created_at DESC LIMIT 1) AS last_message,
           (SELECT created_at FROM messages WHERE ticket_id = t.id ORDER BY created_at DESC LIMIT 1) AS last_message_time
    FROM tickets t
    JOIN customers c ON t.customer_id = c.id
    ORDER BY t.updated_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Detail ticket jika ada parameter ?ticket=
$activeTicket = null;
$ticketMessages = [];

if (isset($_GET['ticket']) && is_numeric($_GET['ticket'])) {
    $ticketId = intval($_GET['ticket']);

    // Ambil data ticket
    $stmt = $conn->prepare("
        SELECT t.*, c.name AS customer_name, c.email AS customer_email 
        FROM tickets t
        JOIN customers c ON t.customer_id = c.id
        WHERE t.id = ?
    ");
    $stmt->bind_param("i", $ticketId);
    $stmt->execute();
    $activeTicket = $stmt->get_result()->fetch_assoc();

    // Ambil pesan
    if ($activeTicket) {
        $stmt2 = $conn->prepare("
            SELECT * FROM messages 
            WHERE ticket_id = ?
            ORDER BY created_at ASC
        ");
        $stmt2->bind_param("i", $ticketId);
        $stmt2->execute();
        $ticketMessages = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tickets - Helpdesk MTsN 11 Majalengka</title>
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <style>
        .tickets-container {
            display: grid;
            grid-template-columns: 320px 1fr;
            height: calc(100vh - 70px);
            overflow: hidden;
        }

        .tickets-list {
            background: #fff;
            border-right: 1px solid #e5e5e5;
            overflow-y: auto;
        }

        .ticket-item {
            padding: 16px;
            border-bottom: 1px solid #f2f2f2;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .ticket-item:hover {
            background: #f8f8ff;
        }

        .ticket-item.active {
            background: #eef1ff;
            border-left: 4px solid #667eea;
        }

        .ticket-name {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .ticket-email {
            font-size: 12px;
            color: #666;
            margin-bottom: 6px;
        }

        .ticket-last-message {
            font-size: 12px;
            color: #333;
            margin-bottom: 6px;
        }

        .ticket-last-time {
            font-size: 11px;
            color: #888;
        }

        .chat-area {
            background: #fafafa;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .chat-header {
            padding: 16px;
            background: white;
            border-bottom: 1px solid #e5e5e5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .message {
            margin-bottom: 20px;
            max-width: 70%;
            padding: 12px;
            border-radius: 10px;
            line-height: 1.4;
            position: relative;
        }

        .message.admin {
            background: #e2e8ff;
            margin-left: auto;
            border-top-right-radius: 2px;
        }

        .message.customer {
            background: #ffffff;
            border: 1px solid #ddd;
            border-top-left-radius: 2px;
        }

        .typing-indicator {
            font-size: 12px;
            color: #666;
            margin-left: 12px;
            margin-bottom: 8px;
            display: none;
        }

        .chat-input-area {
            padding: 12px;
            background: white;
            border-top: 1px solid #e5e5e5;
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

    <main class="admin-content">
        <!-- Header -->
        <header class="admin-header">
            <h1>Kelola Tickets</h1>
            <div class="admin-user">
                <span><?php echo $_SESSION['admin_username']; ?></span>
            </div>
        </header>

        <div class="tickets-container">
            
            <!-- TICKETS LIST -->
            <div class="tickets-list">
                <?php foreach ($tickets as $t): ?>
                    <?php 
                        $active = ($activeTicket && $activeTicket['id'] == $t['id']) ? 'active' : '';
                    ?>
                    <a href="manage-tickets.php?ticket=<?php echo $t['id']; ?>" style="text-decoration:none;color:inherit;">
                        <div class="ticket-item <?php echo $active; ?>">
                            <div class="ticket-name"><?php echo htmlspecialchars($t['customer_name']); ?></div>
                            <div class="ticket-email"><?php echo htmlspecialchars($t['customer_email']); ?></div>

                            <div class="ticket-last-message">
                                <?php echo htmlspecialchars(substr($t['last_message'], 0, 60)); ?>...
                            </div>

                            <div class="ticket-last-time">
                                <?php echo formatDateTime($t['last_message_time']); ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <!-- CHAT AREA -->
            <div class="chat-area">
                <?php if ($activeTicket): ?>

                    <!-- CHAT HEADER -->
                    <div class="chat-header">
                        <div>
                            <h3 style="margin:0;">Ticket #<?php echo $activeTicket['id']; ?></h3>
                            <small>
                                Pengguna: 
                                <strong><?php echo htmlspecialchars($activeTicket['customer_name']); ?></strong> 
                                (<?php echo htmlspecialchars($activeTicket['customer_email']); ?>)
                            </small>
                        </div>

                        <select id="ticketStatus" onchange="updateTicketStatus(<?php echo $activeTicket['id']; ?>, this.value)">
                            <option value="open"   <?php if ($activeTicket['status']=='open') echo 'selected'; ?>>🔵 Dibuka</option>
                            <option value="pending"<?php if ($activeTicket['status']=='pending') echo 'selected'; ?>>🟠 Menunggu</option>
                            <option value="closed" <?php if ($activeTicket['status']=='closed') echo 'selected'; ?>>⚫ Ditutup</option>
                        </select>
                    </div>

                    <!-- TYPING INDICATOR -->
                    <div id="typingIndicator" class="typing-indicator">
                        Pengguna sedang mengetik...
                    </div>

                    <!-- CHAT MESSAGES -->
                    <div class="chat-messages" id="chatMessages">
                        <?php foreach ($ticketMessages as $msg): ?>
                            <div class="message <?php echo $msg['sender_type']; ?>">
                                <?php if (!empty($msg['attachment'])): ?>
                                    <img src="../../uploads/<?php echo $msg['attachment']; ?>" 
                                         onclick="viewImage('../../uploads/<?php echo $msg['attachment']; ?>')" 
                                         style="max-width:150px;border-radius:6px;margin-bottom:10px;cursor:pointer;">
                                <?php endif; ?>

                                <div><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>

                                <small style="font-size:11px;color:#666;display:block;margin-top:6px;">
                                    <?php echo formatDateTime($msg['created_at']); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- CHAT INPUT -->
                    <div class="chat-input-area">
                        <form onsubmit="sendMessageAdmin(event)">
                            <div style="display:flex;gap:10px;align-items:center;">
                                <textarea 
                                    id="adminMessageInput" 
                                    placeholder="Ketik pesan untuk pengguna..." 
                                    oninput="adjustHeight(this); sendTypingStatusAdmin(true);" 
                                    style="flex:1;resize:none;min-height:40px;padding:10px;border-radius:8px;border:1px solid #ddd;background:#fff;">
                                </textarea>

                                <label style="cursor:pointer;">
                                    📎
                                    <input type="file" id="adminFile" accept="image/*" onchange="previewFileAdmin()" style="display:none;">
                                </label>

                                <button type="submit" 
                                        style="padding:10px 16px;border:none;border-radius:8px;background:#4c6ef5;color:white;cursor:pointer;">
                                    📤 Kirim
                                </button>
                            </div>

                            <div id="filePreviewAdmin" style="display:none;margin-top:10px;">
                                <div style="padding:8px;background:#eef;display:flex;justify-content:space-between;align-items:center;border-radius:6px;">
                                    <span id="fileNameAdmin"></span>
                                    <button type="button" onclick="removeFileAdmin()" style="border:none;background:none;font-size:16px;cursor:pointer;">❌</button>
                                </div>
                            </div>
                        </form>
                    </div>

                <?php else: ?>
                    <div style="padding:40px;text-align:center;color:#777;">
                        <h3>Pilih ticket untuk mulai percakapan</h3>
                        <p>Daftar ticket tersedia di sebelah kiri.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
    // ========== Typing Indicator ==========
    function sendTypingStatusAdmin(isTyping) {
        fetch('../../src/api/typing-status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                ticket_id: <?php echo $activeTicket ? $activeTicket['id'] : 'null'; ?>,
                sender_type: 'admin',
                is_typing: isTyping
            })
        });
    }

    function checkTypingStatus() {
        <?php if ($activeTicket): ?>
        fetch('../../src/api/get-typing-status.php?ticket_id=<?php echo $activeTicket['id']; ?>')
            .then(res => res.json())
            .then(data => {
                let indicator = document.getElementById('typingIndicator');
                indicator.style.display = data.is_typing ? 'block' : 'none';
            });
        <?php endif; ?>
    }

    setInterval(checkTypingStatus, 1000);

    // ========== Auto Height Textarea ==========
    function adjustHeight(el) {
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
    }

    // ========== File Upload Preview ==========
    let selectedFileAdmin = null;

    function previewFileAdmin() {
        const file = document.getElementById('adminFile').files[0];
        if (file) {
            selectedFileAdmin = file;
            document.getElementById('fileNameAdmin').textContent = file.name;
            document.getElementById('filePreviewAdmin').style.display = 'block';
        }
    }

    function removeFileAdmin() {
        selectedFileAdmin = null;
        document.getElementById('adminFile').value = '';
        document.getElementById('filePreviewAdmin').style.display = 'none';
    }

    // ========== Send Message ==========
    function sendMessageAdmin(event) {
        event.preventDefault();

        const input = document.getElementById('adminMessageInput');
        const message = input.value.trim();

        if (!message && !selectedFileAdmin) {
            Swal.fire({
                icon: 'warning',
                title: 'Pesan Kosong',
                text: 'Silakan ketik pesan atau pilih gambar'
            });
            return;
        }

        const btn = event.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = '⏳ Mengirim...';

        const ticketNumber = "<?php echo $activeTicket['id'] ?? ''; ?>";

        const formData = new FormData();
        formData.append('ticket_number', ticketNumber);
        formData.append('message', message);
        formData.append('sender_type', 'admin');

        if (selectedFileAdmin) {
            formData.append('attachment', selectedFileAdmin);
        }

        fetch('../../src/api/send-message.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                removeFileAdmin();
                sendTypingStatusAdmin(false);
                setTimeout(() => location.reload(), 500);

                Swal.fire({
                    icon: 'success',
                    title: 'Pesan Terkirim',
                    text: 'Pesan berhasil dikirim',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message || 'Terjadi kesalahan'
                });
            }
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = '📤 Kirim';
        });
    }

    // ========== Update Status ==========
    function updateTicketStatus(ticketId, status) {
        fetch('../../src/api/update-ticket-status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ ticket_id: ticketId, status })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Status Diubah',
                    text: 'Status ticket telah diperbarui',
                    timer: 1500,
                    showConfirmButton: false
                });
                setTimeout(() => location.reload(), 500);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message || 'Terjadi kesalahan'
                });
            }
        });
    }

    // ========== View Image ==========
    function viewImage(url) {
        Swal.fire({
            imageUrl: url,
            showCloseButton: true,
            confirmButtonText: 'Tutup'
        });
    }
</script>

</body>
</html>
