<?php
/**
 * Admin Dashboard - Bootstrap Design
 * Display admin statistics and recent tickets
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

// Get statistics
$stats = $db->query("SELECT 
    COUNT(CASE WHEN status = 'open' THEN 1 END) as open_tickets,
    COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress_tickets,
    COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved_tickets,
    COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed_tickets,
    COUNT(DISTINCT customer_id) as total_customers
FROM tickets");
$statsData = $stats ? mysqli_fetch_assoc($stats) : [];

// Get recent tickets
$recentTickets = $db->query("SELECT t.*, c.name, c.email FROM tickets t 
    LEFT JOIN customers c ON t.customer_id = c.id 
    ORDER BY t.created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Helpdesk</title>
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        /* ===== SIDEBAR ===== */
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
            letter-spacing: -0.5px;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            padding: 12px 20px 12px 20px;
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

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: 260px;
            padding: 30px;
        }

        /* ===== TOP BAR ===== */
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
            letter-spacing: -0.5px;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            text-align: right;
        }

        .user-info .username {
            display: block;
            font-weight: 700;
            color: #1f2937;
            font-size: 0.95em;
        }

        .user-info .role {
            display: block;
            color: #6b7280;
            font-size: 0.85em;
            margin-top: 2px;
        }

        /* ===== STATS CARDS ===== */
        .stats-container {
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border-top: 4px solid var(--primary);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.08), transparent);
            border-radius: 50%;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
        }

        .stat-card.open {
            border-top-color: var(--warning);
        }

        .stat-card.in-progress {
            border-top-color: var(--info);
        }

        .stat-card.resolved {
            border-top-color: var(--success);
        }

        .stat-card.closed {
            border-top-color: #6b7280;
        }

        .stat-card-content {
            position: relative;
            z-index: 1;
        }

        .stat-label {
            font-size: 0.85em;
            color: #6b7280;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }

        .stat-value {
            font-size: 2.5em;
            font-weight: 900;
            color: var(--primary);
            line-height: 1;
        }

        .stat-card.open .stat-value { color: var(--warning); }
        .stat-card.in-progress .stat-value { color: var(--info); }
        .stat-card.resolved .stat-value { color: var(--success); }
        .stat-card.closed .stat-value { color: #6b7280; }

        /* ===== TABLE SECTION ===== */
        .tickets-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-bottom: 2px solid #e5e7eb;
            padding: 24px;
            font-size: 1.2em;
            font-weight: 700;
            color: #1f2937;
            letter-spacing: -0.3px;
        }

        .table {
            margin-bottom: 0;
            font-size: 0.95em;
        }

        .table thead th {
            background-color: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            color: #1f2937;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85em;
            padding: 14px;
        }

        .table tbody td {
            padding: 14px;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .ticket-number {
            font-weight: 700;
            color: var(--primary);
        }

        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.8em;
            text-transform: capitalize;
        }

        .badge-open {
            background-color: rgba(245, 158, 11, 0.15);
            color: #92400e;
        }

        .badge-in-progress {
            background-color: rgba(59, 130, 246, 0.15);
            color: #1e40af;
        }

        .badge-resolved {
            background-color: rgba(16, 185, 129, 0.15);
            color: #065f46;
        }

        .badge-closed {
            background-color: rgba(107, 114, 128, 0.15);
            color: #374151;
        }

        .btn-action {
            padding: 6px 14px;
            font-size: 0.85em;
            font-weight: 700;
            border-radius: 6px;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-action {
            background-color: var(--primary);
            color: white;
        }

        .btn-action:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }

        .empty-state {
            padding: 60px 40px;
            text-align: center;
            color: #6b7280;
        }

        .empty-icon {
            font-size: 3em;
            margin-bottom: 16px;
        }

        /* ===== RESPONSIVE ===== */
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
                padding: 16px 20px;
            }

            .top-bar h1 {
                font-size: 1.6em;
                width: 100%;
            }

            .user-menu {
                width: 100%;
                justify-content: space-between;
            }

            .btn-logout {
                width: 100%;
            }

            .stat-card {
                padding: 16px;
                margin-bottom: 12px;
            }

            .stat-value {
                font-size: 2em;
            }

            .card-header {
                padding: 16px;
                font-size: 1.1em;
            }

            .table thead th,
            .table tbody td {
                padding: 10px 12px;
                font-size: 0.85em;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 12px 10px;
            }

            .top-bar {
                padding: 12px 16px;
            }

            .top-bar h1 {
                font-size: 1.4em;
            }

            .stat-value {
                font-size: 1.8em;
            }

            .card-header {
                padding: 14px 16px;
                font-size: 1em;
            }

            .table thead th,
            .table tbody td {
                padding: 8px 10px;
                font-size: 0.8em;
            }

            .btn-action {
                padding: 4px 10px;
                font-size: 0.75em;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="navbar-brand">📊 Helpdesk</div>
        <nav class="nav flex-column p-3">
            <a class="nav-link active" href="dashboard.php">
                <i class="fas fa-home me-2"></i> Dashboard
            </a>
            <a class="nav-link" href="manage-tickets.php">
                <i class="fas fa-ticket-alt me-2"></i> Kelola Tickets
            </a>
            <a class="nav-link" href="faqs.php">
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
        <!-- Top Bar -->
        <div class="top-bar">
            <h1>📈 Dashboard</h1>
            <div class="user-menu">
                <div class="user-info">
                    <span class="username"><?php echo htmlspecialchars(getAdminUsername()); ?></span>
                    <span class="role"><?php echo htmlspecialchars(getAdminRole()); ?></span>
                </div>
                <a href="../../logout.php" class="btn btn-danger btn-logout">Logout</a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card open">
                        <div class="stat-card-content">
                            <div class="stat-label">🔴 Open</div>
                            <div class="stat-value"><?php echo $statsData['open_tickets'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card in-progress">
                        <div class="stat-card-content">
                            <div class="stat-label">🟡 In Progress</div>
                            <div class="stat-value"><?php echo $statsData['in_progress_tickets'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card resolved">
                        <div class="stat-card-content">
                            <div class="stat-label">🟢 Resolved</div>
                            <div class="stat-value"><?php echo $statsData['resolved_tickets'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card closed">
                        <div class="stat-card-content">
                            <div class="stat-label">⚪ Closed</div>
                            <div class="stat-value"><?php echo $statsData['closed_tickets'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Tickets Table -->
        <div class="tickets-card">
            <div class="card-header">
                <i class="fas fa-list me-2"></i> Ticket Terbaru
            </div>
            <?php if ($recentTickets && mysqli_num_rows($recentTickets) > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No. Ticket</th>
                                <th>Pelanggan</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($ticket = mysqli_fetch_assoc($recentTickets)): ?>
                            <tr>
                                <td><span class="ticket-number"><?php echo htmlspecialchars($ticket['ticket_number']); ?></span></td>
                                <td><?php echo htmlspecialchars($ticket['name']); ?></td>
                                <td><?php echo htmlspecialchars(substr($ticket['subject'], 0, 40) . (strlen($ticket['subject']) > 40 ? '...' : '')); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo str_replace('_', '-', $ticket['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($ticket['created_at'])); ?></td>
                                <td>
                                    <a href="manage-tickets.php?ticket_id=<?php echo $ticket['id']; ?>" class="btn btn-action">
                                        <i class="fas fa-reply me-1"></i> Balas
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <p>Belum ada ticket</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        function logoutAdmin() {
            Swal.fire({
                title: 'Logout?',
                text: 'Apakah Anda yakin ingin logout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#667eea',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../../logout.php';
                }
            });
        }
    </script>
</body>
</html>
