<?php
/**
 * Helpdesk MTsN 11 Majalengka - Main Gateway
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Helpdesk - MTsN 11 Majalengka</title>
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container { max-width: 1200px; margin: 0 auto; }
        
        header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
            padding: 40px 20px;
        }
        
        header h1 { font-size: 36px; margin-bottom: 10px; }
        header p { font-size: 16px; opacity: 0.9; }
        
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        
        .card h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 22px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .faq-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }
        
        .faq-section h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 22px;
        }
        
        .faq-categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .category-btn {
            padding: 10px 15px;
            background: #f0f0f0;
            border: 2px solid transparent;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            color: #333;
            transition: all 0.3s ease;
        }
        
        .category-btn:hover,
        .category-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .faq-list {
            display: none;
        }
        
        .faq-list.active {
            display: block;
        }
        
        .faq-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        
        .faq-item:last-child {
            border-bottom: none;
        }
        
        .faq-question {
            color: #333;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            user-select: none;
        }
        
        .faq-question:hover {
            color: #667eea;
        }
        
        .faq-toggle {
            color: #667eea;
            font-weight: bold;
            font-size: 18px;
        }
        
        .faq-answer {
            display: none;
            color: #666;
            margin-top: 10px;
            line-height: 1.6;
        }
        
        .faq-answer.active {
            display: block;
        }
        
        .footer {
            text-align: center;
            color: white;
            margin-top: 40px;
            padding: 20px;
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
            
            header h1 {
                font-size: 24px;
            }
            
            .faq-categories {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>� Helpdesk MTsN 11 Majalengka</h1>
            <p>Sistem Layanan Bantuan Terpadu</p>
        </header>

        <div class="main-grid">
            <!-- Form Bantuan -->
            <div class="card">
                <h2>✉️ Kirim Bantuan</h2>
                <form id="helpForm" onsubmit="submitHelp(event)">
                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" id="name" name="name" required placeholder="Masukkan nama Anda">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required placeholder="Masukkan email Anda">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">No. Telepon</label>
                        <input type="tel" id="phone" name="phone" placeholder="Masukkan nomor telepon (opsional)">
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Kategori Bantuan</label>
                        <select id="category" name="category" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="teknis">Bantuan Teknis</option>
                            <option value="administrasi">Administrasi</option>
                            <option value="akademik">Akademik</option>
                            <option value="siswa">Kesiswaaan</option>
                            <option value="umum">Umum</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Judul Bantuan</label>
                        <input type="text" id="subject" name="subject" required placeholder="Judul singkat masalah Anda">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Deskripsi Masalah</label>
                        <textarea id="message" name="message" required placeholder="Jelaskan masalah yang Anda alami secara detail"></textarea>
                    </div>
                    
                    <button type="submit" class="btn">📤 Kirim Bantuan</button>
                </form>
            </div>

            <!-- Info & Status -->
            <div class="card">
                <h2>📋 Informasi</h2>
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #333; margin-bottom: 10px;">Jam Layanan</h3>
                    <p style="color: #666; line-height: 1.8;">
                        <strong>Senin - Jumat:</strong> 07:00 - 15:30<br>
                        <strong>Sabtu:</strong> 07:00 - 11:30<br>
                        <strong>Minggu & Libur:</strong> Tutup
                    </p>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #333; margin-bottom: 10px;">Status Layanan</h3>
                    <p style="color: #28a745; font-weight: 600;">✅ Layanan Aktif</p>
                </div>
                
                <div style="background: #f0f0f0; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                    <h3 style="color: #333; margin-bottom: 10px; font-size: 14px;">🔍 Pelacakan Ticket</h3>
                    <p style="color: #666; font-size: 13px; margin-bottom: 10px;">Jika Anda sudah memiliki nomor ticket, masukkan di bawah ini untuk melanjutkan chat:</p>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="trackingNumber" placeholder="TK-20251129-XXXXX" style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                        <button type="button" onclick="trackTicket()" style="padding: 8px 15px; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 12px;">Tracking</button>
                    </div>
                </div>

                <div style="text-align: center; padding: 15px; background: #f9f9f9; border-radius: 6px;">
                    <p style="color: #666; font-size: 13px;">Rata-rata waktu respons:</p>
                    <p style="color: #667eea; font-weight: 600; font-size: 18px;">15 menit</p>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="faq-section">
            <h2>❓ Pertanyaan yang Sering Diajukan (FAQ)</h2>
            
            <div class="faq-categories">
                <button class="category-btn active" onclick="showCategory('teknis')">🔧 Teknis</button>
                <button class="category-btn" onclick="showCategory('umum')">📌 Umum</button>
                <button class="category-btn" onclick="showCategory('proses')">⚙️ Proses</button>
                <button class="category-btn" onclick="showCategory('pembayaran')">💳 Pembayaran</button>
                <button class="category-btn" onclick="showCategory('lainnya')">📚 Lainnya</button>
            </div>

            <!-- FAQ Teknis -->
            <div class="faq-list active" id="teknis">
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleAnswer(this)">
                        <span>Bagaimana cara membuat ticket bantuan?</span>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        Isi form di sebelah kiri dengan data lengkap Anda, pilih kategori bantuan, dan jelaskan masalah yang dialami. Setelah submit, Anda akan mendapatkan nomor ticket untuk melacak status bantuan.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleAnswer(this)">
                        <span>Berapa lama waktu tunggu respons?</span>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        Tim support kami biasanya merespons dalam waktu 15 menit hingga 1 jam pada jam kerja. Untuk pertanyaan kompleks mungkin memerlukan waktu lebih lama.
                    </div>
                </div>
            </div>

            <!-- FAQ Umum -->
            <div class="faq-list" id="umum">
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleAnswer(this)">
                        <span>Siapa yang bisa menggunakan helpdesk ini?</span>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        Semua sivitas akademika MTsN 11 Majalengka dapat menggunakan layanan helpdesk ini, termasuk siswa, guru, dan staf administrasi.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleAnswer(this)">
                        <span>Apakah layanan ini gratis?</span>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        Ya, layanan helpdesk ini sepenuhnya gratis untuk semua pengguna yang terdaftar sebagai sivitas akademika MTsN 11 Majalengka.
                    </div>
                </div>
            </div>

            <!-- FAQ Proses -->
            <div class="faq-list" id="proses">
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleAnswer(this)">
                        <span>Bagaimana cara melacak status ticket saya?</span>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        Gunakan nomor ticket yang Anda terima saat membuat bantuan. Masukkan nomor tersebut di form pelacakan untuk melihat status dan percakapan dengan tim support.
                    </div>
                </div>
            </div>

            <!-- FAQ Pembayaran -->
            <div class="faq-list" id="pembayaran">
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleAnswer(this)">
                        <span>Apakah ada biaya tambahan?</span>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        Tidak ada biaya apapun. Semua layanan disediakan gratis untuk mendukung aktivitas akademik di MTsN 11 Majalengka.
                    </div>
                </div>
            </div>

            <!-- FAQ Lainnya -->
            <div class="faq-list" id="lainnya">
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleAnswer(this)">
                        <span>Kemana saya bisa melaporkan masalah teknis di helpdesk?</span>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        Gunakan kategori "Bantuan Teknis" saat membuat ticket. Jelaskan masalah yang Anda alami secara detail agar tim support dapat membantu dengan lebih cepat.
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>© 2025 Helpdesk MTsN 11 Majalengka | Powered by Support System</p>
        </div>
    </div>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <script>
        function submitHelp(event) {
            event.preventDefault();
            const form = event.target;
            
            // Show loading
            Swal.fire({
                title: 'Mengirim Bantuan...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: (modal) => {
                    Swal.showLoading();
                }
            });
            
            const formData = {
                name: form.name.value,
                email: form.email.value,
                phone: form.phone.value,
                subject: form.subject.value,
                message: form.message.value
            };
            
            // Send to create-ticket API
            fetch('src/api/create-ticket.php', {
                method: 'POST',
                body: JSON.stringify(formData),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.ticket_number) {
                    const ticketNumber = data.data.ticket_number;
                    
                    // Show success alert with options
                    Swal.fire({
                        icon: 'success',
                        title: '✅ Bantuan Terkirim!',
                        html: `
                            <div style="text-align: left; margin: 20px 0;">
                                <p style="color: #666; margin-bottom: 15px;">Nomor ticket Anda:</p>
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                                    <input type="text" id="ticketNumberInput" value="${ticketNumber}" readonly style="
                                        flex: 1;
                                        padding: 10px 15px;
                                        background: #f0f0f0;
                                        border: 2px solid #667eea;
                                        border-radius: 6px;
                                        font-weight: 600;
                                        font-size: 14px;
                                        cursor: text;
                                    ">
                                    <button onclick="copyTicketNumber('${ticketNumber}')" style="
                                        padding: 10px 20px;
                                        background: #667eea;
                                        color: white;
                                        border: none;
                                        border-radius: 6px;
                                        cursor: pointer;
                                        font-weight: 600;
                                        transition: all 0.3s;
                                    " onmouseover="this.style.background='#764ba2'" onmouseout="this.style.background='#667eea'">
                                        📋 Salin
                                    </button>
                                </div>
                                <p style="color: #999; font-size: 12px; margin-bottom: 15px;">Simpan nomor ini untuk melacak status bantuan Anda</p>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: '💬 Lanjut ke Chat',
                        cancelButtonText: '✓ Tutup',
                        confirmButtonColor: '#667eea',
                        cancelButtonColor: '#999',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirect to chat with ticket number
                            startChatWithTicket(ticketNumber);
                        }
                        form.reset();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message || 'Gagal mengirim bantuan. Silakan coba lagi.'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan!',
                    text: 'Terjadi kesalahan jaringan. Silakan coba lagi.'
                });
            });
        }

        function copyTicketNumber(ticketNumber) {
            navigator.clipboard.writeText(ticketNumber).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Disalin!',
                    text: 'Nomor ticket sudah disalin ke clipboard',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        }

        function startChatWithTicket(ticketNumber) {
            // Redirect to widget chat or chat page with ticket number
            // This could be a dedicated chat page or using the floating widget
            window.location.href = `index.php?chat=${ticketNumber}`;
        }

        function trackTicket() {
            const ticketNumber = document.getElementById('trackingNumber').value.trim();
            
            if (!ticketNumber) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nomor Ticket Kosong',
                    text: 'Silakan masukkan nomor ticket terlebih dahulu'
                });
                return;
            }
            
            // Validate ticket number format
            if (!ticketNumber.match(/^TK-\d{8}-\d{5}$/)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Format Tidak Valid',
                    text: 'Format nomor ticket: TK-YYYYMMDD-XXXXX'
                });
                return;
            }
            
            // Verify ticket exists
            Swal.fire({
                title: 'Melacak Ticket...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: (modal) => {
                    Swal.showLoading();
                }
            });
            
            fetch(`src/api/get-messages.php?ticket_number=${ticketNumber}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Ticket Ditemukan!',
                        html: `
                            <div style="text-align: left;">
                                <p><strong>Nomor Ticket:</strong> ${data.data.ticket.ticket_number}</p>
                                <p><strong>Subjek:</strong> ${data.data.ticket.subject}</p>
                                <p><strong>Status:</strong> <span style="
                                    display: inline-block;
                                    padding: 5px 10px;
                                    border-radius: 20px;
                                    font-weight: 600;
                                    background: ${getStatusColor(data.data.ticket.status)};
                                    color: white;
                                ">${getStatusLabel(data.data.ticket.status)}</span></p>
                            </div>
                        `,
                        confirmButtonText: '💬 Buka Chat',
                        confirmButtonColor: '#667eea'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            startChatWithTicket(ticketNumber);
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ticket Tidak Ditemukan',
                        text: 'Silakan periksa kembali nomor ticket Anda'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan!',
                    text: 'Terjadi kesalahan saat melacak ticket'
                });
            });
        }

        function getStatusColor(status) {
            const colors = {
                'open': '#FFA500',
                'in_progress': '#2196F3',
                'resolved': '#28a745',
                'closed': '#6c757d'
            };
            return colors[status] || '#999';
        }

        function getStatusLabel(status) {
            const labels = {
                'open': 'Terbuka',
                'in_progress': 'Sedang Diproses',
                'resolved': 'Terselesaikan',
                'closed': 'Ditutup'
            };
            return labels[status] || status;
        }

        function showCategory(category) {
            // Hide all FAQ lists
            document.querySelectorAll('.faq-list').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.category-btn').forEach(el => el.classList.remove('active'));
            
            // Show selected category
            document.getElementById(category).classList.add('active');
            event.target.classList.add('active');
        }

        function toggleAnswer(element) {
            const answer = element.nextElementSibling;
            const toggle = element.querySelector('.faq-toggle');
            
            // Close other answers
            document.querySelectorAll('.faq-answer.active').forEach(el => {
                if (el !== answer) {
                    el.classList.remove('active');
                    el.previousElementSibling.querySelector('.faq-toggle').textContent = '+';
                }
            });
            
            // Toggle current answer
            answer.classList.toggle('active');
            toggle.textContent = answer.classList.contains('active') ? '−' : '+';
        }
    </script>
</body>
</html>
