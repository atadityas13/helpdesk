<?php
/**
 * Admin - FAQs Management
 * Helpdesk MTsN 11 Majalengka
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/session.php';
require_once __DIR__ . '/../middleware/csrf.php';
require_once __DIR__ . '/../helpers/functions.php';

requireAdminLogin();

// Get all FAQs
$faqs = $conn->query("SELECT * FROM faqs ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Handle add FAQ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $question = sanitizeInput($_POST['question'] ?? '');
    $answer = sanitizeInput($_POST['answer'] ?? '');
    $category = sanitizeInput($_POST['category'] ?? '');
    
    if (!empty($question) && !empty($answer)) {
        $query = "INSERT INTO faqs (question, answer, category) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $question, $answer, $category);
        $stmt->execute();
        
        header("Location: faqs.php");
        exit;
    }
}

// Handle delete FAQ
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM faqs WHERE id = {$id}");
    header("Location: faqs.php");
    exit;
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
                <div class="header-actions">
                    <button class="btn-refresh" onclick="refreshFAQs()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- FAQ Container -->
            <div class="faq-container">
                
                <!-- FAQs List -->
                <div class="faq-list">
                    <?php if (count($faqs) > 0): ?>
                        <?php foreach ($faqs as $faq): ?>
                            <div class="faq-item">
                                <div class="faq-item-header">
                                    <div class="faq-item-question">
                                        <i class="fas fa-question-circle"></i>
                                        <?php echo htmlspecialchars($faq['question']); ?>
                                    </div>
                                    <?php if ($faq['category']): ?>
                                        <div class="faq-item-category">
                                            <i class="fas fa-tag"></i>
                                            <?php echo htmlspecialchars($faq['category']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="faq-item-answer">
                                    <?php echo htmlspecialchars(substr($faq['answer'], 0, 200)) . (strlen($faq['answer']) > 200 ? '...' : ''); ?>
                                </div>

                                <div class="faq-item-meta">
                                    <span class="faq-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?php echo date('d M Y', strtotime($faq['created_at'])); ?>
                                    </span>
                                    <div class="faq-item-actions">
                                        <button class="btn-edit" onclick="editFAQ(<?php echo $faq['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="?delete=<?php echo $faq['id']; ?>"
                                           class="btn-delete"
                                           onclick="return confirm('Hapus FAQ ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-faqs">
                            <i class="fas fa-info-circle"></i>
                            <p>Belum ada FAQ</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Add FAQ Form -->
                <div class="faq-form-panel">
                    <h3>Tambah FAQ</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group">
                            <label for="question">Pertanyaan</label>
                            <input type="text" id="question" name="question" required>
                        </div>

                        <div class="form-group">
                            <label for="category">Kategori</label>
                            <input type="text" id="category" name="category" placeholder="Contoh: Teknis">
                        </div>

                        <div class="form-group">
                            <label for="answer">Jawaban</label>
                            <textarea id="answer" name="answer" required></textarea>
                        </div>

                        <button type="submit" class="btn-submit">Tambah FAQ</button>
                    </form>
                </div>

            </div>
        </main>
    </div>

    <script>
        function refreshFAQs() {
            location.reload();
        }

        function clearForm() {
            document.getElementById('faqForm').reset();
        }

        function editFAQ(id) {
            // Placeholder for edit functionality
            alert('Edit functionality will be implemented soon. FAQ ID: ' + id);
        }

        // Auto-resize textarea
        document.getElementById('answer').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 200) + 'px';
        });

        // Form validation
        document.getElementById('faqForm').addEventListener('submit', function(e) {
            const question = document.getElementById('question').value.trim();
            const answer = document.getElementById('answer').value.trim();

            if (question.length < 5) {
                e.preventDefault();
                alert('Pertanyaan harus minimal 5 karakter');
                return;
            }

            if (answer.length < 10) {
                e.preventDefault();
                alert('Jawaban harus minimal 10 karakter');
                return;
            }
        });
    </script>
</body>
</html>
