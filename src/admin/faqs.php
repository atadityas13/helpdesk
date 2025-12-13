<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ Management - Helpdesk Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        .dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            width: 250px;
            overflow-y: auto;
        }
        .sidebar h2 {
            margin-bottom: 30px;
            text-align: center;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            padding-bottom: 15px;
        }
        .sidebar-menu {
            list-style: none;
        }
        .sidebar-menu li {
            margin-bottom: 10px;
        }
        .sidebar-menu a {
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            display: block;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.3);
            font-weight: bold;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: #333;
        }
        .btn-add {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .faq-list {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .faq-item {
            padding: 20px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .faq-item:hover {
            background: #f5f5f5;
        }
        .faq-item-question {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .faq-item-answer {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
            display: none;
        }
        .faq-item.active .faq-item-answer {
            display: block;
        }
        .faq-item-footer {
            display: flex;
            gap: 10px;
            font-size: 0.9em;
            color: #999;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }
        .btn-small {
            padding: 6px 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85em;
            transition: all 0.2s ease;
        }
        .btn-small:hover {
            background: #5568d3;
        }
        .btn-delete {
            background: #e74c3c;
        }
        .btn-delete:hover {
            background: #c0392b;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
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
        }
        .btn-submit {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
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
