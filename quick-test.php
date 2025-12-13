<?php
/**
 * Quick Admin Test - Verify everything works
 */

require_once 'src/config/.env.php';
require_once 'src/config/database.php';
require_once 'src/middleware/session.php';
require_once 'src/helpers/functions.php';

initSession();
requireAdminLogin();

$db = Database::getInstance();
$conn = $db->getConnection();

// Get statistics
$stats = [
    'total_customers' => 0,
    'total_tickets' => 0,
    'active_tickets' => 0,
    'total_messages' => 0,
    'first_ticket' => null
];

// Count customers
if ($result = $conn->query("SELECT COUNT(*) as count FROM customers")) {
    $row = $result->fetch_assoc();
    $stats['total_customers'] = $row['count'];
}

// Count all tickets
if ($result = $conn->query("SELECT COUNT(*) as count FROM tickets")) {
    $row = $result->fetch_assoc();
    $stats['total_tickets'] = $row['count'];
}

// Count active tickets
if ($result = $conn->query("SELECT COUNT(*) as count FROM tickets WHERE status != 'closed'")) {
    $row = $result->fetch_assoc();
    $stats['active_tickets'] = $row['count'];
}

// Count messages
if ($result = $conn->query("SELECT COUNT(*) as count FROM messages")) {
    $row = $result->fetch_assoc();
    $stats['total_messages'] = $row['count'];
}

