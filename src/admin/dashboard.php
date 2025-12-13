<?php
/**
 * Admin Dashboard
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
    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #5568d3;
            --primary-light: #8b9ff5;
            --secondary: #764ba2;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --light: #f9fafb;
            --light-gray: #f3f4f6;
            --medium-gray: #e5e7eb;
            --dark-gray: #6b7280;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 1px 3px rgba(0, 0, 0, 0.06);
            --box-shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        /* Dashboard Layout */
        .dashboard {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 0;
            position: fixed;
            height: 100vh;
            width: 260px;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h2 {
            font-size: 1.4em;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 16px 0;
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
            gap: 10px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left-color: white;
            font-weight: 600;
        }

        .sidebar-separator {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin: 8px 0;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 24px;
            background: var(--light);
        }

        /* Header */
        .header {
            background: white;
            padding: 24px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .header h1 {
            color: var(--text-dark);
            font-size: 1.8em;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .header-right {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-left: auto;
        }

        .user-info {
            text-align: right;
        }

        .user-info p {
            margin: 2px 0;
            font-size: 0.9em;
        }

        .user-info strong {
            display: block;
            color: var(--text-dark);
            font-size: 1em;
        }

        .user-info .role {
            color: var(--text-light);
            font-size: 0.85em;
        }

        .btn-logout {
            padding: 10px 24px;
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.9em;
            white-space: nowrap;
        }

        .btn-logout:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: var(--box-shadow);
        }

        .btn-logout:active {
            transform: translateY(0);
        }

        /* Statistics Grid */
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            padding: 20px 16px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
            transition: all 0.3s ease;
            border-top: 4px solid var(--primary);
            position: relative;
            overflow: hidden;
        }
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: -50px;
            width: 100px;
            height: 100px;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 50%;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--box-shadow-lg);
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
            border-top-color: var(--dark-gray);
        }

        .stat-card h3 {
            color: var(--text-light);
            font-size: 0.85em;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .stat-number {
            font-size: 2.4em;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
        }

        /* Content Box */
        .content-box {
            background: white;
            padding: 24px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .content-box h2 {
            font-size: 1.4em;
            color: var(--text-dark);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Table Responsive */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: var(--light-gray);
            padding: 14px;
            text-align: left;
            color: var(--text-dark);
            font-weight: 600;
            border-bottom: 2px solid var(--medium-gray);
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table td {
            padding: 14px;
            border-bottom: 1px solid var(--medium-gray);
            font-size: 0.95em;
        }

        table tbody tr {
            transition: all 0.2s ease;
        }

        table tbody tr:hover {
            background: var(--light);
        }

        /* Badges */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            display: inline-block;
            text-transform: capitalize;
        }

        .badge-open {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-in-progress {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-resolved {
            background: #dcfce7;
            color: #166534;
        }

        .badge-closed {
            background: #f3f4f6;
            color: #4b5563;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .btn-small {
            padding: 8px 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8em;
            font-weight: 600;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-small:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-small:active {
            transform: translateY(0);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }

        .empty-state-icon {
            font-size: 3em;
            margin-bottom: 16px;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .dashboard {
                grid-template-columns: 200px 1fr;
            }

            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
                padding: 16px;
            }

            .sidebar-menu a {
                padding: 12px 16px;
                font-size: 0.9em;
            }

            .header {
                padding: 16px;
                flex-direction: column;
                align-items: flex-start;
            }

            .header h1 {
                font-size: 1.5em;
            }

            .header-right {
                width: 100%;
                justify-content: space-between;
                margin-left: 0;
            }

            .stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 14px;
                margin-bottom: 24px;
            }

            .stat-card {
                padding: 16px 12px;
            }

            .stat-number {
                font-size: 2em;
            }
        }

        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            .sidebar-header {
                padding: 16px 20px;
            }

            .sidebar-menu {
                display: flex;
                overflow-x: auto;
                gap: 0;
                padding: 0;
                margin: 0;
                -webkit-overflow-scrolling: touch;
            }

            .sidebar-menu li {
                flex: 0 0 auto;
            }

            .sidebar-menu a {
                padding: 12px 16px;
                border-left: none;
                border-bottom: 3px solid transparent;
                white-space: nowrap;
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
                padding: 12px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                padding: 16px;
            }

            .header h1 {
                font-size: 1.4em;
                width: 100%;
            }

            .header-right {
                width: 100%;
                margin-left: 0;
                justify-content: space-between;
            }

            .user-info {
                text-align: left;
            }

            .stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .stat-card {
                padding: 16px;
            }

            .stat-number {
                font-size: 2em;
            }

            .content-box {
                padding: 16px;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            table {
                min-width: 600px;
            }

            table th,
            table td {
                padding: 10px 8px;
                font-size: 0.85em;
            }

            .action-buttons {
                flex-wrap: wrap;
            }

            .btn-small {
                padding: 6px 10px;
                font-size: 0.75em;
            }
        }

        @media (max-width: 480px) {
            .dashboard {
                grid-template-columns: 1fr;
            }

            .main-content {
                padding: 8px;
            }

            .header {
                flex-direction: column;
                gap: 12px;
                padding: 12px;
            }

            .header h1 {
                font-size: 1.2em;
            }

            .header-right {
                width: 100%;
                flex-direction: column;
                gap: 12px;
            }

            .user-info {
                width: 100%;
            }

            .btn-logout {
                width: 100%;
                padding: 10px;
            }

            .stats {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .stat-card {
                padding: 14px;
            }

            .stat-number {
                font-size: 1.8em;
            }

            .stat-card h3 {
                font-size: 0.8em;
            }

            .content-box {
                padding: 12px;
                border-radius: 8px;
            }

            .content-box h2 {
                font-size: 1.1em;
                margin-bottom: 16px;
            }
        }

        /* Scrollbar Styling */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar Navigation -->
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

        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1>📊 Dashboard</h1>
                <div class="header-right">
                    <div class="user-info">
                        <strong><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></strong>
                        <span class="role"><?php echo ucfirst($_SESSION['admin_role'] ?? 'Agent'); ?></span>
                    </div>
                    <a href="../../logout.php" class="btn-logout">Logout</a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats">
                <div class="stat-card open">
                    <h3>🔴 Open</h3>
                    <p class="stat-number"><?php echo $statsData['open_tickets'] ?? 0; ?></p>
                </div>
                <div class="stat-card in-progress">
                    <h3>🟡 In Progress</h3>
                    <p class="stat-number"><?php echo $statsData['in_progress_tickets'] ?? 0; ?></p>
                </div>
                <div class="stat-card resolved">
                    <h3>🟢 Resolved</h3>
                    <p class="stat-number"><?php echo $statsData['resolved_tickets'] ?? 0; ?></p>
                </div>
                <div class="stat-card closed">
                    <h3>⚪ Closed</h3>
                    <p class="stat-number"><?php echo $statsData['closed_tickets'] ?? 0; ?></p>
                </div>
            </div>

            <!-- Recent Tickets Table -->
            <div class="content-box">
                <h2>📋 Ticket Terbaru</h2>
                <?php if ($recentTickets && mysqli_num_rows($recentTickets) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No. Ticket</th>
                                <th>Pelanggan</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($ticket = mysqli_fetch_assoc($recentTickets)): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($ticket['ticket_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($ticket['name']); ?></td>
                                <td><?php echo htmlspecialchars(substr($ticket['subject'], 0, 40) . (strlen($ticket['subject']) > 40 ? '...' : '')); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo str_replace('_', '-', $ticket['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y H:i', strtotime($ticket['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="manage-tickets.php?ticket_id=<?php echo $ticket['id']; ?>" class="btn-small">
                                            💬 Chat
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <p>Belum ada ticket</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
