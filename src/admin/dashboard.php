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
        .header-right {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .user-info {
            text-align: right;
        }
        .user-info p {
            margin: 0;
            color: #666;
            font-size: 0.9em;
        }
        .btn-logout {
            padding: 10px 20px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-logout:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        .stat-card h3 {
            color: #999;
            font-size: 0.9em;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
        }
        .content-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            color: #333;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        table tr:hover {
            background: #f9f9f9;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }
        .badge-open {
            background: #fff3cd;
            color: #856404;
        }
        .badge-in-progress {
            background: #cfe2ff;
            color: #084298;
        }
        .badge-resolved {
            background: #d1e7dd;
            color: #0f5132;
        }
        .badge-closed {
            background: #e2e3e5;
            color: #383d41;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
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
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .sidebar h2 {
                margin-bottom: 0;
                border-bottom: none;
                padding-bottom: 0;
            }
            .sidebar-menu {
                display: flex;
                gap: 5px;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <?php
    require_once __DIR__ . '/../middleware/session.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../helpers/functions.php';
    require_once __DIR__ . '/../helpers/ticket.php';

    // Require admin login
    requireAdminLogin();

    // Get statistics
    $stats = getTicketStatistics();
    $adminUsername = getAdminUsername();
    $adminRole = getAdminRole();

    // Get recent activity
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $recentTickets = [];
    if ($result = $conn->query("
        SELECT t.*, c.name, c.email
        FROM tickets t
        JOIN customers c ON t.customer_id = c.id
        ORDER BY t.created_at DESC
        LIMIT 5
    ")) {
        while ($row = $result->fetch_assoc()) {
            $recentTickets[] = $row;
        }
    }
    ?>

    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>📊 Admin</h2>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active">🏠 Dashboard</a></li>
                <li><a href="manage-tickets.php">🎫 Kelola Tickets</a></li>
                <li><a href="faqs.php">❓ FAQ Management</a></li>
                <li><hr style="border: none; border-top: 1px solid rgba(255, 255, 255, 0.2); margin: 15px 0;"></li>
                <li><a href="../../logout.php">🚪 Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1>📊 Dashboard</h1>
                <div class="header-right">
                    <div class="user-info">
                        <p><strong><?php echo htmlspecialchars($adminUsername); ?></strong></p>
                        <p><?php echo ucfirst($adminRole); ?></p>
                    </div>
                    <a href="../../logout.php" class="btn-logout">Logout</a>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats">
                <div class="stat-card">
                    <h3>Open</h3>
                    <div class="stat-number"><?php echo $stats['open']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>In Progress</h3>
                    <div class="stat-number"><?php echo $stats['in_progress']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Resolved</h3>
                    <div class="stat-number"><?php echo $stats['resolved']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Closed</h3>
                    <div class="stat-number"><?php echo $stats['closed']; ?></div>
                </div>
            </div>

            <!-- Recent Tickets -->
            <div class="content-box">
                <h2>📋 Ticket Terbaru</h2>
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
                        <?php foreach ($recentTickets as $ticket): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($ticket['ticket_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($ticket['name']); ?></td>
                            <td><?php echo htmlspecialchars(truncateText($ticket['subject'], 40)); ?></td>
                            <td>
                                <span class="badge badge-<?php echo str_replace('_', '-', $ticket['status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo formatDateIndonesian($ticket['created_at']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="manage-tickets.php?ticket_id=<?php echo $ticket['id']; ?>" class="btn-small">
                                        Chat
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