// Get first active ticket with details
if ($result = $conn->query("
    SELECT t.id, t.ticket_number, t.subject, t.status, c.name, c.email, 
           COUNT(m.id) as msg_count
    FROM tickets t
    JOIN customers c ON t.customer_id = c.id
    LEFT JOIN messages m ON t.id = m.ticket_id
    WHERE t.status != 'closed'
    GROUP BY t.id
    LIMIT 1
")) {
    $stats['first_ticket'] = $result->fetch_assoc();
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Admin Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 20px;
        }
        .container {
            max-width: 900px;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border: none;
        }
        .stat-card {
            text-align: center;
            padding: 25px;
            border-radius: 12px;
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: 700;
            color: #667eea;
        }
        .stat-label {
            color: #6b7280;
            font-size: 0.95em;
            margin-top: 8px;
        }
        .badge-success {
            background: #d4edda;
            color: #155724;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9em;
        }
        .badge-error {
            background: #f8d7da;
            color: #721c24;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9em;
        }
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .btn-action {
            padding: 15px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: all 0.3s;
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary-custom:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        .btn-success-custom {
            background: #10b981;
            color: white;
        }
        .btn-success-custom:hover {
            background: #059669;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }
        .ticket-info {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }
        .ticket-info strong {
            color: #1f2937;
        }
        .ticket-info small {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-body pt-5 pb-5">
                <h1 style="color: white; margin-bottom: 40px;">
                    <i class="fas fa-stethoscope me-3"></i>Quick Admin System Check
                </h1>

                <!-- Statistics -->
                <div class="row mb-5">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $stats['total_customers']; ?></div>
                            <div class="stat-label">Total Customers</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $stats['total_tickets']; ?></div>
                            <div class="stat-label">Total Tickets</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-number" style="color: #f59e0b;"><?php echo $stats['active_tickets']; ?></div>
                            <div class="stat-label">Active Tickets</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-number" style="color: #10b981;"><?php echo $stats['total_messages']; ?></div>
                            <div class="stat-label">Total Messages</div>
                        </div>
                    </div>
                </div>

                <!-- Status Check -->
                <div style="background: white; padding: 25px; border-radius: 12px; margin-bottom: 30px;">
                    <h3 style="margin-bottom: 20px;">System Status</h3>
                    
                    <?php if ($stats['total_customers'] > 0): ?>
                        <div class="mb-3"><span class="badge-success">✅ Customers exist</span></div>
                    <?php else: ?>
                        <div class="mb-3"><span class="badge-error">❌ No customers yet</span></div>
                    <?php endif; ?>

                    <?php if ($stats['active_tickets'] > 0): ?>
                        <div class="mb-3"><span class="badge-success">✅ Active tickets available</span></div>
                    <?php else: ?>
                        <div class="mb-3"><span class="badge-error">❌ No active tickets - Create one first!</span></div>
                    <?php endif; ?>

                    <?php if ($stats['first_ticket']): ?>
                        <div class="mb-3"><span class="badge-success">✅ First ticket ready for testing</span></div>
                        <div class="ticket-info mt-3">
                            <p>
                                <strong>Ticket:</strong> <?php echo htmlspecialchars($stats['first_ticket']['ticket_number']); ?><br>
                                <strong>Customer:</strong> <?php echo htmlspecialchars($stats['first_ticket']['name']); ?> (<?php echo htmlspecialchars($stats['first_ticket']['email']); ?>)<br>
                                <strong>Subject:</strong> <?php echo htmlspecialchars($stats['first_ticket']['subject']); ?><br>
                                <strong>Status:</strong> <span style="background: #e0e7ff; color: #667eea; padding: 2px 8px; border-radius: 4px; font-weight: 600;"><?php echo ucfirst(str_replace('_', ' ', $stats['first_ticket']['status'])); ?></span><br>
                                <strong>Messages:</strong> <?php echo $stats['first_ticket']['msg_count']; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="mb-3"><span class="badge-error">❌ No tickets available</span></div>
                    <?php endif; ?>
                </div>

                <!-- Quick Links -->
                <h3 style="background: white; padding: 20px; border-radius: 8px 8px 0 0; margin-bottom: 0;">Quick Navigation</h3>
                <div class="action-buttons" style="background: white; padding: 20px;">
                    <a href="../../index.php" class="btn-action btn-primary-custom">
                        <i class="fas fa-home me-2"></i>Customer Home
                    </a>
                    <a href="../../src/admin/" class="btn-action btn-success-custom">
                        <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
                    </a>
                    <a href="manage-tickets.php" class="btn-action btn-primary-custom">
                        <i class="fas fa-list me-2"></i>Manage Tickets
                    </a>
                    <a href="../../admin-diagnostic.php" class="btn-action btn-success-custom">
                        <i class="fas fa-microscope me-2"></i>Diagnostic Test
                    </a>
                </div>

                <!-- Instructions -->
                <div style="background: #f0f7ff; border-left: 4px solid #3b82f6; padding: 20px; border-radius: 8px; margin-top: 30px;">
                    <h4 style="color: #1e40af; margin-bottom: 15px;">
                        <i class="fas fa-info-circle me-2"></i>Testing Instructions
                    </h4>
                    <ol style="color: #1f2937;">
                        <li><strong>Check Status Above</strong> - Ensure you have active tickets</li>
                        <li><strong>Click "Manage Tickets"</strong> - Open the admin tickets page</li>
                        <li><strong>Open Browser Console</strong> - Press F12 → Console tab</li>
                        <li><strong>Click on a Ticket</strong> - You should see console logs like:
                            <pre style="background: white; padding: 10px; border-radius: 4px; margin-top: 5px; color: #667eea; font-size: 0.85em;">selectTicket called with ID: [number]
Loading ticket details for ID: [number]
API response status: 200</pre>
                        </li>
                        <li><strong>Chat Interface</strong> - Should load and display ticket info</li>
                    </ol>
                </div>

                <?php if (!$stats['active_tickets']): ?>
                <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 20px; border-radius: 8px; margin-top: 30px;">
                    <h4 style="color: #92400e; margin-bottom: 15px;">
                        <i class="fas fa-exclamation-triangle me-2"></i>Important
                    </h4>
                    <p style="color: #78350f; margin: 0;">
                        You need to create at least one ticket first! 
                        <a href="../../index.php" style="color: #dc2626; font-weight: 600;">Go to customer home and create a ticket</a>
                    </p>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</body>
</html>
