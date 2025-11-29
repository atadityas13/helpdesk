<?php
/**
 * Admin Dashboard - Tampilan Helpdesk Modern
 * Helpdesk MTsN 11 Majalengka
 */

// --- Dependencies ---
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/functions.php';

requireAdminLogin();

// --- Logika Pengambilan Data ---

// 1. Dapatkan Statistik
$ticketsQuery = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
                FROM tickets";

$stats = $conn->query($ticketsQuery)->fetch_assoc();

// 2. Filter Status (opsional)
$statusFilter = $_GET['status'] ?? 'all';
$allowedStatuses = ['all', 'open', 'in_progress', 'resolved', 'closed'];
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'all';
}

// 3. Dapatkan Tiket Terbaru (Untuk Daftar Chat/Panel Kiri)
$recentTicketsQuery = "SELECT t.*, c.name, c.email, COUNT(m.id) as message_count
                       FROM tickets t
                       JOIN customers c ON t.customer_id = c.id
                       LEFT JOIN messages m ON t.id = m.ticket_id";

// Kondisi filter
if ($statusFilter !== 'all') {
    // Menggunakan parameter binding atau real_escape_string untuk keamanan
    $recentTicketsQuery .= " WHERE t.status = '" . $conn->real_escape_string($statusFilter) . "'";
}

$recentTicketsQuery .= " GROUP BY t.id
                         ORDER BY t.updated_at DESC
                         LIMIT 15"; 

$recentTickets = $conn->query($recentTicketsQuery)->fetch_all(MYSQLI_ASSOC);

// Data admin untuk header
$adminUsername = $_SESSION['admin_username'] ?? 'Admin Helpdesk';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Helpdesk MTsN 11 Majalengka</title>
    <link rel="stylesheet" href="../../public/css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-app-layout">

        <aside class="main-sidebar">
            <div class="sidebar-logo">
                <h2>🎓 **Helpdesk**</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active"><i class="fas fa-tachometer-alt"></i> **Dashboard**</a>
                <a href="manage-tickets.php" class="nav-item"><i class="fas fa-headset"></i> Kelola Tiket</a>
                <a href="manage-users.php" class="nav-item"><i class="fas fa-users"></i> Kelola User</a>
                <a href="faqs.php" class="nav-item"><i class="fas fa-question-circle"></i> FAQ</a>
                <a href="../../logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <div class="ticket-list-panel">
            <header class="panel-header">
                <h3>**Daftar Tiket**</h3>
            </header>
            
            <div class="ticket-filter">
                <p>Filter Status:</p>
                <?php foreach ($allowedStatuses as $status): ?>
                    <a href="?status=<?php echo $status; ?>" 
                       class="filter-btn <?php echo $statusFilter === $status ? 'active' : ''; ?> status-<?php echo $status; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="list-container">
                <?php if (count($recentTickets) > 0): ?>
                    <?php foreach ($recentTickets as $ticket): 
                        // Tambahkan indikator visual untuk tiket baru/belum direspon
                        $isNewOrUnread = $ticket['status'] === 'open' && $ticket['message_count'] > 0;
                        $badgeClass = getStatusBadge($ticket['status']);
                    ?>
                        <a href="view-ticket.php?id=<?php echo $ticket['id']; ?>" class="ticket-item <?php echo $isNewOrUnread ? 'unread-indicator' : ''; ?>">
                            <div class="avatar-placeholder">
                                <?php echo strtoupper(substr($ticket['name'], 0, 1)); ?>
                            </div>
                            <div class="ticket-info">
                                <div class="ticket-header">
                                    <div class="customer-name">**<?php echo htmlspecialchars($ticket['name']); ?>**</div>
                                    <div class="ticket-date"><?php echo formatDateTime($ticket['updated_at']); ?></div>
                                </div>
                                <div class="ticket-subject-preview">
                                    <span class="preview-text"><?php echo htmlspecialchars(truncateText($ticket['subject'], 35)); ?></span>
                                    <?php if ($ticket['message_count'] > 0): ?>
                                        <span class="message-count-badge"><?php echo $ticket['message_count']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="ticket-meta">
                                    <span class="ticket-id">#<?php echo $ticket['ticket_number']; ?></span>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-tickets">
                        <i class="fas fa-inbox fa-3x"></i>
                        <p>Tidak ada tiket yang cocok dengan filter **'<?php echo ucfirst(str_replace('_', ' ', $statusFilter)); ?>'**.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <main class="admin-content-area">
            <header class="admin-header">
                <div class="header-left">
                    <h1>**Ringkasan Aktivitas Helpdesk**</h1>
                    <p class="greeting-message">Selamat datang kembali, **<?php echo $adminUsername; ?>**. Lihat metrik terbaru di bawah.</p>
                </div>
                <div class="admin-user-info">
                    <span class="user-avatar-header"><?php echo strtoupper(substr($adminUsername, 0, 1)); ?></span>
                </div>
            </header>

            <section class="dashboard-stats-cards">
                <div class="stat-card stat-total">
                    <i class="fas fa-layer-group stat-icon"></i>
                    <div class="stat-info">
                        <div class="stat-label">Total Tiket Masuk</div>
                        <div class="stat-value"><?php echo $stats['total']; ?></div>
                    </div>
                </div>

                <div class="stat-card stat-open">
                    <i class="fas fa-clock stat-icon"></i>
                    <div class="stat-info">
                        <div class="stat-label">Menunggu Respon (Open)</div>
                        <div class="stat-value"><?php echo $stats['open']; ?></div>
                    </div>
                </div>

                <div class="stat-card stat-progress">
                    <i class="fas fa-cogs stat-icon"></i>
                    <div class="stat-info">
                        <div class="stat-label">Sedang Diproses</div>
                        <div class="stat-value"><?php echo $stats['in_progress']; ?></div>
                    </div>
                </div>

                <div class="stat-card stat-resolved">
                    <i class="fas fa-check-double stat-icon"></i>
                    <div class="stat-info">
                        <div class="stat-label">Selesai (Resolved)</div>
                        <div class="stat-value"><?php echo $stats['resolved']; ?></div>
                    </div>
                </div>
            </section>
            
            <section class="dashboard-section chart-section">
                <h2>📊 Analisis Tren dan Kinerja</h2>
                <div class="chart-container">
                    [Placeholder untuk Grafik Kinerja (misalnya, Line Chart Time Series atau Pie Chart Distribusi)]
                    <p class="chart-note">Integrasikan library charting (Chart.js/ApexCharts) di sini untuk visualisasi data status dan waktu respon.</p>
                </div>
            </section>

            <section class="dashboard-section quick-links">
                <h2>🔗 Akses Cepat</h2>
                <div class="link-grid">
                    <a href="manage-tickets.php?status=open" class="quick-link-item link-new">
                        <i class="fas fa-plus-circle"></i> Tangani Tiket Baru
                    </a>
                    <a href="manage-users.php" class="quick-link-item link-users">
                        <i class="fas fa-user-friends"></i> Kelola Semua User
                    </a>
                    <a href="faqs.php" class="quick-link-item link-faq">
                        <i class="fas fa-list-alt"></i> Tinjau FAQ
                    </a>
                </div>
            </section>

        </main>
    </div>
</body>
</html>