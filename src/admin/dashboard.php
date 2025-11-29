<?php
/**
 * Admin Dashboard
 * Helpdesk MTsN 11 Majalengka
 */

require_once '../../src/config/database.php';
require_once '../../src/middleware/auth.php';
require_once '../../src/helpers/functions.php';

requireAdminLogin();

// Get statistics
$ticketsQuery = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
                FROM tickets";

$stats = $conn->query($ticketsQuery)->fetch_assoc();

// Get recent tickets
$recentTicketsQuery = "SELECT t.*, c.name, c.email, COUNT(m.id) as message_count
                       FROM tickets t
                       JOIN customers c ON t.customer_id = c.id
                       LEFT JOIN messages m ON t.id = m.ticket_id
                       GROUP BY t.id
                       ORDER BY t.updated_at DESC
                       LIMIT 10";

$recentTickets = $conn->query($recentTicketsQuery)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Helpdesk MTsN 11 Majalengka</title>
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
                <a href="dashboard.php" class="nav-item active">
                    <span>📊 Dashboard</span>
                </a>
                <a href="manage-tickets.php" class="nav-item">
                    <span>🎫 Kelola Tickets</span>
                </a>
                <a href="faqs.php" class="nav-item">
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
                <h1>Dashboard</h1>
                <div class="admin-user">
                    <span><?php echo $_SESSION['admin_username']; ?></span>
                </div>
            </header>

            <!-- Statistics -->
            <section class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon total">📊</div>
                    <div class="stat-info">
                        <div class="stat-label">Total Tickets</div>
                        <div class="stat-value"><?php echo $stats['total']; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon open">🔴</div>
                    <div class="stat-info">
                        <div class="stat-label">Open</div>
                        <div class="stat-value"><?php echo $stats['open']; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon progress">🟡</div>
                    <div class="stat-info">
                        <div class="stat-label">In Progress</div>
                        <div class="stat-value"><?php echo $stats['in_progress']; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon resolved">🟢</div>
                    <div class="stat-info">
                        <div class="stat-label">Resolved</div>
                        <div class="stat-value"><?php echo $stats['resolved']; ?></div>
                    </div>
                </div>
            </section>

            <!-- Recent Tickets -->
            <section class="dashboard-section">
                <h2>Tickets Terbaru</h2>
                <div class="tickets-table-container">
                    <table class="tickets-table">
                        <thead>
                            <tr>
                                <th>Nomor Ticket</th>
                                <th>Pengguna</th>
                                <th>Subjek</th>
                                <th>Status</th>
                                <th>Pesan</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTickets as $ticket): ?>
                                <tr>
                                    <td><strong><?php echo $ticket['ticket_number']; ?></strong></td>
                                    <td>
                                        <div><?php echo $ticket['name']; ?></div>
                                        <small><?php echo $ticket['email']; ?></small>
                                    </td>
                                    <td><?php echo $ticket['subject']; ?></td>
                                    <td>
                                        <span class="badge <?php echo getStatusBadge($ticket['status']); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                        </span>
                                    </td>
                                    <td><small><?php echo $ticket['message_count']; ?></small></td>
                                    <td><?php echo formatDateTime($ticket['created_at']); ?></td>
                                    <td>
                                        <a href="manage-tickets.php?ticket=<?php echo $ticket['id']; ?>" class="btn-small">
                                            Buka
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
