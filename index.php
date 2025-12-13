<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Helpdesk MTsN 11 Majalengka</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fa;
        }

        /* Navbar */
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-size: 1.5em;
            font-weight: 800;
            color: white !important;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: white !important;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 120px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .hero h1 {
            font-size: 3.5em;
            font-weight: 800;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .hero .lead {
            font-size: 1.3em;
            opacity: 0.95;
            position: relative;
            z-index: 1;
            margin-bottom: 40px;
        }

        .hero-buttons {
            position: relative;
            z-index: 1;
        }

        .btn-hero {
            padding: 12px 30px;
            font-weight: 700;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin: 10px;
            font-size: 1.05em;
        }

        .btn-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        /* Features Section */
        .features-section {
            padding: 80px 40px;
            background: white;
        }

        .section-title {
            font-size: 2.5em;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 50px;
            text-align: center;
        }

        .feature-card {
            background: white;
            border-radius: 12px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border-top: 4px solid var(--primary);
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            font-size: 3em;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 1.3em;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 15px;
        }

        .feature-card p {
            color: #6b7280;
            line-height: 1.6;
            margin: 0;
        }

        /* FAQ Section */
        .faq-section {
            padding: 80px 40px;
            background: #f8f9fa;
        }

        .faq-item {
            background: white;
            border-radius: 10px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .faq-header {
            padding: 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary);
        }

        .faq-header:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        }

        .faq-header h3 {
            color: #1f2937;
            font-weight: 700;
            margin: 0;
            font-size: 1.1em;
        }

        .faq-icon {
            color: var(--primary);
            transition: transform 0.3s ease;
        }

        .faq-item.active .faq-icon {
            transform: rotate(180deg);
        }

        .faq-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .faq-item.active .faq-body {
            max-height: 500px;
        }

        .faq-body p {
            padding: 20px;
            color: #6b7280;
            line-height: 1.8;
            margin: 0;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 80px 40px;
            text-align: center;
        }

        .cta-title {
            font-size: 2.5em;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .cta-subtitle {
            font-size: 1.2em;
            opacity: 0.95;
            margin-bottom: 40px;
        }

        /* Footer */
        .footer {
            background: #1f2937;
            color: white;
            padding: 40px;
            text-align: center;
        }

        .footer p {
            margin: 0;
            opacity: 0.8;
        }

        /* Modal */
        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            border-radius: 12px 12px 0 0;
        }

        .modal-header .modal-title {
            font-weight: 800;
            font-size: 1.3em;
        }

        .btn-close-white {
            filter: brightness(0) invert(1);
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            padding: 12px;
            font-size: 0.95em;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
        }

        @media (max-width: 768px) {
            .hero {
                padding: 80px 20px;
            }

            .hero h1 {
                font-size: 2.2em;
            }

            .hero .lead {
                font-size: 1.1em;
            }

            .section-title {
                font-size: 2em;
            }

            .features-section, .faq-section, .cta-section {
                padding: 60px 20px;
            }

            .btn-hero {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-headset me-2"></i> Helpdesk
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon" style="filter: brightness(0) invert(1);"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#faq">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1><i class="fas fa-phone-alt me-3"></i>Helpdesk Support</h1>
            <p class="lead">MTsN 11 Majalengka</p>
            <p class="lead">Layanan support online untuk semua sivitas akademika</p>
            <div class="hero-buttons">
                <button class="btn btn-hero btn-light" data-bs-toggle="modal" data-bs-target="#ticketModal">
                    <i class="fas fa-plus me-2"></i>Buat Ticket Baru
                </button>
                <button class="btn btn-hero btn-outline-light" data-bs-toggle="modal" data-bs-target="#continueModal">
                    <i class="fas fa-comments me-2"></i>Lanjutkan Chat
                </button>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <h2 class="section-title">✨ Fitur Utama</h2>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <h3>Buat Ticket</h3>
                        <p>Laporkan masalah Anda dengan mudah dan terstruktur</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3>Chat Real-time</h3>
                        <p>Komunikasi langsung dengan tim support kami</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3>Quick Response</h3>
                        <p>Tim support siap membantu dalam jam kerja</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3>Knowledge Base</h3>
                        <p>Cari jawaban atas pertanyaan umum Anda</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section" id="faq">
        <div class="container">
            <h2 class="section-title">❓ Pertanyaan Umum (FAQ)</h2>
            <div class="row">
                <div class="col-lg-8 mx-auto" id="faqContainer">
                    <div class="faq-item">
                        <div class="faq-header" onclick="toggleFaq(this)">
                            <h3>Bagaimana cara membuat ticket baru?</h3>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-body">
                            <p>Klik tombol "Buat Ticket Baru" di halaman utama, kemudian isi formulir dengan data lengkap Anda termasuk nama, email, dan deskripsi masalah. Setelah submit, Anda akan mendapatkan nomor ticket yang dapat digunakan untuk melacak status.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-header" onclick="toggleFaq(this)">
                            <h3>Berapa lama waktu respon dari tim support?</h3>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-body">
                            <p>Tim support kami bekerja pada jam kerja 08:00 - 16:00 Waktu Indonesia Barat (WIB). Untuk ticket dengan prioritas tinggi, respons biasanya diberikan dalam 1-2 jam. Untuk prioritas sedang dan rendah, respons diberikan dalam 24 jam kerja.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-header" onclick="toggleFaq(this)">
                            <h3>Apakah bisa melanjutkan chat jika sudah disconnect?</h3>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-body">
                            <p>Ya, Anda dapat melanjutkan chat dengan menggunakan tombol "Lanjutkan Chat" dan memasukkan nomor ticket Anda. Semua riwayat pesan akan tersimpan dan dapat dilihat kembali.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-header" onclick="toggleFaq(this)">
                            <h3>Apa itu prioritas ticket?</h3>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-body">
                            <p>Prioritas menentukan urutan penanganan ticket. Prioritas Tinggi untuk masalah mendesak (sistem down, data hilang), Sedang untuk masalah normal (akses akun, password reset), dan Rendah untuk pertanyaan umum atau request non-urgent.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-header" onclick="toggleFaq(this)">
                            <h3>Bagaimana jika tidak puas dengan solusi yang diberikan?</h3>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-body">
                            <p>Anda dapat memberikan feedback melalui chat atau membuka ticket baru untuk escalation. Tim manager akan review dan follow-up dengan solusi yang lebih baik.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title">Butuh Bantuan?</h2>
            <p class="cta-subtitle">Tim support kami siap membantu Anda 24/7</p>
            <button class="btn btn-hero btn-light" data-bs-toggle="modal" data-bs-target="#ticketModal">
                <i class="fas fa-plus me-2"></i>Buat Ticket Sekarang
            </button>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Helpdesk MTsN 11 Majalengka. All rights reserved.</p>
        </div>
    </footer>

    <!-- Modal New Ticket -->
    <div class="modal fade" id="ticketModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Buat Ticket Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="ticketForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="name" name="name" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Telepon (Optional)</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subjek</label>
                            <input type="text" class="form-control" id="subject" name="subject" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Pesan/Deskripsi Masalah</label>
                            <textarea class="form-control" id="message" name="message" required rows="5" maxlength="5000"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="priority" class="form-label">Prioritas</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="low">🟢 Rendah</option>
                                <option value="medium" selected>🟡 Sedang</option>
                                <option value="high">🔴 Tinggi</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-submit w-100">
                            <i class="fas fa-paper-plane me-2"></i>Buat Ticket
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Continue Chat -->
    <div class="modal fade" id="continueModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-comments me-2"></i>Lanjutkan Chat
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="continueForm">
                        <div class="mb-3">
                            <label for="ticketNumber" class="form-label">Nomor Ticket</label>
                            <input type="text" class="form-control" id="ticketNumber" name="ticket_number" placeholder="TK-20241213-XXXXX" required>
                            <small class="text-muted">Contoh: TK-20241213-ABC123</small>
                        </div>
                        <button type="submit" class="btn-submit w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Buka Chat
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        function toggleFaq(header) {
            const item = header.parentElement;
            const isActive = item.classList.contains('active');
            
            document.querySelectorAll('.faq-item.active').forEach(el => {
                el.classList.remove('active');
            });
            
            if (!isActive) {
                item.classList.add('active');
            }
        }

        document.getElementById('ticketForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('src/api/create-ticket.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Ticket Berhasil Dibuat!',
                        html: `
                            <div style="text-align: left; margin-top: 20px;">
                                <p><strong>Nomor Ticket:</strong></p>
                                <p style="background: #f0f0f0; padding: 10px; border-radius: 6px; font-weight: bold; color: #667eea; font-size: 1.1em;">
                                    ${data.data.ticket_number}
                                </p>
                                <p style="margin-top: 15px; color: #666; font-size: 0.95em;">
                                    Simpan nomor ini untuk melacak status ticket Anda.
                                </p>
                            </div>
                        `,
                        showConfirmButton: true,
                        confirmButtonText: 'Buka Chat',
                        showCancelButton: true,
                        cancelButtonText: 'Tutup',
                        confirmButtonColor: '#667eea'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'chat.php?ticket=' + encodeURIComponent(data.data.ticket_number);
                        } else {
                            document.getElementById('ticketForm').reset();
                            bootstrap.Modal.getInstance(document.getElementById('ticketModal')).hide();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Membuat Ticket',
                        text: data.message || 'Terjadi kesalahan. Silakan coba lagi.',
                        confirmButtonColor: '#667eea'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan',
                    text: 'Terjadi kesalahan saat mengirim data. Silakan coba lagi.',
                    confirmButtonColor: '#667eea'
                });
            });
        });

        document.getElementById('continueForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const ticketNumber = document.getElementById('ticketNumber').value;
            window.location.href = 'chat.php?ticket=' + encodeURIComponent(ticketNumber);
        });
    </script>
</body>
</html>
