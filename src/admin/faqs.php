<?php
/**
 * Admin FAQs - Bootstrap Design
 * Manage frequently asked questions
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

// Get FAQs
$faqs = [];
if ($result = $db->query("SELECT * FROM faqs ORDER BY created_at DESC")) {
    while ($row = $result->fetch_assoc()) {
        $faqs[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ Management - Helpdesk Admin</title>
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

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
            color: white;
            text-decoration: none;
        }

        .faq-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .faq-card {
            background: white;
            border-radius: 10px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border-top: 3px solid var(--primary);
            position: relative;
        }

        .faq-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .faq-card h3 {
            font-size: 1.1em;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .faq-card p {
            font-size: 0.95em;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .faq-actions {
            display: flex;
            gap: 8px;
            border-top: 1px solid #e5e7eb;
            padding-top: 16px;
        }

        .btn-edit, .btn-delete {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.9em;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            text-decoration: none;
        }

        .btn-edit {
            background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%);
            color: white;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: white;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-icon {
            font-size: 3.5em;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #374151;
            margin-bottom: 10px;
        }

        /* Modal Styling */
        .modal-content {
            border: none;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-bottom: 1px solid #e5e7eb;
            border-radius: 10px 10px 0 0;
        }

        .modal-header .modal-title {
            font-weight: 700;
            color: #1f2937;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            padding: 10px 12px;
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

        .btn-modal-save {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-modal-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
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

            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .top-bar h1 {
                font-size: 1.6em;
            }

            .faq-grid {
                grid-template-columns: 1fr;
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
            <a class="nav-link" href="manage-tickets.php">
                <i class="fas fa-ticket-alt me-2"></i> Kelola Tickets
            </a>
            <a class="nav-link active" href="faqs.php">
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
            <h1>❓ FAQ Management</h1>
            <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#faqModal" onclick="resetFaqForm()">
                <i class="fas fa-plus me-2"></i> Tambah FAQ
            </button>
        </div>

        <div id="faqsContainer">
            <?php if (empty($faqs)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3>Belum Ada FAQ</h3>
                    <p>Klik tombol "Tambah FAQ" untuk membuat pertanyaan baru</p>
                </div>
            <?php else: ?>
                <div class="faq-grid">
                    <?php foreach ($faqs as $faq): ?>
                        <div class="faq-card">
                            <h3><?php echo htmlspecialchars($faq['question']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($faq['answer'], 0, 150)); ?>...</p>
                            <div class="faq-actions">
                                <button class="btn-edit" onclick="editFaq(<?php echo $faq['id']; ?>, '<?php echo addslashes(htmlspecialchars($faq['question'])); ?>', '<?php echo addslashes(htmlspecialchars($faq['answer'])); ?>')">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </button>
                                <button class="btn-delete" onclick="deleteFaq(<?php echo $faq['id']; ?>)">
                                    <i class="fas fa-trash me-1"></i> Hapus
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add/Edit FAQ Modal -->
    <div class="modal fade" id="faqModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="question" class="form-label">Pertanyaan</label>
                        <input type="text" class="form-control" id="question" placeholder="Masukkan pertanyaan FAQ">
                    </div>
                    <div class="mb-3">
                        <label for="answer" class="form-label">Jawaban</label>
                        <textarea class="form-control" id="answer" placeholder="Masukkan jawaban FAQ"></textarea>
                    </div>
                    <input type="hidden" id="faqId" value="">
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e5e7eb;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn-modal-save" onclick="saveFaq()">
                        <i class="fas fa-save me-2"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        function resetFaqForm() {
            document.getElementById('faqId').value = '';
            document.getElementById('question').value = '';
            document.getElementById('answer').value = '';
            document.getElementById('modalTitle').textContent = 'Tambah FAQ';
        }

        function editFaq(id, question, answer) {
            document.getElementById('faqId').value = id;
            document.getElementById('question').value = question;
            document.getElementById('answer').value = answer;
            document.getElementById('modalTitle').textContent = 'Edit FAQ';
            new bootstrap.Modal(document.getElementById('faqModal')).show();
        }

        function saveFaq() {
            const id = document.getElementById('faqId').value;
            const question = document.getElementById('question').value.trim();
            const answer = document.getElementById('answer').value.trim();

            if (!question || !answer) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Tidak Lengkap',
                    text: 'Pertanyaan dan jawaban harus diisi!',
                    confirmButtonColor: '#667eea'
                });
                return;
            }

            const formData = new FormData();
            formData.append('id', id);
            formData.append('question', question);
            formData.append('answer', answer);
            formData.append('csrf_token', '<?php echo getCsrfToken(); ?>');

            const endpoint = id ? '../api/update-faq.php' : '../api/create-faq.php';

            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: id ? 'FAQ berhasil diupdate' : 'FAQ berhasil ditambah',
                        confirmButtonColor: '#667eea'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#667eea'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan',
                    confirmButtonColor: '#667eea'
                });
            });
        }

        function deleteFaq(id) {
            Swal.fire({
                title: 'Hapus FAQ?',
                text: 'Tindakan ini tidak dapat dibatalkan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#667eea',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('csrf_token', '<?php echo getCsrfToken(); ?>');

                    fetch('../api/delete-faq.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Dihapus!',
                                text: 'FAQ berhasil dihapus',
                                confirmButtonColor: '#667eea'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message,
                                confirmButtonColor: '#667eea'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan',
                            confirmButtonColor: '#667eea'
                        });
                    });
                }
            });
        }
    </script>
</body>
</html>
