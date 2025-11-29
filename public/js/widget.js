/**
 * Floating Button Widget
 * Helpdesk MTsN 11 Majalengka
 */

(function() {
    'use strict';

    const HELPDESK_CONFIG = {
        apiBase: '/helpdesk/src/api/',
        buttonId: 'helpdesk-floating-btn',
        chatWindowId: 'helpdesk-chat-window',
        storageKey: 'helpdesk_ticket_number'
    };

    class HelpdeskWidget {
        constructor() {
            this.ticketNumber = localStorage.getItem(HELPDESK_CONFIG.storageKey);
            this.init();
        }

        init() {
            this.injectStyles();
            this.createFloatingButton();
            this.attachEventListeners();
        }

        injectStyles() {
            if (document.getElementById('helpdesk-widget-styles')) return;
            
            const link = document.createElement('link');
            link.id = 'helpdesk-widget-styles';
            link.rel = 'stylesheet';
            link.href = '/helpdesk/public/css/widget.css';
            document.head.appendChild(link);
        }

        createFloatingButton() {
            const container = document.createElement('div');
            container.id = HELPDESK_CONFIG.buttonId;
            container.className = 'helpdesk-floating-button';
            container.innerHTML = `
                <button class="helpdesk-btn-main" title="Buka Helpdesk">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </button>
                <div class="helpdesk-menu ${this.ticketNumber ? 'has-ticket' : ''}">
                    <button class="helpdesk-menu-item" data-action="new-ticket">
                        <span>Ticket Baru</span>
                    </button>
                    ${this.ticketNumber ? `
                        <button class="helpdesk-menu-item" data-action="continue-ticket">
                            <span>Lanjutkan Chat (${this.ticketNumber})</span>
                        </button>
                    ` : ''}
                </div>
            `;

            document.body.appendChild(container);
        }

        attachEventListeners() {
            const mainBtn = document.querySelector('.helpdesk-btn-main');
            const menuItems = document.querySelectorAll('[data-action]');

            mainBtn.addEventListener('click', () => {
                const menu = document.querySelector('.helpdesk-menu');
                menu.classList.toggle('show');
            });

            menuItems.forEach(item => {
                item.addEventListener('click', () => {
                    const action = item.getAttribute('data-action');
                    if (action === 'new-ticket') {
                        this.openNewTicketForm();
                    } else if (action === 'continue-ticket') {
                        this.openContinueTicketForm();
                    }
                });
            });

            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('#' + HELPDESK_CONFIG.buttonId)) {
                    document.querySelector('.helpdesk-menu')?.classList.remove('show');
                }
            });
        }

        openNewTicketForm() {
            const modal = document.createElement('div');
            modal.id = 'helpdesk-new-ticket-modal';
            modal.className = 'helpdesk-modal';
            modal.innerHTML = `
                <div class="helpdesk-modal-content">
                    <div class="helpdesk-modal-header">
                        <h3>Buat Ticket Baru</h3>
                        <button class="helpdesk-close-btn">&times;</button>
                    </div>
                    <form id="newTicketForm" class="helpdesk-form">
                        <div class="form-group">
                            <label for="name">Nama Anda</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">No. Telepon</label>
                            <input type="tel" id="phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="subject">Subjek</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Pesan</label>
                            <textarea id="message" name="message" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="helpdesk-btn-submit">Buat Ticket</button>
                    </form>
                </div>
            `;

            document.body.appendChild(modal);

            // Close button
            modal.querySelector('.helpdesk-close-btn').addEventListener('click', () => {
                modal.remove();
            });

            // Form submit
            document.getElementById('newTicketForm').addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitNewTicket();
            });

            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.remove();
            });
        }

        openContinueTicketForm() {
            const modal = document.createElement('div');
            modal.id = 'helpdesk-continue-modal';
            modal.className = 'helpdesk-modal';
            modal.innerHTML = `
                <div class="helpdesk-modal-content">
                    <div class="helpdesk-modal-header">
                        <h3>Lanjutkan Chat</h3>
                        <button class="helpdesk-close-btn">&times;</button>
                    </div>
                    <form id="continueForm" class="helpdesk-form">
                        <p class="helpdesk-info">Masukkan nomor ticket Anda untuk melanjutkan chat</p>
                        <div class="form-group">
                            <label for="ticketNumber">Nomor Ticket</label>
                            <input type="text" id="ticketNumber" name="ticketNumber" 
                                   placeholder="Contoh: TK-20251129-00001" required>
                        </div>
                        <button type="submit" class="helpdesk-btn-submit">Lanjutkan</button>
                    </form>
                </div>
            `;

            document.body.appendChild(modal);

            modal.querySelector('.helpdesk-close-btn').addEventListener('click', () => {
                modal.remove();
            });

            document.getElementById('continueForm').addEventListener('submit', (e) => {
                e.preventDefault();
                const ticketNumber = document.getElementById('ticketNumber').value;
                this.openChatWindow(ticketNumber);
                modal.remove();
            });

            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.remove();
            });
        }

        submitNewTicket() {
            const formData = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                subject: document.getElementById('subject').value,
                message: document.getElementById('message').value
            };

            fetch(HELPDESK_CONFIG.apiBase + 'create-ticket.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.ticketNumber = data.data.ticket_number;
                    localStorage.setItem(HELPDESK_CONFIG.storageKey, this.ticketNumber);
                    
                    // Close modal and open chat
                    document.getElementById('helpdesk-new-ticket-modal').remove();
                    this.openChatWindow(this.ticketNumber);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Terjadi kesalahan saat membuat ticket');
            });
        }

        openChatWindow(ticketNumber) {
            if (document.getElementById(HELPDESK_CONFIG.chatWindowId)) {
                document.getElementById(HELPDESK_CONFIG.chatWindowId).remove();
            }

            const chatWindow = document.createElement('div');
            chatWindow.id = HELPDESK_CONFIG.chatWindowId;
            chatWindow.className = 'helpdesk-chat-window';
            chatWindow.innerHTML = `
                <div class="helpdesk-chat-header">
                    <div class="helpdesk-chat-title">
                        <h4>Helpdesk Support</h4>
                        <span class="helpdesk-ticket-badge">${ticketNumber}</span>
                    </div>
                    <button class="helpdesk-chat-close">&times;</button>
                </div>
                <div class="helpdesk-chat-messages" id="chatMessages"></div>
                <div class="helpdesk-chat-input-area">
                    <textarea id="messageInput" placeholder="Ketik pesan..." rows="3"></textarea>
                    <button id="sendBtn" class="helpdesk-btn-send">Kirim</button>
                </div>
            `;

            document.body.appendChild(chatWindow);

            // Close button
            chatWindow.querySelector('.helpdesk-chat-close').addEventListener('click', () => {
                chatWindow.remove();
            });

            // Send message
            document.getElementById('sendBtn').addEventListener('click', () => {
                this.sendMessage(ticketNumber);
            });

            // Send on Ctrl+Enter
            document.getElementById('messageInput').addEventListener('keydown', (e) => {
                if (e.ctrlKey && e.key === 'Enter') {
                    this.sendMessage(ticketNumber);
                }
            });

            // Load messages
            this.loadMessages(ticketNumber);
            
            // Refresh messages every 3 seconds
            setInterval(() => this.loadMessages(ticketNumber), 3000);
        }

        loadMessages(ticketNumber) {
            fetch(HELPDESK_CONFIG.apiBase + 'get-messages.php?ticket_number=' + encodeURIComponent(ticketNumber))
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.displayMessages(data.data.messages);
                    }
                })
                .catch(err => console.error('Error loading messages:', err));
        }

        displayMessages(messages) {
            const container = document.getElementById('chatMessages');
            if (!container) return;

            container.innerHTML = messages.map(msg => `
                <div class="helpdesk-message ${msg.sender_type}">
                    <div class="helpdesk-message-sender">${msg.sender_name}</div>
                    <div class="helpdesk-message-content">${this.escapeHtml(msg.message)}</div>
                    <div class="helpdesk-message-time">${this.formatTime(msg.created_at)}</div>
                </div>
            `).join('');

            // Scroll to bottom
            container.scrollTop = container.scrollHeight;
        }

        sendMessage(ticketNumber) {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();

            if (!message) return;

            const sendBtn = document.getElementById('sendBtn');
            sendBtn.disabled = true;

            fetch(HELPDESK_CONFIG.apiBase + 'send-message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ticket_number: ticketNumber,
                    message: message,
                    sender_type: 'customer'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    this.loadMessages(ticketNumber);
                } else {
                    alert('Error: ' + data.message);
                }
                sendBtn.disabled = false;
            })
            .catch(err => {
                console.error('Error:', err);
                sendBtn.disabled = false;
            });
        }

        escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        formatTime(datetime) {
            const date = new Date(datetime);
            return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new HelpdeskWidget();
        });
    } else {
        new HelpdeskWidget();
    }
})();
