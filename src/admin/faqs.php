<?php
/**
 * Admin FAQ Management
 * Handle FAQ CRUD operations
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ Management - Helpdesk Admin</title>
    <style>
        /* ===== CSS Custom Properties / Variables ===== */
        :root {
            --primary: #667eea;
            --primary-light: #7c8ef0;
            --primary-dark: #5568d3;
            --secondary: #764ba2;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --light: #f9fafb;
            --lighter: #f3f4f6;
            --border: #e5e7eb;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-light: #9ca3af;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
                'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
            background: var(--lighter);
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* ===== Dashboard Layout ===== */
        .dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
            overflow-y: auto;
        }

        /* ===== Sidebar ===== */
        .sidebar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 25px 20px;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
            display: flex;
            flex-direction: column;
            gap: 20px;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .sidebar h2 {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .sidebar-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            display: block;
            padding: 12px 15px;
            border-radius: var(--radius);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(4px);
        }

        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: 600;
        }

        /* ===== Main Content ===== */
        .main-content {
            padding: 32px 24px;
            background: var(--lighter);
            flex: 1;
        }

        /* ===== Header Section ===== */
        .header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            padding: 24px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--border);
        }

        .header h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.5px;
        }

        /* ===== Buttons ===== */
        .btn-add {
            padding: 11px 20px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 700;
            transition: all 0.3s ease;
            font-size: 14px;
            letter-spacing: -0.3px;
            box-shadow: var(--shadow-md);
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-add:active {
            transform: translateY(0);
        }

        .btn-small {
            padding: 7px 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .btn-small:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-delete {
            background: var(--danger);
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        /* ===== FAQ List ===== */
        .faq-list {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .faq-item {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            transition: all 0.2s ease;
            background: white;
        }

        .faq-item:last-child {
            border-bottom: none;
        }

        .faq-item:hover {
            background: var(--light);
            transform: translateX(2px);
        }

        .faq-item-question {
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-size: 16px;
            letter-spacing: -0.3px;
        }

        .faq-item-answer {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 12px;
            line-height: 1.6;
            display: none;
            border-left: 3px solid var(--info);
            padding-left: 16px;
        }

        .faq-item.active .faq-item-answer {
            display: block;
        }

        .faq-item-footer {
            display: flex;
            gap: 12px;
            font-size: 13px;
            color: var(--text-light);
            align-items: center;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }

        /* ===== Modal ===== */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: var(--radius);
            padding: 32px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }

        .modal-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .close {
            color: var(--text-light);
            font-size: 24px;
            font-weight: 700;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            transition: color 0.2s ease;
        }

        .close:hover {
            color: var(--text-primary);
        }

        /* ===== Form ===== */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-weight: 700;
            font-size: 14px;
            letter-spacing: -0.3px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 14px;
            color: var(--text-primary);
            transition: all 0.3s ease;
            background: var(--light);
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .btn-submit {
            width: 100%;
            padding: 12px 20px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 700;
            transition: all 0.3s ease;
            font-size: 14px;
            letter-spacing: -0.3px;
            box-shadow: var(--shadow-md);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* ===== Empty State ===== */
        .empty-state {
            padding: 60px 24px;
            text-align: center;
            color: var(--text-secondary);
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 20px;
        }

        /* ===== Scrollbar Styling ===== */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-light);
        }

        /* ===== Responsive Design ===== */
        @media (max-width: 1024px) {
            .dashboard {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                z-index: 999;
                width: 250px;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                padding: 24px 16px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 16px;
            }

            .header {
                padding: 16px;
            }

            .header h1 {
                font-size: 20px;
            }

            .faq-item {
                padding: 16px;
            }

            .modal-content {
                padding: 24px;
                width: 95%;
            }

            .action-buttons {
                flex-direction: column;
                gap: 8px;
            }

            .btn-small {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .sidebar {
                width: 100%;
            }

            .header h1 {
                font-size: 18px;
            }

            .faq-item-question {
                font-size: 14px;
            }

            .modal-content {
                max-height: 90vh;
            }
        }
    </style>
</head>
<body>
    <?php
    require_once __DIR__ . '/../middleware/session.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../helpers/functions.php';
    require_once __DIR__ . '/../middleware/csrf.php';

    // Require admin login
    requireAdminLogin();

    $adminUsername = getAdminUsername();
    $adminRole = getAdminRole();

    // Get FAQs
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $faqs = [];
    if ($result = $conn->query("
        SELECT * FROM faqs
        ORDER BY created_at DESC
    ")) {
        while ($row = $result->fetch_assoc()) {
            $faqs[] = $row;
        }
    }
    ?>

    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>📊 Admin</h2>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php">🏠 Dashboard</a></li>
                <li><a href="manage-tickets.php">🎫 Kelola Tickets</a></li>
                <li><a href="faqs.php" class="active">❓ FAQ Management</a></li>
                <li><hr style="border: none; border-top: 1px solid rgba(255, 255, 255, 0.2); margin: 15px 0;"></li>
                <li><a href="../../logout.php">🚪 Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1>❓ FAQ Management</h1>
                <button class="btn-add" onclick="openModal()">+ Tambah FAQ</button>
            </div>

            <!-- FAQ List -->
            <div class="faq-list">
                <?php if (empty($faqs)): ?>
                    <div style="padding: 20px; text-align: center; color: #999;">
                        Tidak ada FAQ
                    </div>
                <?php else: ?>
                    <?php foreach ($faqs as $faq): ?>
                        <div class="faq-item" onclick="toggleFAQ(this)">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <div class="faq-item-question"><?php echo htmlspecialchars($faq['question']); ?></div>
                                    <div class="faq-item-answer"><?php echo htmlspecialchars($faq['answer']); ?></div>
                                    <div class="faq-item-footer">
                                        <span><?php echo $faq['category']; ?></span>
                                        <span>Views: <?php echo $faq['views']; ?></span>
                                        <span><?php echo $faq['is_active'] ? '✓ Aktif' : '✗ Nonaktif'; ?></span>
                                    </div>
                                </div>
                                <div class="action-buttons" onclick="event.stopPropagation();">
                                    <button class="btn-small" onclick="editFAQ(<?php echo $faq['id']; ?>)">Edit</button>
                                    <button class="btn-small btn-delete" onclick="deleteFAQ(<?php echo $faq['id']; ?>)">Hapus</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="faqModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Tambah FAQ</h2>
            <form id="faqForm">
                <input type="hidden" id="faqId" name="id">
                <div class="form-group">
                    <label>Pertanyaan</label>
                    <input type="text" id="question" name="question" required maxlength="255">
                </div>
                <div class="form-group">
                    <label>Jawaban</label>
                    <textarea id="answer" name="answer" required rows="6"></textarea>
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select id="category" name="category">
                        <option value="Support">Support</option>
                        <option value="Teknologi">Teknologi</option>
                        <option value="Umum">Umum</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="is_active" name="is_active" checked>
                        Aktif
                    </label>
                </div>
                <button type="submit" class="btn-submit">Simpan</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('faqModal').style.display = 'block';
            document.getElementById('faqId').value = '';
            document.getElementById('question').value = '';
            document.getElementById('answer').value = '';
            document.getElementById('category').value = 'Support';
            document.getElementById('is_active').checked = true;
            document.getElementById('modalTitle').textContent = 'Tambah FAQ';
        }

        function closeModal() {
            document.getElementById('faqModal').style.display = 'none';
        }

        function toggleFAQ(element) {
            element.classList.toggle('active');
        }

        function editFAQ(id) {
            fetch(`../api/get-faq.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const faq = data.data;
                        document.getElementById('faqId').value = faq.id;
                        document.getElementById('question').value = faq.question;
                        document.getElementById('answer').value = faq.answer;
                        document.getElementById('category').value = faq.category;
                        document.getElementById('is_active').checked = faq.is_active;
                        document.getElementById('modalTitle').textContent = 'Edit FAQ';
                        document.getElementById('faqModal').style.display = 'block';
                    }
                })
                .catch(error => alert('Error: ' + error.message));
        }

        function deleteFAQ(id) {
            if (confirm('Yakin ingin menghapus FAQ ini?')) {
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
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => alert('Error: ' + error.message));
            }
        }

        document.getElementById('faqForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.append('csrf_token', '<?php echo getCsrfToken(); ?>');

            const endpoint = formData.get('id') ? '../api/update-faq.php' : '../api/create-faq.php';

            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => alert('Error: ' + error.message));
        });

        window.addEventListener('click', (e) => {
            const modal = document.getElementById('faqModal');
            if (e.target === modal) {
                closeModal();
            }
        });
    </script>
</body>
</html>
