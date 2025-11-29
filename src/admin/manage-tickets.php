<?php
/**
 * Admin - Manage Tickets
 * Helpdesk MTsN 11 Majalengka
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/ticket.php';
require_once __DIR__ . '/../helpers/admin-status.php';

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
    <link href="https://cdn.jsdelivr.net/npm/emoji-mart@latest/css/emoji-mart.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
                                <div class="status-buttons">
                                    <?php
                                    $statuses = [
                                        'open' => ['label' => 'Terbuka', 'emoji' => '📂'],
                                        'in_progress' => ['label' => 'Diproses', 'emoji' => '⏳'],
                                        'resolved' => ['label' => 'Selesai', 'emoji' => '✅'],
                                        'closed' => ['label' => 'Ditutup', 'emoji' => '🔒']
                                    ];
                                    
                                    foreach ($statuses as $st => $info):
                                        $isActive = ($selectedTicket['status'] === $st) ? 'active' : '';
                                    ?>
                                        <button type="button" 
                                                class="status-btn <?php echo $isActive; ?>"
                                                onclick="updateTicketStatus(<?php echo $ticketId; ?>, '<?php echo $st; ?>')">
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
                                            <span style="color: #4caf50; font-size: 10px; margin-left: 6px;">✓✓ Dibaca</span>
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
                                        📷
                                        <input type="file" id="fileInputAdmin" accept="image/*" onchange="handleFileSelectAdmin(event)">
                                    </label>
                                    
                                    <button type="button" onclick="sendAdminMessage(event)" class="btn-send">➤ Kirim</button>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>
    
    <script>
        let selectedFileAdmin = null;
        let emojiPickerOpenAdmin = false;
        const ticketIdAdmin = <?php echo $ticketId ?? 'null'; ?>;
        const ticketNumberAdmin = '<?php echo htmlspecialchars($selectedTicket['ticket_number'] ?? ''); ?>';
        let typingTimeoutAdmin;
        let messageRefreshIntervalAdmin;
        let currentlyViewingTicket = ticketNumberAdmin;

        const adminTextarea = document.getElementById('adminMessageInput');
        if (adminTextarea) {
            adminTextarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 100) + 'px';
            });
        }

        let adminViewingIntervalAdmin; // Interval untuk keep-alive tracking

        document.addEventListener('DOMContentLoaded', () => {
            if (ticketIdAdmin) {
                initEmojiPickerAdmin();
                startTypingIndicatorAdmin();
                
                // Track viewing untuk ticket yang dipilih
                trackAdminViewing(true);
                
                // Keep-alive: Update last_view setiap 10 detik agar tetap terdeteksi
                adminViewingIntervalAdmin = setInterval(() => {
                    trackAdminViewing(true);
                }, 10000);
                
                loadMessagesAdmin();
                
                messageRefreshIntervalAdmin = setInterval(loadMessagesAdmin, 1500);
                
                adminTextarea?.addEventListener('input', () => {
                    sendTypingStatusAdmin(true);
                    clearTimeout(typingTimeoutAdmin);
                    typingTimeoutAdmin = setTimeout(() => {
                        sendTypingStatusAdmin(false);
                    }, 3000);
                });
            }
            
            // Setup untuk track ketika user klik ticket lain
            setupTicketNavigation();
        });

        function setupTicketNavigation() {
            document.querySelectorAll('.ticket-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    // Stop tracking ticket sebelumnya LANGSUNG tanpa delay
                    if (currentlyViewingTicket) {
                        trackAdminViewing(false);
                    }
                    
                    // Biarkan navigasi berjalan normal (tidak perlu preventDefault)
                    // Link akan pindah ke ticket baru
                });
            });
        }

        function trackAdminViewing(isViewing) {
            if (!ticketNumberAdmin) return;
            
            fetch('../../src/api/admin-viewing.php', {
                method: 'POST',
                body: JSON.stringify({
                    ticket_number: ticketNumberAdmin,
                    is_viewing: isViewing
                }),
                headers: { 'Content-Type': 'application/json' }
            }).catch(e => console.error('Error tracking view:', e));
        }

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
                    
                    try {
                        new EmojiMart.Picker({
                            onEmojiSelect: (emoji) => {
                                adminTextarea.value += emoji.native;
                                adminTextarea.focus();
                                adminTextarea.dispatchEvent(new Event('input'));
                                emojiPickerOpenAdmin = false;
                                emojiMartAdmin.innerHTML = '';
                            },
                            theme: 'light',
                            set: 'native',
                            previewPosition: 'none',
                            perLine: 8
                        }).then(picker => divAdmin.appendChild(picker)).catch(e => console.error('Emoji error:', e));
                    } catch (error) {
                        console.error('Emoji error:', error);
                    }
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
            if (!ticketNumberAdmin) return;
            
            fetch('../../src/api/typing-status.php', {
                method: 'POST',
                body: JSON.stringify({
                    ticket_number: ticketNumberAdmin,
                    is_typing: isTyping,
                    sender_type: 'admin'
                }),
                headers: { 'Content-Type': 'application/json' }
            }).catch(e => console.error('Error:', e));
        }

        function startTypingIndicatorAdmin() {
            if (!ticketNumberAdmin) return;
            
            setInterval(() => {
                fetch(`../../src/api/typing-status.php?ticket_number=${ticketNumberAdmin}`)
                .then(r => r.json())
                .then(data => {
                    const typingContainer = document.getElementById('typingIndicatorAdmin');
                    if (!typingContainer) return;
                    
                    if (data.success && data.data && data.data.is_typing) {
                        const senderType = data.data.sender_type;
                        
                        if (senderType === 'customer') {
                            if (!typingContainer.innerHTML) {
                                typingContainer.innerHTML = `
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
                            typingContainer.innerHTML = '';
                        }
                    } else {
                        typingContainer.innerHTML = '';
                    }
                })
                .catch(e => console.error('Error:', e));
            }, 2000);
        }

        function loadMessagesAdmin() {
            if (!ticketNumberAdmin) return;
            
            fetch(`../../src/api/get-messages.php?ticket_number=${ticketNumberAdmin}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data) {
                    displayMessagesAdmin(data.data);
                }
            })
            .catch(e => console.error('Error:', e));
        }

        function displayMessagesAdmin(ticketData) {
            const messages = ticketData.messages || [];
            const messagesArea = document.querySelector('.chat-messages');
            
            if (!messagesArea) return;
            
            const typingIndicator = messagesArea.querySelector('#typingIndicatorAdmin');
            const existingMessages = messagesArea.querySelectorAll('.chat-message');
            
            if (existingMessages.length === messages.length) {
                return; // Tidak perlu re-render jika jumlah sama
            }
            
            const typingContent = typingIndicator?.innerHTML;
            messagesArea.innerHTML = '';
            
            if (messages.length === 0) {
                messagesArea.innerHTML = '<div style="text-align: center; color: #999; padding: 20px;">Belum ada pesan</div>';
                if (typingIndicator) {
                    const div = document.createElement('div');
                    div.id = 'typingIndicatorAdmin';
                    div.innerHTML = typingContent;
                    messagesArea.appendChild(div);
                }
                return;
            }
            
            messages.forEach(msg => {
                const messageEl = document.createElement('div');
                messageEl.className = `chat-message ${msg.sender_type}`;
                
                let statusHtml = '';
                if (msg.sender_type === 'customer' && msg.is_read) {
                    statusHtml = '<span style="color: #4caf50; font-size: 10px; margin-left: 6px;">✓✓ Dibaca</span>';
                } else if (msg.sender_type === 'customer') {
                    statusHtml = '<span style="color: #999; font-size: 10px; margin-left: 6px;">✓ Terkirim</span>';
                }
                
                let attachmentHtml = '';
                if (msg.attachment_url) {
                    attachmentHtml = `<img src="../../${msg.attachment_url}" class="chat-message-attachment" onclick="viewImage('../../${msg.attachment_url}')">`;
                }
                
                const timeStr = new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                
                messageEl.innerHTML = `
                    <div class="chat-message-sender">
                        ${msg.sender_name}
                        ${statusHtml}
                    </div>
                    <div>
                        <div class="chat-message-content">${msg.message}${attachmentHtml}</div>
                    </div>
                    <div class="chat-message-time">${timeStr}</div>
                `;
                
                messagesArea.appendChild(messageEl);
            });
            
            const newTypingDiv = document.createElement('div');
            newTypingDiv.id = 'typingIndicatorAdmin';
            newTypingDiv.innerHTML = typingContent || '';
            messagesArea.appendChild(newTypingDiv);
            
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }

        function handleFileSelectAdmin(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                Swal.fire({ icon: 'error', title: 'File Tidak Valid', text: 'Hanya file gambar yang diizinkan' });
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({ icon: 'error', title: 'File Terlalu Besar', text: 'Ukuran file maksimal 5MB' });
                return;
            }

            const reader = new FileReader();
            reader.onload = e => {
                selectedFileAdmin = file;
                document.getElementById('previewImageAdmin').src = e.target.result;
                document.getElementById('previewAreaAdmin').classList.add('show');
            };
            reader.readAsDataURL(file);
        }

        function removeFileAdmin() {
            selectedFileAdmin = null;
            document.getElementById('fileInputAdmin').value = '';
            document.getElementById('previewAreaAdmin').classList.remove('show');
        }

        function sendAdminMessage(event) {
            event.preventDefault();
            
            const message = adminTextarea.value.trim();
            if (!message && !selectedFileAdmin) {
                Swal.fire({ icon: 'warning', title: 'Pesan Kosong', text: 'Silakan ketik pesan atau pilih gambar' });
                return;
            }

            const btn = event.target;
            btn.disabled = true;

            const formData = new FormData();
            formData.append('ticket_number', ticketNumberAdmin);
            formData.append('message', message);
            formData.append('sender_type', 'admin');
            
            if (selectedFileAdmin) formData.append('attachment', selectedFileAdmin);

            fetch('../../src/api/send-message.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    adminTextarea.value = '';
                    removeFileAdmin();
                    btn.disabled = false;
                    Swal.fire({ icon: 'success', title: 'Pesan Terkirim', text: 'Pesan Anda telah terkirim' });
                    
                    // Refresh messages
                    loadMessagesAdmin();
                } else {
                    btn.disabled = false;
                    Swal.fire({ icon: 'error', title: 'Gagal Mengirim Pesan', text: data.message });
                }
            })
            .catch(e => {
                btn.disabled = false;
                console.error('Error:', e);
                Swal.fire({ icon: 'error', title: 'Gagal Mengirim Pesan', text: 'Terjadi kesalahan pada server' });
            });
        }

        function updateTicketStatus(ticketId, status) {
            const statusLabel = {
                open: 'Terbuka',
                in_progress: 'Diproses',
                resolved: 'Selesai',
                closed: 'Ditutup'
            }[status];
            
            if (!statusLabel) return;
            
            Swal.fire({
                title: `Ubah Status Ticket`,
                text: `Apakah Anda yakin ingin mengubah status ticket ini menjadi "${statusLabel}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Ubah',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('../../src/api/update-ticket-status.php', {
                        method: 'POST',
                        body: JSON.stringify({ ticket_id: ticketId, status: status }),
                        headers: { 'Content-Type': 'application/json' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Status Diubah', text: `Status ticket telah diubah menjadi "${statusLabel}"` });
                            
                            // Update status button: hapus semua .active lalu aktifkan tombol sesuai status
                            document.querySelectorAll('.status-btn').forEach(btn => btn.classList.remove('active'));
                            const targetBtn = document.querySelector(`.status-btn[onclick*="${status}"]`);
                            if (targetBtn) targetBtn.classList.add('active');
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal Mengubah Status', text: data.message });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({ icon: 'error', title: 'Gagal Mengubah Status', text: 'Terjadi kesalahan pada server' });
                    });
                }
            });
        }

        function viewImage(url) {
            const img = new Image();
            img.src = url;
            const w = window.open("");
            w.document.write(img.outerHTML);
        }

        window.addEventListener('beforeunload', () => {
            // Stop tracking LANGSUNG ketika meninggalkan page
            if (ticketNumberAdmin) {
                // Gunakan navigator.sendBeacon untuk ensure request terkirim meski page unload
                navigator.sendBeacon('../../src/api/admin-viewing.php', JSON.stringify({
                    ticket_number: ticketNumberAdmin,
                    is_viewing: false
                }));
            }
            
            if (messageRefreshIntervalAdmin) clearInterval(messageRefreshIntervalAdmin);
            if (adminViewingIntervalAdmin) clearInterval(adminViewingIntervalAdmin);
            sendTypingStatusAdmin(false);
        });
    </script>
</body>
</html>
