<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Helpdesk MTsN 11 Majalengka</title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            text-align: center;
        }
        .hero h1 {
            margin: 0 0 20px 0;
            font-size: 3em;
            font-weight: bold;
        }
        .hero p {
            margin: 0 0 10px 0;
            font-size: 1.2em;
            opacity: 0.95;
        }
        .content {
            padding: 60px 40px;
        }
        .faq-section h2 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .faq-item {
            margin-bottom: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .faq-item:hover {
            background: #e8ecf1;
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.2);
        }
        .faq-item h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 1.1em;
        }
        .faq-item p {
            margin: 0;
            color: #666;
            line-height: 1.6;
            display: none;
        }
        .faq-item.active p {
            display: block;
        }
        .buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 15px 40px;
            font-size: 1.1em;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        .btn-secondary:hover {
            background: #f0f0f0;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        .feature-card {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        .feature-card h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
        .feature-card p {
            color: #666;
            line-height: 1.6;
        }
        .icon {
            font-size: 2.5em;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero">
            <h1>📞 Helpdesk Support</h1>
            <p>MTsN 11 Majalengka</p>
            <p>Layanan support online untuk semua sivitas akademika</p>
        </div>

        <div class="content">
            <!-- Features Section -->
            <div class="features">
                <div class="feature-card">
                    <div class="icon">🎫</div>
                    <h3>Buat Ticket</h3>
                    <p>Laporkan masalah Anda dengan mudah dan terstruktur</p>
                </div>
                <div class="feature-card">
                    <div class="icon">💬</div>
                    <h3>Chat Real-time</h3>
                    <p>Komunikasi langsung dengan tim support kami</p>
                </div>
                <div class="feature-card">
                    <div class="icon">⏱️</div>
                    <h3>Quick Response</h3>
                    <p>Tim support siap membantu dalam jam kerja</p>
                </div>
                <div class="feature-card">
                    <div class="icon">📋</div>
                    <h3>Knowledge Base</h3>
                    <p>Cari jawaban atas pertanyaan umum Anda</p>
                </div>
            </div>

            <!-- Buttons -->
            <div class="buttons">
                <button class="btn btn-primary" onclick="openNewTicketForm()">
                    + Buat Ticket Baru
                </button>
                <button class="btn btn-secondary" onclick="openContinueChat()">
                    🔄 Lanjutkan Chat
                </button>
            </div>

            <!-- FAQ Section -->
            <div class="faq-section">
                <h2>❓ Pertanyaan Umum (FAQ)</h2>
                <div id="faqContainer"></div>
            </div>
        </div>
    </div>

    <!-- Modal New Ticket -->
    <div id="ticketModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeTicketModal()">&times;</span>
            <h2>Buat Ticket Baru</h2>
            <form id="ticketForm">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" required maxlength="255">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Telepon (Optional)</label>
                    <input type="tel" name="phone">
                </div>
                <div class="form-group">
                    <label>Subjek</label>
                    <input type="text" name="subject" required maxlength="255">
                </div>
                <div class="form-group">
                    <label>Pesan</label>
                    <textarea name="message" required rows="5" maxlength="5000"></textarea>
                </div>
                <div class="form-group">
                    <label>Prioritas</label>
                    <select name="priority">
                        <option value="low">Rendah</option>
                        <option value="medium" selected>Sedang</option>
                        <option value="high">Tinggi</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Buat Ticket</button>
            </form>
        </div>
    </div>

    <!-- Modal Continue Chat -->
    <div id="continueModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeContinueModal()">&times;</span>
            <h2>Lanjutkan Chat</h2>
            <form id="continueForm">
                <div class="form-group">
                    <label>Nomor Ticket (e.g., TK-20241213-XXXXX)</label>
                    <input type="text" name="ticket_number" placeholder="TK-" required>
                </div>
                <button type="submit" class="btn btn-primary">Buka Chat</button>
            </form>
        </div>
    </div>

    <!-- Styles for Modal -->
    <style>
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-height: 90vh;
            overflow-y: auto;
        }
        .close {
            color: #999;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
            font-size: 1em;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }
    </style>

    <script>
        // Load FAQs
        function loadFAQs() {
            fetch('src/api/get-faqs.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayFAQs(data.data);
                    }
                })
                .catch(error => console.error('Error loading FAQs:', error));
        }

        function displayFAQs(faqs) {
            const container = document.getElementById('faqContainer');
            container.innerHTML = faqs.map(faq => `
                <div class="faq-item" onclick="toggleFAQ(this)">
                    <h3>${faq.question}</h3>
                    <p>${faq.answer}</p>
                </div>
            `).join('');
        }

        function toggleFAQ(element) {
            element.classList.toggle('active');
        }

        function openNewTicketForm() {
            document.getElementById('ticketModal').style.display = 'flex';
        }

        function closeTicketModal() {
            document.getElementById('ticketModal').style.display = 'none';
        }

        function openContinueChat() {
            document.getElementById('continueModal').style.display = 'flex';
        }

        function closeContinueModal() {
            document.getElementById('continueModal').style.display = 'none';
        }

        // Handle ticket form submission
        document.getElementById('ticketForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('src/api/create-ticket.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    closeTicketModal();
                    // Redirect to chat
                    window.location.href = 'chat.php?ticket_id=' + data.data.ticket_id;
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });

        // Handle continue chat form
        document.getElementById('continueForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const ticketNumber = document.querySelector('input[name="ticket_number"]').value;
            window.location.href = 'chat.php?ticket_number=' + encodeURIComponent(ticketNumber);
        });

        // Load FAQs on page load
        window.addEventListener('load', loadFAQs);

        // Close modals when clicking outside
        window.addEventListener('click', (e) => {
            const ticketModal = document.getElementById('ticketModal');
            const continueModal = document.getElementById('continueModal');
            if (e.target === ticketModal) ticketModal.style.display = 'none';
            if (e.target === continueModal) continueModal.style.display = 'none';
        });
    </script>
</body>
</html>
