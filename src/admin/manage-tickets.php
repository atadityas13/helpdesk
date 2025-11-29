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
    // Pastikan ID adalah integer
    $ticketId = intval($ticketId);

    // Ambil data ticket yang dipilih
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
$allTicketsQuery = "SELECT t.*, c.name, 
                           (SELECT message FROM messages WHERE ticket_id = t.id ORDER BY created_at DESC LIMIT 1) as last_message,
                           (SELECT sender_type FROM messages WHERE ticket_id = t.id ORDER BY created_at DESC LIMIT 1) as last_sender
                    FROM tickets t
                    JOIN customers c ON t.customer_id = c.id
                    ORDER BY t.updated_at DESC"; // Sortir berdasarkan updated_at untuk menempatkan yang baru/aktif di atas

$allTickets = $conn->query($allTicketsQuery)->fetch_all(MYSQLI_ASSOC);

$adminUsername = $_SESSION['admin_username'] ?? 'Admin Helpdesk';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tickets - Helpdesk MTsN 11 Majalengka</title>
    <link rel="stylesheet" href="../../public/css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/emoji-mart@latest/css/emoji-mart.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <div class="admin-app-layout">

        <aside class="main-sidebar">
            <div class="sidebar-logo">
                <h2>🎓 **Helpdesk**</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="manage-tickets.php" class="nav-item active"><i class="fas fa-headset"></i> **Kelola Tiket**</a>
                <a href="manage-users.php" class="nav-item"><i class="fas fa-users"></i> Kelola User</a>
                <a href="faqs.php" class="nav-item"><i class="fas fa-question-circle"></i> FAQ</a>
                <a href="../../logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="admin-content-area ticket-chat-layout">
            
            <header class="admin-header">
                <div class="header-left">
                    <h1>**Antrian Tiket Masuk**</h1>
                    <p class="greeting-message">Total **<?php echo count($allTickets); ?>** tiket sedang menunggu tanggapan Anda.</p>
                </div>
                <div class="admin-user-info">
                    <span class="user-avatar-header"><?php echo strtoupper(substr($adminUsername, 0, 1)); ?></span>
                </div>
            </header>

            <div class="ticket-chat-container">
                
                <div class="tickets-list">
                    <div class="search-filter-area">
                        <input type="text" id="ticketSearch" placeholder="Cari berdasarkan nama/nomor tiket..." onkeyup="filterTickets()">
                    </div>
                    <?php if (count($allTickets) > 0): ?>
                        <div class="ticket-list-content">
                            <?php foreach ($allTickets as $ticket): 
                                $statusClass = str_replace('_', '-', $ticket['status']); 
                                $isActive = ($ticketId == $ticket['id']) ? 'active' : '';
                                
                                // Tampilkan status tiket dengan badge
                                $statusBadge = getStatusBadge($ticket['status']);
                                
                                // Tampilkan ringkasan pesan terakhir
                                $lastMessageText = !empty($ticket['last_message']) ? htmlspecialchars(substr($ticket['last_message'], 0, 50)) . '...' : 'Belum ada pesan';
                                $lastSenderIcon = ($ticket['last_sender'] === 'admin') ? '<i class="fas fa-reply"></i>' : '<i class="fas fa-comment"></i>';
                            ?>
                                <a href="?ticket=<?php echo $ticket['id']; ?>" 
                                   class="ticket-item <?php echo $isActive; ?> status-<?php echo $statusClass; ?>"
                                   data-search="<?php echo htmlspecialchars($ticket['name'] . ' ' . $ticket['ticket_number']); ?>">
                                    <div class="ticket-info">
                                        <div class="ticket-header">
                                            <span class="ticket-item-number">#<?php echo $ticket['ticket_number']; ?></span>
                                            <?php echo $statusBadge; ?>
                                        </div>
                                        <div class="ticket-item-customer">**<?php echo htmlspecialchars($ticket['name']); ?>**</div>
                                        <div class="ticket-item-subject"><?php echo htmlspecialchars($ticket['subject']); ?></div>
                                        <div class="ticket-item-last-message">
                                            <?php echo $lastSenderIcon; ?> <?php echo $lastMessageText; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">Tidak ada tiket aktif saat ini.</div>
                    <?php endif; ?>
                </div>

                <div class="chat-panel">
                    <?php if ($selectedTicket): ?>
                        <div class="chat-header">
                            <div class="ticket-meta">
                                <h3 class="ticket-subject-title">
                                    <i class="fas fa-ticket-alt"></i> <?php echo htmlspecialchars($selectedTicket['subject']); ?>
                                </h3>
                                <p class="customer-info">
                                    **<?php echo htmlspecialchars($selectedTicket['name']); ?>** (<?php echo htmlspecialchars($selectedTicket['email']); ?>)
                                    | Telp: <?php echo htmlspecialchars($selectedTicket['phone']); ?>
                                </p>
                            </div>
                            
                            <div class="status-buttons">
                                <?php
                                $statuses = [
                                    'open' => ['label' => 'Terbuka', 'icon' => 'fas fa-folder-open'],
                                    'in_progress' => ['label' => 'Diproses', 'icon' => 'fas fa-spinner'],
                                    'resolved' => ['label' => 'Selesai', 'icon' => 'fas fa-check-circle'],
                                    'closed' => ['label' => 'Ditutup', 'icon' => 'fas fa-lock']
                                ];
                                
                                foreach ($statuses as $st => $info):
                                    $isActive = ($selectedTicket['status'] === $st) ? 'active' : '';
                                ?>
                                    <button type="button" 
                                            class="status-btn btn-status-<?php echo str_replace('_', '-', $st); ?> <?php echo $isActive; ?>"
                                            onclick="updateTicketStatus(<?php echo $ticketId; ?>, '<?php echo $st; ?>')">
                                        <i class="<?php echo $info['icon']; ?>"></i> <?php echo $info['label']; ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="chat-messages" id="chatMessagesArea">
                            <?php foreach ($messages as $msg): ?>
                                <div class="chat-message <?php echo $msg['sender_type']; ?>">
                                    <div class="chat-bubble">
                                        <div class="chat-message-sender">
                                            **<?php echo ($msg['sender_type'] === 'admin') ? 'Anda' : htmlspecialchars($msg['sender_name']); ?>**
                                        </div>
                                        
                                        <div class="chat-message-content">
                                            <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                            <?php if ($msg['attachment_url']): ?>
                                                <div class="attachment-preview">
                                                    <img src="../../<?php echo htmlspecialchars($msg['attachment_url']); ?>" 
                                                         class="chat-message-attachment" 
                                                         onclick="viewImage('../../<?php echo htmlspecialchars($msg['attachment_url']); ?>')">
                                                    <small class="attachment-link" onclick="viewImage('../../<?php echo htmlspecialchars($msg['attachment_url']); ?>')">Lihat Lampiran</small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="chat-message-time-status">
                                            <span class="chat-message-time">
                                                <?php echo formatDateTime($msg['created_at']); ?>
                                            </span>
                                            <?php 
                                                // Tampilkan status baca hanya untuk pesan Admin (Admin sent, Customer received/read)
                                                if ($msg['sender_type'] === 'admin'): 
                                            ?>
                                                <span class="read-status">
                                                    <?php if ($selectedTicket['status'] !== 'closed' && $msg['is_read']): ?>
                                                        <i class="fas fa-check-double read"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-check"></i>
                                                    <?php endif; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div id="typingIndicatorAdmin"></div>
                        </div>

                        <div class="chat-input">
                            <form id="adminMessageForm" class="chat-form">
                                <div class="preview-area-admin" id="previewAreaAdmin">
                                    <span class="file-name-preview" id="fileNameAdmin"></span>
                                    <img id="previewImageAdmin" class="preview-image-admin" alt="Preview">
                                    <button type="button" class="remove-file-admin" onclick="removeFileAdmin()">✕</button>
                                </div>

                                <div class="input-row">
                                    <div class="textarea-wrapper">
                                        <textarea id="adminMessageInput" name="message" placeholder="Ketik pesan balasan..." rows="1"></textarea>
                                        <div class="icon-group">
                                            <div class="emoji-picker-wrapper-admin">
                                                <button type="button" class="icon-btn-admin" id="emojiAdminBtn" title="Pilih Emoji">
                                                    <i class="far fa-smile"></i>
                                                </button>
                                                <div id="emojiMartAdmin" class="emoji-mart-container"></div>
                                            </div>
                                            
                                            <label class="icon-btn-admin file-input-label-admin" title="Lampirkan gambar">
                                                <i class="fas fa-paperclip"></i>
                                                <input type="file" id="fileInputAdmin" accept="image/*" onchange="handleFileSelectAdmin(event)">
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <button type="button" onclick="sendAdminMessage(event)" class="btn-send btn-primary">
                                        <i class="fas fa-paper-plane"></i> Kirim
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="no-ticket-selected">
                            <i class="fas fa-headset fa-5x"></i>
                            <h2>Pilih Ticket</h2>
                            <p>Silakan pilih salah satu tiket dari daftar di sebelah kiri untuk mulai membaca atau membalas pesan.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>
    <script>
        // --- Variabel Global & Setup ---
        let selectedFileAdmin = null;
        let emojiPickerOpenAdmin = false;
        const ticketIdAdmin = <?php echo $ticketId ?? 'null'; ?>;
        const ticketNumberAdmin = '<?php echo htmlspecialchars($selectedTicket['ticket_number'] ?? ''); ?>';
        let typingTimeoutAdmin;
        let messageRefreshIntervalAdmin;
        let adminViewingIntervalAdmin;
        const chatMessagesArea = document.getElementById('chatMessagesArea');
        const adminTextarea = document.getElementById('adminMessageInput');
        let isScrolledToBottom = true;


        // Fungsi untuk scroll ke bawah secara otomatis
        function scrollToBottom() {
            if (chatMessagesArea) {
                chatMessagesArea.scrollTop = chatMessagesArea.scrollHeight;
            }
        }
        
        // Cek apakah user sedang di paling bawah (untuk menghindari auto-scroll jika user sedang membaca pesan lama)
        if (chatMessagesArea) {
            chatMessagesArea.addEventListener('scroll', () => {
                const threshold = 100; 
                isScrolledToBottom = (chatMessagesArea.scrollHeight - chatMessagesArea.clientHeight) <= (chatMessagesArea.scrollTop + threshold);
            });
        }


        // Setup Auto-Resize Textarea
        if (adminTextarea) {
            adminTextarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 100) + 'px'; // Max 4 baris (sekitar 100px)
            });
            // Handle Enter key for sending message
            adminTextarea.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendAdminMessage(e);
                }
            });
        }
        
        // --- Initialization ---
        document.addEventListener('DOMContentLoaded', () => {
            if (ticketIdAdmin) {
                initEmojiPickerAdmin();
                
                // Track viewing dan keep-alive
                trackAdminViewing(true);
                adminViewingIntervalAdmin = setInterval(() => {
                    trackAdminViewing(true);
                }, 10000);
                
                // Load messages awal dan polling
                loadMessagesAdmin();
                messageRefreshIntervalAdmin = setInterval(loadMessagesAdmin, 2500); // Polling setiap 2.5 detik
                startTypingIndicatorAdmin(); // Start cek status mengetik customer
                
                // Typing status sender
                adminTextarea?.addEventListener('input', () => {
                    sendTypingStatusAdmin(true);
                    clearTimeout(typingTimeoutAdmin);
                    typingTimeoutAdmin = setTimeout(() => {
                        sendTypingStatusAdmin(false);
                    }, 3000);
                });
                
                // Scroll ke bawah saat pertama kali dibuka
                setTimeout(scrollToBottom, 300);
            }
        });

        // --- AJAX: Track Admin Viewing Status ---
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

        // --- AJAX: Kirim Status Mengetik ---
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
            }).catch(e => console.error('Error sending typing status:', e));
        }

        // --- AJAX: Ambil Status Mengetik Customer ---
        function startTypingIndicatorAdmin() {
            if (!ticketNumberAdmin) return;
            
            setInterval(() => {
                fetch(`../../src/api/typing-status.php?ticket_number=${ticketNumberAdmin}`)
                .then(r => r.json())
                .then(data => {
                    const typingContainer = document.getElementById('typingIndicatorAdmin');
                    if (!typingContainer) return;
                    
                    if (data.success && data.data && data.data.is_typing && data.data.sender_type === 'customer') {
                        // Tampilkan indikator mengetik
                        if (!typingContainer.innerHTML) {
                            typingContainer.innerHTML = `
                                <div class="chat-message customer typing">
                                    <div class="chat-bubble">
                                        <div class="typing-indicator">
                                            <div class="typing-dot"></div>
                                            <div class="typing-dot"></div>
                                            <div class="typing-dot"></div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            if (isScrolledToBottom) scrollToBottom(); // Auto-scroll jika sudah di bawah
                        }
                    } else {
                        // Sembunyikan indikator
                        typingContainer.innerHTML = '';
                    }
                })
                .catch(e => console.error('Error fetching typing status:', e));
            }, 2000);
        }
        
        // --- AJAX: Load Messages ---
        function loadMessagesAdmin() {
            if (!ticketNumberAdmin) return;
            
            fetch(`../../src/api/get-messages.php?ticket_number=${ticketNumberAdmin}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data) {
                    displayMessagesAdmin(data.data.messages);
                }
            })
            .catch(e => console.error('Error loading messages:', e));
        }

        function displayMessagesAdmin(messages) {
            const messagesArea = document.querySelector('.chat-messages');
            if (!messagesArea) return;
            
            // Cek apakah ada pesan baru
            const currentMessageCount = messagesArea.querySelectorAll('.chat-message:not(.typing)').length;
            if (currentMessageCount === messages.length) {
                return; // Tidak ada pesan baru
            }
            
            const typingIndicatorHtml = document.getElementById('typingIndicatorAdmin')?.outerHTML || '<div id="typingIndicatorAdmin"></div>';
            
            messagesArea.innerHTML = '';
            
            if (messages.length === 0) {
                messagesArea.innerHTML = '<div class="no-messages">Belum ada pesan dalam tiket ini.</div>';
                messagesArea.appendChild(document.createElement('div')).outerHTML = typingIndicatorHtml;
                return;
            }
            
            let newMessagesHtml = '';
            messages.forEach(msg => {
                const senderName = msg.sender_type === 'admin' ? 'Anda' : msg.sender_name;
                
                let readStatusHtml = '';
                if (msg.sender_type === 'admin') {
                    // Ceklis ganda (Dibaca)
                    readStatusHtml = `<span class="read-status">${msg.is_read ? '<i class="fas fa-check-double read"></i>' : '<i class="fas fa-check"></i>'}</span>`;
                }

                let attachmentHtml = '';
                if (msg.attachment_url) {
                    attachmentHtml = `
                        <div class="attachment-preview">
                            <img src="../../${msg.attachment_url}" class="chat-message-attachment" onclick="viewImage('../../${msg.attachment_url}')">
                            <small class="attachment-link" onclick="viewImage('../../${msg.attachment_url}')">Lihat Lampiran</small>
                        </div>
                    `;
                }
                
                const timeStr = new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                
                newMessagesHtml += `
                    <div class="chat-message ${msg.sender_type}">
                        <div class="chat-bubble">
                            <div class="chat-message-sender">
                                **${senderName}**
                            </div>
                            <div class="chat-message-content">
                                ${msg.message.replace(/\n/g, '<br>')}
                                ${attachmentHtml}
                            </div>
                            <div class="chat-message-time-status">
                                <span class="chat-message-time">${timeStr}</span>
                                ${readStatusHtml}
                            </div>
                        </div>
                    </div>
                `;
            });

            messagesArea.innerHTML = newMessagesHtml;
            
            // Re-append typing indicator
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = typingIndicatorHtml;
            messagesArea.appendChild(tempDiv.firstChild);

            // Scroll ke bawah jika sebelumnya user berada di bawah
            if (isScrolledToBottom) {
                scrollToBottom();
            }
        }
        
        // --- Form/Send Message ---
        function handleFileSelectAdmin(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                Swal.fire({ icon: 'error', title: 'File Tidak Valid', text: 'Hanya file gambar yang diizinkan' });
                event.target.value = '';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({ icon: 'error', title: 'File Terlalu Besar', text: 'Ukuran file maksimal 5MB' });
                event.target.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = e => {
                selectedFileAdmin = file;
                document.getElementById('previewImageAdmin').src = e.target.result;
                document.getElementById('fileNameAdmin').textContent = file.name;
                document.getElementById('previewAreaAdmin').classList.add('show');
                adminTextarea.focus();
            };
            reader.readAsDataURL(file);
        }

        function removeFileAdmin() {
            selectedFileAdmin = null;
            document.getElementById('fileInputAdmin').value = '';
            document.getElementById('previewImageAdmin').src = '';
            document.getElementById('fileNameAdmin').textContent = '';
            document.getElementById('previewAreaAdmin').classList.remove('show');
        }

        function sendAdminMessage(event) {
            event.preventDefault();
            
            const message = adminTextarea.value.trim();
            if (!message && !selectedFileAdmin) {
                Swal.fire({ icon: 'warning', title: 'Pesan Kosong', text: 'Silakan ketik pesan atau lampirkan gambar' });
                return;
            }

            const btn = event.target.closest('button') || document.querySelector('.btn-send');
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
                    adminTextarea.style.height = 'auto'; // Reset height
                    removeFileAdmin();
                    btn.disabled = false;
                    
                    // Stop typing status immediately
                    sendTypingStatusAdmin(false); 
                    clearTimeout(typingTimeoutAdmin);
                    
                    loadMessagesAdmin(); // Update UI
                    // Swal.fire({ icon: 'success', title: 'Pesan Terkirim', showConfirmButton: false, timer: 1000 }); // Dihilangkan agar tidak mengganggu
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
        
        // --- Status Management ---
        window.updateTicketStatus = function(ticketId, status) {
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
                            Swal.fire({ icon: 'success', title: 'Status Diubah', text: `Status ticket telah diubah menjadi "${statusLabel}"`, timer: 1500 });
                            
                            // Update status button & refresh page to update list (simple method)
                            setTimeout(() => window.location.reload(), 1500);
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
        
        // --- Emoji Picker Setup ---
        function initEmojiPickerAdmin() {
            const emojiBtnAdmin = document.getElementById('emojiAdminBtn');
            const emojiMartAdmin = document.getElementById('emojiMartAdmin');
            if (!emojiBtnAdmin || !emojiMartAdmin) return;

            emojiBtnAdmin.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Stop propagation from affecting document click listener
                emojiPickerOpenAdmin = !emojiPickerOpenAdmin;
                
                if (emojiPickerOpenAdmin) {
                    emojiMartAdmin.classList.add('show');
                    if (emojiMartAdmin.children.length === 0) {
                        try {
                            new EmojiMart.Picker({
                                onEmojiSelect: (emoji) => {
                                    adminTextarea.value += emoji.native;
                                    adminTextarea.focus();
                                    adminTextarea.dispatchEvent(new Event('input'));
                                    emojiPickerOpenAdmin = false;
                                    emojiMartAdmin.classList.remove('show');
                                },
                                theme: 'light',
                                set: 'native',
                                previewPosition: 'none',
                                perLine: 8,
                                I18N: { search: 'Cari', categories: { recent: 'Sering digunakan', smileys: 'Senyum & Emosi', people: 'Orang & Tubuh', animals: 'Hewan & Alam', food: 'Makanan & Minuman', activities: 'Aktivitas', travel: 'Perjalanan & Tempat', objects: 'Objek', symbols: 'Simbol', flags: 'Bendera', custom: 'Kustom' } }
                            }).then(picker => emojiMartAdmin.appendChild(picker)).catch(e => console.error('Emoji error:', e));
                        } catch (error) {
                            console.error('Emoji error:', error);
                        }
                    }
                } else {
                    emojiMartAdmin.classList.remove('show');
                }
            });
            
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.emoji-picker-wrapper-admin') && emojiPickerOpenAdmin) {
                    emojiPickerOpenAdmin = false;
                    emojiMartAdmin.classList.remove('show');
                }
            });
        }
        
        // --- Image Viewer & Filter ---
        window.viewImage = function(url) {
            Swal.fire({
                imageUrl: url,
                imageAlt: 'Lampiran Tiket',
                showConfirmButton: false,
                backdrop: true,
                padding: '0',
                customClass: {
                    image: 'swal-attachment-image'
                }
            });
        }

        window.filterTickets = function() {
            const searchInput = document.getElementById('ticketSearch').value.toLowerCase();
            const ticketItems = document.querySelectorAll('.ticket-item');

            ticketItems.forEach(item => {
                const searchText = item.getAttribute('data-search').toLowerCase();
                if (searchText.includes(searchInput)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        
        // --- Unload Handler ---
        window.addEventListener('beforeunload', () => {
            // Hentikan polling dan kirim status non-viewing
            if (ticketNumberAdmin) {
                // Gunakan navigator.sendBeacon untuk memastikan request terkirim meski page unload
                navigator.sendBeacon('../../src/api/admin-viewing.php', JSON.stringify({
                    ticket_number: ticketNumberAdmin,
                    is_viewing: false
                }));
            }
            
            if (messageRefreshIntervalAdmin) clearInterval(messageRefreshIntervalAdmin);
            if (adminViewingIntervalAdmin) clearInterval(adminViewingIntervalAdmin);
            sendTypingStatusAdmin(false); // Kirim status berhenti mengetik terakhir
        });
    </script>
</body>
</html>