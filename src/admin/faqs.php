<?php
/**
 * Admin - FAQs Management
 * Helpdesk MTsN 11 Majalengka
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/functions.php';

requireAdminLogin();

// Variabel untuk menampung data FAQ yang sedang diedit
$faqToEdit = null;

// --- Logika Handling Formulir ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $question = sanitizeInput($_POST['question'] ?? '');
    // Gunakan fungsi sanitizeInput jika ini hanya untuk teks biasa. 
    // Jika Anda ingin mengizinkan HTML (Rich Text), Anda harus menggunakan sanitasi yang berbeda.
    $answer = sanitizeInput($_POST['answer'] ?? ''); 
    $category = sanitizeInput($_POST['category'] ?? '');
    
    if (!empty($question) && !empty($answer)) {
        if ($action === 'add') {
            // Tambah FAQ Baru
            $query = "INSERT INTO faqs (question, answer, category) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $question, $answer, $category);
            $stmt->execute();
        } elseif ($action === 'edit' && isset($_POST['id'])) {
            // Edit FAQ yang Sudah Ada
            $id = intval($_POST['id']);
            $query = "UPDATE faqs SET question = ?, answer = ?, category = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $question, $answer, $category, $id);
            $stmt->execute();
        }
        
        // Redirect setelah operasi berhasil
        header("Location: faqs.php");
        exit;
    }
}

// --- Logika Handling Edit/Delete dari URL ---

// 1. Handle Delete FAQ
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Penggunaan prepare statement jauh lebih aman, meskipun untuk DELETE sederhana
    $stmt = $conn->prepare("DELETE FROM faqs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: faqs.php");
    exit;
}

// 2. Handle Load FAQ untuk Edit
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $faqToEdit = $result->fetch_assoc();
}

// --- Logika Pengambilan Data ---

// Get all FAQs
$faqs = $conn->query("SELECT * FROM faqs ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

$adminUsername = $_SESSION['admin_username'] ?? 'Admin Helpdesk';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola FAQ - Helpdesk MTsN 11 Majalengka</title>
    <link rel="stylesheet" href="../../public/css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-app-layout">

        <aside class="main-sidebar">
            <div class="sidebar-logo">
                <h2>🎓 **Helpdesk**</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="manage-tickets.php" class="nav-item"><i class="fas fa-headset"></i> Kelola Tiket</a>
                <a href="manage-users.php" class="nav-item"><i class="fas fa-users"></i> Kelola User</a>
                <a href="faqs.php" class="nav-item active"><i class="fas fa-question-circle"></i> **FAQ**</a>
                <a href="../../logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="admin-content-area dual-panel-layout">
            
            <header class="admin-header">
                <div class="header-left">
                    <h1>**Kelola FAQ & Pengetahuan**</h1>
                    <p class="greeting-message">Tambahkan, edit, atau hapus Pertanyaan yang Sering Diajukan (FAQ).</p>
                </div>
                <div class="admin-user-info">
                    <span class="user-avatar-header"><?php echo strtoupper(substr($adminUsername, 0, 1)); ?></span>
                </div>
            </header>

            <div class="dual-panel-container">
                
                <div class="panel-left faq-list-panel">
                    <h2>Daftar Semua FAQ (<?php echo count($faqs); ?>)</h2>
                    <div class="faq-accordion-container">
                        <?php if (count($faqs) > 0): ?>
                            <?php foreach ($faqs as $faq): ?>
                                <div class="accordion-item">
                                    <div class="accordion-header">
                                        <div class="faq-question-text">
                                            <span class="faq-category-tag"><?php echo !empty($faq['category']) ? htmlspecialchars($faq['category']) : 'Umum'; ?></span>
                                            **<?php echo htmlspecialchars($faq['question']); ?>**
                                        </div>
                                        <div class="faq-actions-icon">
                                            <i class="fas fa-chevron-down toggle-icon"></i>
                                        </div>
                                    </div>
                                    <div class="accordion-content">
                                        <p>**Jawaban:**</p>
                                        <div class="faq-answer-text"><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></div>
                                        <div class="faq-item-actions">
                                            <a href="?edit=<?php echo $faq['id']; ?>" class="btn-small btn-edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="?delete=<?php echo $faq['id']; ?>" 
                                               class="btn-small btn-delete" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus FAQ ini? Tindakan ini tidak dapat dibatalkan.')">
                                                <i class="fas fa-trash-alt"></i> Hapus
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-data">
                                <i class="fas fa-book-open fa-3x"></i>
                                <p>Belum ada FAQ yang tersimpan. Tambahkan yang pertama!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="panel-right faq-form-panel">
                    <h2><i class="fas fa-pencil-alt"></i> 
                        <?php echo $faqToEdit ? 'Edit FAQ #' . $faqToEdit['id'] : 'Tambah FAQ Baru'; ?>
                    </h2>
                    <form method="POST" action="faqs.php">
                        <?php if ($faqToEdit): ?>
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?php echo $faqToEdit['id']; ?>">
                        <?php else: ?>
                            <input type="hidden" name="action" value="add">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="question">Pertanyaan **(Wajib)**</label>
                            <input type="text" id="question" name="question" 
                                   value="<?php echo htmlspecialchars($faqToEdit['question'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="category">Kategori (Opsional)</label>
                            <input type="text" id="category" name="category" 
                                   placeholder="Contoh: Pembayaran, Teknis, Pendaftaran"
                                   value="<?php echo htmlspecialchars($faqToEdit['category'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="answer">Jawaban **(Wajib)**</label>
                            <textarea id="answer" name="answer" rows="8" required><?php echo htmlspecialchars($faqToEdit['answer'] ?? ''); ?></textarea>
                            <small class="form-hint">Gunakan Enter untuk membuat baris baru pada jawaban.</small>
                        </div>

                        <button type="submit" class="btn-submit btn-primary">
                            <i class="fas fa-save"></i> 
                            <?php echo $faqToEdit ? 'Simpan Perubahan' : 'Terbitkan FAQ'; ?>
                        </button>
                        
                        <?php if ($faqToEdit): ?>
                            <a href="faqs.php" class="btn-small btn-cancel"><i class="fas fa-times"></i> Batal Edit</a>
                        <?php endif; ?>
                    </form>
                </div>

            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const accordionHeaders = document.querySelectorAll('.accordion-header');

            accordionHeaders.forEach(header => {
                header.addEventListener('click', () => {
                    const item = header.closest('.accordion-item');
                    const content = item.querySelector('.accordion-content');
                    const icon = item.querySelector('.toggle-icon');

                    // Tutup semua yang lain (opsional, tapi bagus untuk tampilan bersih)
                    document.querySelectorAll('.accordion-item.active').forEach(activeItem => {
                        if (activeItem !== item) {
                            activeItem.classList.remove('active');
                            activeItem.querySelector('.accordion-content').style.maxHeight = null;
                            activeItem.querySelector('.toggle-icon').classList.remove('fa-chevron-up');
                            activeItem.querySelector('.toggle-icon').classList.add('fa-chevron-down');
                        }
                    });

                    // Toggle yang sedang diklik
                    item.classList.toggle('active');
                    if (item.classList.contains('active')) {
                        content.style.maxHeight = content.scrollHeight + "px";
                        icon.classList.remove('fa-chevron-down');
                        icon.classList.add('fa-chevron-up');
                    } else {
                        content.style.maxHeight = null;
                        icon.classList.remove('fa-chevron-up');
                        icon.classList.add('fa-chevron-down');
                    }
                });
            });

            // Otomatis buka form edit jika ada data yang sedang diedit
            <?php if ($faqToEdit): ?>
                // Tambahkan kelas khusus ke body agar form menonjol
                document.body.classList.add('editing-faq');
            <?php endif; ?>
        });
    </script>
</body>
</html>