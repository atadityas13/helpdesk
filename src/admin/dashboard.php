<?php
/**
 * Admin Dashboard
 * Helpdesk MTsN 11 Majalengka
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/functions.php';

requireAdminLogin();

// Get statistics
$ticketsQuery = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
                FROM tickets";

$stats = $conn->query($ticketsQuery)->fetch_assoc();

// Filter status (optional)
$statusFilter = $_GET['status'] ?? 'all';
$allowedStatuses = ['all', 'open', 'in_progress', 'resolved', 'closed'];
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'all';
}

// Get recent tickets
$recentTicketsQuery = "SELECT t.*, c.name, c.email, COUNT(m.id) as message_count
                       FROM tickets t
                       JOIN customers c ON t.customer_id = c.id
                       LEFT JOIN messages m ON t.id = m.ticket_id";

if ($statusFilter !== 'all') {
    $recentTicketsQuery .= " WHERE t.status = '" . $conn->real_escape_string($statusFilter) . "'";
}

$recentTicketsQuery .= " GROUP BY t.id
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
            <!-- New Page Header -->
            <div class="page-header">
                <h1>Dashboard <span class="admin-label"><?php echo $_SESSION['admin_username']; ?></span></h1>
            </div>

            <!-- Statistics -->
            <section class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon-wrapper total">
                        <span class="stat-icon">📊</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Total Tickets</div>
                        <div class="stat-value"><?php echo $stats['total']; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon-wrapper open">
                        <span class="stat-icon">🔴</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Open</div>
                        <div class="stat-value"><?php echo $stats['open']; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon-wrapper progress">
                        <span class="stat-icon">🟡</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">In Progress</div>
                        <div class="stat-value"><?php echo $stats['in_progress']; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon-wrapper resolved">
                        <span class="stat-icon">🟢</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Resolved</div>
                        <div class="stat-value"><?php echo $stats['resolved']; ?></div>
                    </div>
                </div>
            </section>

            <!-- Recent Tickets -->
            <section class="dashboard-section">
                <div class="section-header">
                    <h2>Tickets Terbaru</h2>
                    <div class="ticket-filters">
                        <a href="?status=all" class="<?php echo $statusFilter === 'all' ? 'active' : ''; ?>">All</a>
                        <a href="?status=open" class="<?php echo $statusFilter === 'open' ? 'active' : ''; ?>">Open</a>
                        <a href="?status=in_progress" class="<?php echo $statusFilter === 'in_progress' ? 'active' : ''; ?>">In Progress</a>
                        <a href="?status=resolved" class="<?php echo $statusFilter === 'resolved' ? 'active' : ''; ?>">Resolved</a>
                        <a href="?status=closed" class="<?php echo $statusFilter === 'closed' ? 'active' : ''; ?>">Closed</a>
                    </div>
                </div>
                
                <div class="ticket-list-modern">
                    <?php if (count($recentTickets) > 0): ?>
                        <?php foreach ($recentTickets as $ticket): ?>
                            <a href="manage-tickets.php?ticket=<?php echo $ticket['id']; ?>" class="ticket-card">
                                <div class="ticket-card-left">
                                    <div class="ticket-customer-avatar">
                                        <?php echo strtoupper(substr($ticket['name'], 0, 1)); ?>
                                    </div>
                                    <div class="ticket-details">
                                        <div class="ticket-subject"><?php echo htmlspecialchars($ticket['subject']); ?></div>
                                        <div class="ticket-customer-name"><?php echo htmlspecialchars($ticket['name']); ?> • <?php echo htmlspecialchars($ticket['ticket_number']); ?></div>
                                    </div>
                                </div>
                                <div class="ticket-card-right">
                                    <span class="badge <?php echo getStatusBadge($ticket['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                    </span>
                                    <div class="ticket-time"><?php echo formatDateTime($ticket['updated_at']); ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-tickets-found">
                            <p>Tidak ada tiket dengan status "<?php echo htmlspecialchars($statusFilter); ?>"</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
