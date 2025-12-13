<?php
/**
 * Admin - FAQs Management (Improved)
 * Helpdesk MTsN 11 Majalengka
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/session.php';
require_once __DIR__ . '/../middleware/csrf.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/validator.php';

requireAdminLogin();

$message = '';
$messageType = '';

// Get all FAQs with error handling
$faqsResult = $conn->query("SELECT * FROM faqs ORDER BY created_at DESC");
$faqs = $faqsResult ? $faqsResult->fetch_all(MYSQLI_ASSOC) : [];

// Handle add FAQ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    // Verify CSRF
    if (!verifyCsrfRequest()) {
        $message = 'Token CSRF tidak valid';
        $messageType = 'error';
    } else {
        // Validate input
        $validator = new Validator($_POST);
        $validator
            ->required('question', 'Pertanyaan harus diisi')
            ->min('question', 5, 'Pertanyaan minimal 5 karakter')
            ->required('answer', 'Jawaban harus diisi')
            ->min('answer', 10, 'Jawaban minimal 10 karakter');

        if (!$validator->isValid()) {
            $message = 'Data tidak valid: ' . implode(', ', $validator->errors());
            $messageType = 'error';
        } else {
            $data = $validator->getData();
            $query = "INSERT INTO faqs (question, answer, category) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            
            if ($stmt) {
                $category = !empty($_POST['category']) ? sanitizeInput($_POST['category']) : '';
                $stmt->bind_param("sss", $data['question'], $data['answer'], $category);
                
                if ($stmt->execute()) {
                    $message = 'FAQ berhasil ditambahkan';
                    $messageType = 'success';
                    // Refresh FAQs
                    $faqsResult = $conn->query("SELECT * FROM faqs ORDER BY created_at DESC");
                    $faqs = $faqsResult ? $faqsResult->fetch_all(MYSQLI_ASSOC) : [];
                } else {
                    $message = 'Gagal menambahkan FAQ';
                    $messageType = 'error';
                }
                $stmt->close();
            }
        }
    }
}

// Handle delete FAQ
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id > 0) {
        $deleteQuery = "DELETE FROM faqs WHERE id = ?";
        $stmt = $conn->prepare($deleteQuery);
        
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = 'FAQ berhasil dihapus';
                $messageType = 'success';
                $faqsResult = $conn->query("SELECT * FROM faqs ORDER BY created_at DESC");
                $faqs = $faqsResult ? $faqsResult->fetch_all(MYSQLI_ASSOC) : [];
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola FAQ - Helpdesk MTsN 11 Majalengka</title>
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .faq-container {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 20px;
        }

        .faq-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .faq-item {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 16px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .faq-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-color: #667eea;
        }

        .faq-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            gap: 12px;
        }

        .faq-item-question {
            font-size: 15px;
            font-weight: 600;
            color: #333;
            flex: 1;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .faq-item-question i {
            color: #667eea;
        }

        .faq-item-category {
            background: #e3f2fd;
            color: #667eea;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .faq-item-answer {
            color: #666;
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 12px;
        }

        .faq-item-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid #f0f0f0;
        }

        .faq-date {
            font-size: 11px;
            color: #999;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .faq-item-actions {
            display: flex;
            gap: 8px;
        }

        .btn-edit,
        .btn-delete {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 4px;
            text-decoration: none;
        }

        .btn-edit {
            background: #e3f2fd;
            color: #667eea;
        }

        .btn-edit:hover {
            background: #bbdefb;
        }

        .btn-delete {
            background: #ffebee;
            color: #f44336;
        }

        .btn-delete:hover {
            background: #ffcdd2;
        }

        .no-faqs {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .no-faqs i {
            font-size: 48px;
            margin-bottom: 12px;
            display: block;
        }

        .faq-form-panel {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .faq-form-panel h3 {
            margin-top: 0;
            margin-bottom: 16px;
            font-size: 16px;
            color: #333;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 12px;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 16px;
            font-size: 13px;
        }

        .alert-success {
            background: #c8e6c9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }

        .alert-error {
            background: #ffcdd2;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        @media (max-width: 768px) {
            .faq-container {
                grid-template-columns: 1fr;
            }

            .faq-form-panel {
                position: static;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-logo">
                <h2><i class="fas fa-headset"></i> Helpdesk</h2>
                <div class="sidebar-subtitle">MTsN 11 Majalengka</div>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <span><i class="fas fa-tachometer-alt"></i> Dashboard</span>
                </a>
                <a href="manage-tickets.php" class="nav-item">
                    <span><i class="fas fa-ticket-alt"></i> Kelola Tickets</span>
                </a>
                <a href="faqs.php" class="nav-item active">
                    <span><i class="fas fa-question-circle"></i> FAQ</span>
                </a>
                <a href="../../logout.php" class="nav-item logout">
                    <span><i class="fas fa-sign-out-alt"></i> Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <!-- Header -->
            <div class="page-header">
                <h1><i class="fas fa-question-circle"></i> Kelola FAQ <span class="admin-label"><?php echo $_SESSION['admin_username']; ?></span></h1>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- FAQ Container -->
            <div class="faq-container">
                
                <!-- FAQs List -->
                <div class="faq-list">
                    <?php if (!empty($faqs)): ?>
                        <?php foreach ($faqs as $faq): ?>
                            <div class="faq-item">
                                <div class="faq-item-header">
                                    <div class="faq-item-question">
                                        <i class="fas fa-question-circle"></i>
                                        <?php echo htmlspecialchars(substr($faq['question'], 0, 50)); ?>
                                    </div>
                                    <?php if (!empty($faq['category'])): ?>
                                        <span class="faq-item-category">
                                            <i class="fas fa-tag"></i>
                                            <?php echo htmlspecialchars($faq['category']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="faq-item-answer">
                                    <?php echo htmlspecialchars(substr($faq['answer'], 0, 150)) . (strlen($faq['answer']) > 150 ? '...' : ''); ?>
                                </div>

                                <div class="faq-item-meta">
                                    <span class="faq-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?php echo date('d M Y', strtotime($faq['created_at'])); ?>
                                    </span>
                                    <div class="faq-item-actions">
                                        <button class="btn-edit" onclick="editFAQ(<?php echo $faq['id']; ?>)" title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn-delete" onclick="deleteFAQ(<?php echo $faq['id']; ?>)" title="Hapus">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-faqs">
                            <i class="fas fa-inbox"></i>
                            <p>Belum ada FAQ</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Add FAQ Form -->
                <div class="faq-form-panel">
                    <h3><i class="fas fa-plus"></i> Tambah FAQ</h3>
                    <form method="POST">
                        <?php echo getCsrfTokenField(); ?>
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group">
                            <label for="question">Pertanyaan *</label>
                            <input type="text" id="question" name="question" placeholder="Ketik pertanyaan..." required>
                        </div>

                        <div class="form-group">
                            <label for="category">Kategori</label>
                            <input type="text" id="category" name="category" placeholder="Contoh: Teknis">
                        </div>

                        <div class="form-group">
                            <label for="answer">Jawaban *</label>
                            <textarea id="answer" name="answer" placeholder="Ketik jawaban..." required></textarea>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Simpan FAQ
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        function deleteFAQ(id) {
            Swal.fire({
                title: 'Hapus FAQ',
                text: 'Apakah Anda yakin ingin menghapus FAQ ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) {
                    window.location.href = `?delete=${id}`;
                }
            });
        }

        function editFAQ(id) {
            Swal.fire({
                title: 'Edit FAQ',
                text: 'Fitur edit akan segera hadir. Anda bisa hapus dan buat FAQ baru.',
                icon: 'info',
                confirmButtonText: 'OK'
            });
        }

        // Auto-resize textarea
        const textarea = document.getElementById('answer');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 200) + 'px';
            });
        }
    </script>
</body>
</html>
