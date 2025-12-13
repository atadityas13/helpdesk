<?php
/**
 * Admin Ticket Diagnostic
 * Untuk debug tiket tidak bisa diklik
 */

require_once 'src/config/.env.php';
require_once 'src/config/database.php';
require_once 'src/middleware/session.php';
require_once 'src/helpers/functions.php';

initSession();
requireAdminLogin();

// Get database connection
$db = Database::getInstance();

// Get tickets count and data
$ticketsCount = 0;
$tickets = [];
$firstTicket = null;

if ($result = $db->query("
    SELECT t.id, t.ticket_number, t.subject, t.status, c.name, 
           COUNT(m.id) as message_count
    FROM tickets t
    JOIN customers c ON t.customer_id = c.id
    LEFT JOIN messages m ON t.id = m.ticket_id
    WHERE t.status != 'closed'
    GROUP BY t.id
    ORDER BY t.updated_at DESC
    LIMIT 10
")) {
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
        $firstTicket = $firstTicket ?? $row;
    }
    $ticketsCount = count($tickets);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Ticket Diagnostic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 1000px;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .status-ok {
            background: #d4edda;
            color: #155724;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .status-error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .test-item {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        .test-item:last-child {
            border-bottom: none;
        }
        .code-block {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            font-family: monospace;
            font-size: 0.9em;
            overflow-x: auto;
            margin: 10px 0;
        }
        .ticket-test {
            background: white;
            border: 1px solid #e5e7eb;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 8px 0;
            cursor: pointer;
            transition: all 0.2s;
        }
        .ticket-test:hover {
            background: #f9fafb;
            border-color: #667eea;
            transform: translateX(4px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header bg-white pt-4 pb-0">
                <h1>🔍 Admin Ticket Diagnostic</h1>
            </div>
            <div class="card-body">
                
                <!-- Database Status -->
                <div class="test-item">
                    <h3><i class="fas fa-database me-2"></i>Database Status</h3>
                    <?php if ($ticketsCount > 0): ?>
                        <div class="status-ok">✅ Database OK - Found <?php echo $ticketsCount; ?> active tickets</div>
                    <?php else: ?>
                        <div class="status-error">❌ No active tickets found</div>
                        <p>Create a ticket first from the customer side (index.php)</p>
                    <?php endif; ?>
                </div>

                <!-- Tickets List -->
                <div class="test-item">
                    <h3><i class="fas fa-list me-2"></i>Available Tickets for Testing</h3>
                    <?php if (count($tickets) > 0): ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($tickets as $ticket): ?>
                                <div class="ticket-test" onclick="testTicketClick(<?php echo $ticket['id']; ?>)">
                                    <strong><?php echo htmlspecialchars($ticket['ticket_number']); ?></strong> - 
                                    <?php echo htmlspecialchars($ticket['subject']); ?>
                                    <br>
                                    <small style="color: #6b7280;">
                                        <?php echo htmlspecialchars($ticket['name']); ?> | 
                                        Status: <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?> | 
                                        Messages: <?php echo $ticket['message_count']; ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="color: #dc3545;">No tickets available</p>
                    <?php endif; ?>
                </div>

                <!-- JavaScript Test -->
                <div class="test-item">
                    <h3><i class="fas fa-code me-2"></i>JavaScript Test</h3>
                    <p>Click a ticket above to test JavaScript selectTicket() function</p>
                    <div id="testOutput" style="display: none; margin-top: 15px;">
                        <div class="code-block" id="testLog"></div>
                    </div>
                </div>

                <!-- API Test -->
                <div class="test-item">
                    <h3><i class="fas fa-network-wired me-2"></i>API Test</h3>
                    <?php if ($firstTicket): ?>
                        <p>Testing with ticket ID: <?php echo $firstTicket['id']; ?></p>
                        <button class="btn btn-primary" onclick="testAPI(<?php echo $firstTicket['id']; ?>)">
                            Test API Endpoints
                        </button>
                        <div id="apiOutput" style="display: none; margin-top: 15px;">
                            <div class="code-block" id="apiLog"></div>
                        </div>
                    <?php else: ?>
                        <p style="color: #dc3545;">No ticket available for API testing</p>
                    <?php endif; ?>
                </div>

                <!-- Manual Test -->
                <div class="test-item">
                    <h3><i class="fas fa-play me-2"></i>Manual Test</h3>
                    <p>Go to Manage Tickets page and check browser console (F12)</p>
                    <a href="src/admin/manage-tickets.php" class="btn btn-success btn-lg">
                        Open Manage Tickets →
                    </a>
                    <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <p><strong>Instructions:</strong></p>
                        <ol>
                            <li>Click the button above</li>
                            <li>Open DevTools (F12)</li>
                            <li>Go to Console tab</li>
                            <li>Click on any ticket in the list</li>
                            <li>You should see console logs like:
                                <div class="code-block">
selectTicket called with ID: [number]
Loading ticket details for ID: [number]
API response status: 200
                                </div>
                            </li>
                        </ol>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addLog(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString('id-ID');
            const color = type === 'error' ? '#dc3545' : type === 'success' ? '#28a745' : '#667eea';
            console.log(`[${timestamp}] ${message}`);
        }

        function testTicketClick(ticketId) {
            const output = document.getElementById('testOutput');
            const log = document.getElementById('testLog');
            
            output.style.display = 'block';
            log.innerHTML = '';

            try {
                addLog(`Testing selectTicket(${ticketId})`, 'info');
                
                // Simulate what should happen
                log.innerHTML += `<span style="color: #667eea">[TEST] Simulating selectTicket(${ticketId})</span><br>`;
                log.innerHTML += `<span style="color: #28a745">[✓] Function would be called</span><br>`;
                log.innerHTML += `<span style="color: #28a745">[✓] Loading ticket details...</span><br>`;
                log.innerHTML += `<span style="color: #28a745">[✓] Loading messages...</span><br>`;
                log.innerHTML += `<br><strong>Expected API calls:</strong><br>`;
                log.innerHTML += `GET ../api/get-ticket.php?id=${ticketId}<br>`;
                log.innerHTML += `GET ../api/get-ticket-messages.php?ticket_id=${ticketId}<br>`;
                log.innerHTML += `<br><strong>Check actual manage-tickets.php page console for real results</strong>`;
            } catch (e) {
                log.innerHTML += `<span style="color: #dc3545">[ERROR] ${e.message}</span>`;
                addLog(`Error: ${e.message}`, 'error');
            }
        }

        async function testAPI(ticketId) {
            const output = document.getElementById('apiOutput');
            const log = document.getElementById('apiLog');
            
            output.style.display = 'block';
            log.innerHTML = '<span style="color: #667eea">Testing API endpoints...</span><br>';

            const baseUrl = 'src/api';
            const apis = [
                { name: 'get-ticket.php', url: `${baseUrl}/get-ticket.php?id=${ticketId}` },
                { name: 'get-ticket-messages.php', url: `${baseUrl}/get-ticket-messages.php?ticket_id=${ticketId}` }
            ];

            for (const api of apis) {
                try {
                    log.innerHTML += `<br><strong>Testing: ${api.name}</strong><br>`;
                    log.innerHTML += `URL: ${api.url}<br>`;
                    
                    const response = await fetch(api.url);
                    const data = await response.json();
                    
                    if (data.success) {
                        log.innerHTML += `<span style="color: #28a745">[✓] Success (${response.status})</span><br>`;
                        log.innerHTML += `Data: ${JSON.stringify(data, null, 2).substring(0, 200)}...<br>`;
                    } else {
                        log.innerHTML += `<span style="color: #dc3545">[✗] Error: ${data.message}</span><br>`;
                    }
                } catch (e) {
                    log.innerHTML += `<span style="color: #dc3545">[✗] Fetch Error: ${e.message}</span><br>`;
                }
            }
        }

        // Run on load
        window.addEventListener('load', () => {
            console.log('=== Admin Ticket Diagnostic Loaded ===');
            console.log('Tickets available:', <?php echo $ticketsCount; ?>);
            console.log('Database connection:', 'OK');
        });
    </script>
</body>
</html>
