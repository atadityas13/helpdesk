<?php
/**
 * Admin - FAQs Management
 * Helpdesk MTsN 11 Majalengka
 */

require_once '../../src/config/database.php';
require_once '../../src/middleware/auth.php';
require_once '../../src/helpers/functions.php';

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
    <title>FAQ Management - Helpdesk MTsN 11 Majalengka</title>
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <style>
        .faq-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
            min-height: calc(100vh - 200px);
        }

        .faq-list {
            background: white;
            border-radius: 8px;
            overflow-y: auto;
        }

        .faq-item {
            padding: 16px;
            border-bottom: 1px solid #f0f0f0;
        }

        .faq-item-question {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .faq-item-category {
            display: inline-block;
            background: #f0f0ff;
            color: #667eea;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .faq-item-answer {
            font-size: 13px;
            color: #666;
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .faq-item-actions {
            display: flex;
            gap: 8px;
        }

        .btn-delete {
            padding: 6px 12px;
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s ease;
        }

        .btn-delete:hover {
            background: #fcc;
        }

        .faq-form-panel {
            background: white;
            border-radius: 8px;
            padding: 20px;
            height: fit-content;
        }

        .faq-form-panel h3 {
            margin: 0 0 16px 0;
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
            color: #333;
            font-size: 13px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            max-height: 150px;
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
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .no-faqs {
            padding: 40px;
            text-align: center;
            color: #999;
        }
    </style>
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
                <h1>FAQ Management</h1>
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
                                <div class="faq-item-answer"><?php echo substr($faq['answer'], 0, 150) . '...'; ?></div>
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
