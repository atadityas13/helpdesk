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

// Get unread message count for notification badge
$unreadQuery = "SELECT COUNT(*) as unread FROM messages WHERE sender_type = 'customer' AND is_read = 0";
$unreadResult = $conn->query($unreadQuery);
$unreadCount = $unreadResult->fetch_assoc()['unread'];

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

// Get recent activities (messages) for WhatsApp-like feed
$activitiesQuery = "SELECT m.*, t.ticket_number, c.name
                    FROM messages m
                    JOIN tickets t ON m.ticket_id = t.id
                    JOIN customers c ON t.customer_id = c.id
                    ORDER BY m.created_at DESC
                    LIMIT 20";

$activities = $conn->query($activitiesQuery)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Helpdesk MTsN 11 Majalengka</title>
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-logo">
                <h2><i class="fas fa-headset"></i> Helpdesk</h2>
                <div class="sidebar-subtitle">MTsN 11 Majalengka</div>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <span><i class="fas fa-tachometer-alt"></i> Dashboard</span>
                </a>
                <a href="manage-tickets.php" class="nav-item">
                    <span><i class="fas fa-ticket-alt"></i> Kelola Tickets</span>
                    <?php if ($unreadCount > 0): ?>
                        <span class="notification-badge"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </a>
                <a href="faqs.php" class="nav-item">
                    <span><i class="fas fa-question-circle"></i> FAQ</span>
                </a>
                <a href="../../logout.php" class="nav-item logout">
                    <span><i class="fas fa-sign-out-alt"></i> Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <!-- New Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-chart-line"></i> Dashboard <span class="admin-label"><?php echo $_SESSION['admin_username']; ?></span></h1>
                <div class="header-actions">
                    <button class="btn-refresh" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- Statistics -->
            <section class="dashboard-stats">
                <div class="stat-card total">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Total Tickets</div>
                        <div class="stat-value"><?php echo $stats['total']; ?></div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i> +12%
                        </div>
                    </div>
                </div>

                <div class="stat-card open">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Open</div>
                        <div class="stat-value"><?php echo $stats['open']; ?></div>
                        <div class="stat-change warning">
                            <i class="fas fa-clock"></i> Pending
                        </div>
                    </div>
                </div>

                <div class="stat-card progress">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">In Progress</div>
                        <div class="stat-value"><?php echo $stats['in_progress']; ?></div>
                        <div class="stat-change info">
                            <i class="fas fa-spinner"></i> Active
                        </div>
                    </div>
                </div>

                <div class="stat-card resolved">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Resolved</div>
                        <div class="stat-value"><?php echo $stats['resolved']; ?></div>
                        <div class="stat-change success">
                            <i class="fas fa-thumbs-up"></i> Great!
                        </div>
                    </div>
                </div>
            </section>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Recent Tickets -->
                <section class="dashboard-section tickets-section">
                    <div class="section-header">
                        <h2><i class="fas fa-clock"></i> Recent Tickets</h2>
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

                <!-- Activity Feed (WhatsApp-like) -->
                <section class="dashboard-section activity-section">
                    <div class="section-header">
                        <h2><i class="fas fa-comments"></i> Recent Activity</h2>
                        <div class="activity-toggle">
                            <button class="toggle-btn active" data-filter="all">All</button>
                            <button class="toggle-btn" data-filter="unread">Unread</button>
                        </div>
                    </div>

                    <div class="activity-feed">
                        <?php if (count($activities) > 0): ?>
                            <?php foreach ($activities as $activity): ?>
                                <div class="activity-item <?php echo $activity['is_read'] ? '' : 'unread'; ?>" data-ticket="<?php echo $activity['ticket_number']; ?>">
                                    <div class="activity-avatar">
                                        <?php echo strtoupper(substr($activity['name'], 0, 1)); ?>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-header">
                                            <span class="activity-name"><?php echo htmlspecialchars($activity['name']); ?></span>
                                            <span class="activity-ticket"><?php echo htmlspecialchars($activity['ticket_number']); ?></span>
                                            <span class="activity-time"><?php echo formatDateTime($activity['created_at']); ?></span>
                                        </div>
                                        <div class="activity-message">
                                            <?php echo htmlspecialchars(substr($activity['message'], 0, 100)) . (strlen($activity['message']) > 100 ? '...' : ''); ?>
                                        </div>
                                    </div>
                                    <?php if (!$activity['is_read'] && $activity['sender_type'] === 'customer'): ?>
                                        <div class="unread-indicator"></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-activity">
                                <p>Belum ada aktivitas terbaru</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

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

    <script>
        // Dashboard JavaScript for real-time updates and interactions
        let refreshInterval;

        function refreshDashboard() {
            location.reload();
        }

        function initDashboard() {
            // Auto-refresh every 30 seconds
            refreshInterval = setInterval(() => {
                // Check for new messages/tickets without full refresh
                checkForUpdates();
            }, 30000);

            // Activity feed interactions
            initActivityFeed();
        }

        function checkForUpdates() {
            fetch('../../src/api/get-admin-status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.hasUpdates) {
                        // Show notification or update counters
                        updateNotificationBadges(data);
                    }
                })
                .catch(err => console.error('Error checking updates:', err));
        }

        function updateNotificationBadges(data) {
            const badge = document.querySelector('.notification-badge');
            if (data.unreadCount > 0) {
                if (badge) {
                    badge.textContent = data.unreadCount;
                    badge.style.display = 'inline-block';
                } else {
                    // Create badge if it doesn't exist
                    const navItem = document.querySelector('.nav-item[href*="manage-tickets"]');
                    if (navItem) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'notification-badge';
                        newBadge.textContent = data.unreadCount;
                        navItem.appendChild(newBadge);
                    }
                }
            }
        }

        function initActivityFeed() {
            // Toggle between all/unread activities
            document.querySelectorAll('.toggle-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const filter = this.dataset.filter;
                    const items = document.querySelectorAll('.activity-item');

                    items.forEach(item => {
                        if (filter === 'all') {
                            item.style.display = 'flex';
                        } else if (filter === 'unread') {
                            item.style.display = item.classList.contains('unread') ? 'flex' : 'none';
                        }
                    });
                });
            });

            // Click on activity item to open ticket
            document.querySelectorAll('.activity-item').forEach(item => {
                item.addEventListener('click', function() {
                    const ticketNumber = this.dataset.ticket;
                    window.location.href = `manage-tickets.php?ticket=${ticketNumber}`;
                });
            });
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', initDashboard);

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (refreshInterval) clearInterval(refreshInterval);
        });
    </script>
</body>
</html>
