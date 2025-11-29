<?php
/**
 * Admin - FAQs Management
 * Helpdesk MTsN 11 Majalengka
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
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
                <a href="manage-tickets.php" class="nav-item">
                    <span>🎫 Kelola Tickets</span>
                </a>
                <a href="faqs.php" class="nav-item active">
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
                <h1>Kelola FAQ</h1>
                <div class="admin-user">
                    <span><?php echo $_SESSION['admin_username']; ?></span>
                </div>
            </header>

            <!-- FAQ Container -->
            <div class="faq-container">
                
                <!-- FAQs List -->
                <div class="faq-list">
                    <?php if (count($faqs) > 0): ?>
                        <?php foreach ($faqs as $faq): ?>
                            <div class="faq-item">
                                <div class="faq-item-question"><?php echo $faq['question']; ?></div>

                                <?php if ($faq['category']): ?>
                                    <div class="faq-item-category"><?php echo $faq['category']; ?></div>
                                <?php endif; ?>

                                <div class="faq-item-answer">
                                    <?php echo substr($faq['answer'], 0, 150) . '...'; ?>
                                </div>

                                <div class="faq-item-actions">
                                    <a href="?delete=<?php echo $faq['id']; ?>" 
                                       class="btn-delete" 
                                       onclick="return confirm('Hapus FAQ ini?')">
                                        Hapus
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-faqs">Belum ada FAQ</div>
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
</body>
</html>