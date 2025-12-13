<?php
/**
 * Admin Dashboard - Modern Design
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
    ORDER BY t.created_at DESC LIMIT 8");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Helpdesk</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #667eea;
            --primary-dark: #5568d3;
            --secondary: #764ba2;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --text-dark: #1f2937;
            --text-gray: #6b7280;
            --bg-light: #f9fafb;
            --bg-lighter: #f3f4f6;
            --border-light: #e5e7eb;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .dashboard {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 0;
            position: fixed;
            height: 100vh;
            width: 260px;
            overflow-y: auto;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 28px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .sidebar-header h2 {
            font-size: 1.5em;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.5px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-weight: 500;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-left-color: white;
            font-weight: 700;
        }

        .sidebar-separator {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin: 12px 0;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: 260px;
            padding: 32px 24px;
            background: var(--bg-light);
        }

        /* ===== HEADER ===== */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            gap: 24px;
        }

        .page-title {
            font-size: 2em;
            font-weight: 800;
            color: var(--text-dark);
            letter-spacing: -1px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-left: auto;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            display: block;
            font-weight: 700;
            color: var(--text-dark);
            font-size: 0.95em;
        }

        .user-role {
            display: block;
            color: var(--text-gray);
            font-size: 0.85em;
            margin-top: 2px;
        }

        .btn-logout {
            padding: 10px 24px;
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.9em;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .btn-logout:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(239, 68, 68, 0.3);
        }

        /* ===== STATS SECTION ===== */
        .stats-section {
            margin-bottom: 40px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 4px solid var(--primary);
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
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
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
        }

        .stat-card.open {
            border-left-color: var(--warning);
        }

        .stat-card.in-progress {
            border-left-color: var(--info);
        }

        .stat-card.resolved {
            border-left-color: var(--success);
        }

        .stat-card.closed {
            border-left-color: var(--text-gray);
        }

        .stat-label {
            font-size: 0.85em;
            color: var(--text-gray);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
        }

        .stat-value {
            font-size: 2.8em;
            font-weight: 900;
            color: var(--primary);
            line-height: 1;
            position: relative;
            z-index: 1;
        }

        .stat-card.open .stat-value { color: var(--warning); }
        .stat-card.in-progress .stat-value { color: var(--info); }
        .stat-card.resolved .stat-value { color: var(--success); }
        .stat-card.closed .stat-value { color: var(--text-gray); }

        /* ===== TICKETS SECTION ===== */
        .tickets-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .section-header {
            padding: 24px;
            border-bottom: 1px solid var(--border-light);
            font-size: 1.3em;
            font-weight: 800;
            color: var(--text-dark);
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: var(--bg-lighter);
        }

        table th {
            padding: 16px 24px;
            text-align: left;
            font-size: 0.85em;
            font-weight: 700;
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-light);
        }

        table td {
            padding: 16px 24px;
            border-bottom: 1px solid var(--border-light);
            font-size: 0.95em;
        }

        table tbody tr {
            transition: all 0.2s ease;
        }

        table tbody tr:hover {
            background: var(--bg-light);
        }

        .ticket-number {
            font-weight: 700;
            color: var(--primary);
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8em;
            font-weight: 700;
            text-transform: capitalize;
        }

        .badge-open {
            background: rgba(245, 158, 11, 0.15);
            color: #92400e;
        }

        .badge-in-progress {
            background: rgba(59, 130, 246, 0.15);
            color: #1e40af;
        }

        .badge-resolved {
            background: rgba(16, 185, 129, 0.15);
            color: #065f46;
        }

        .badge-closed {
            background: rgba(107, 114, 128, 0.15);
            color: #374151;
        }

        .action-btn {
            padding: 8px 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8em;
            font-weight: 700;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }

        .action-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .empty-state {
            padding: 60px 40px;
            text-align: center;
            color: var(--text-gray);
        }

        .empty-icon {
            font-size: 3em;
            margin-bottom: 16px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-value {
                font-size: 2.2em;
            }
        }

        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }

            .sidebar-menu {
                display: flex;
                overflow-x: auto;
                padding: 0;
                gap: 0;
                -webkit-overflow-scrolling: touch;
            }

            .sidebar-menu li {
                flex: 0 0 auto;
            }

            .sidebar-menu a {
                border-left: none;
                border-bottom: 3px solid transparent;
                padding: 12px 16px;
            }

            .sidebar-menu a.active {
                border-left: none;
                border-bottom-color: white;
            }

            .sidebar-separator {
                display: none;
            }

            .main-content {
                margin-left: 0;
                padding: 20px 16px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                margin-bottom: 24px;
            }

            .page-title {
                font-size: 1.6em;
                width: 100%;
            }

            .user-section {
                width: 100%;
                margin-left: 0;
                flex-direction: column;
                align-items: flex-start;
            }

            .user-info {
                text-align: left;
            }

            .btn-logout {
                width: 100%;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
                margin-bottom: 24px;
            }

            .stat-card {
                padding: 16px;
            }

            .stat-value {
                font-size: 1.8em;
            }

            .section-header {
                padding: 16px;
                font-size: 1.1em;
            }

            table th,
            table td {
                padding: 12px 16px;
                font-size: 0.85em;
            }

            .table-wrapper {
                overflow-x: auto;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 12px;
            }

            .page-header {
                margin-bottom: 20px;
            }

            .page-title {
                font-size: 1.4em;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .stat-card {
                padding: 14px;
            }

            .stat-value {
                font-size: 1.8em;
            }

            .stat-label {
                font-size: 0.75em;
            }

            .section-header {
                padding: 14px;
                font-size: 1em;
            }

            table th,
            table td {
                padding: 10px 12px;
                font-size: 0.8em;
            }

            .action-btn {
                padding: 6px 12px;
                font-size: 0.75em;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>📊 Helpdesk</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active">🏠 Dashboard</a></li>
                <li><a href="manage-tickets.php">🎫 Kelola Tickets</a></li>
                <li><a href="faqs.php">❓ FAQ Management</a></li>
                <li><hr class="sidebar-separator"></li>
                <li><a href="../../logout.php">🚪 Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">📈 Dashboard</h1>
                <div class="user-section">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars(getAdminUsername()); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars(getAdminRole()); ?></span>
                    </div>
                    <a href="../../logout.php" class="btn-logout">Logout</a>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-section">
                <div class="stats-grid">
                    <div class="stat-card open">
                        <div class="stat-label">🔴 Open</div>
                        <div class="stat-value"><?php echo $statsData['open_tickets'] ?? 0; ?></div>
                    </div>
                    <div class="stat-card in-progress">
                        <div class="stat-label">🟡 In Progress</div>
                        <div class="stat-value"><?php echo $statsData['in_progress_tickets'] ?? 0; ?></div>
                    </div>
                    <div class="stat-card resolved">
                        <div class="stat-label">🟢 Resolved</div>
                        <div class="stat-value"><?php echo $statsData['resolved_tickets'] ?? 0; ?></div>
                    </div>
                    <div class="stat-card closed">
                        <div class="stat-label">⚪ Closed</div>
                        <div class="stat-value"><?php echo $statsData['closed_tickets'] ?? 0; ?></div>
                    </div>
                </div>
            </div>

            <!-- Recent Tickets -->
            <div class="tickets-section">
                <div class="section-header">📋 Ticket Terbaru</div>
                <?php if ($recentTickets && mysqli_num_rows($recentTickets) > 0): ?>
                    <div class="table-wrapper">
                        <table>
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
                                    <td><?php echo htmlspecialchars(substr($ticket['subject'], 0, 50) . (strlen($ticket['subject']) > 50 ? '...' : '')); ?></td>
                                    <td><span class="badge badge-<?php echo str_replace('_', '-', $ticket['status']); ?>"><?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($ticket['created_at'])); ?></td>
                                    <td><a href="manage-tickets.php?ticket_id=<?php echo $ticket['id']; ?>" class="action-btn">💬 Balas</a></td>
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
    </div>
</body>
</html>
