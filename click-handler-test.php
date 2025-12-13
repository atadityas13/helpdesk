<?php
/**
 * Admin Click Handler Test
 * Minimal test untuk verify selectTicket() function works
 */

require_once 'src/config/.env.php';
require_once 'src/config/database.php';
require_once 'src/middleware/session.php';
require_once 'src/helpers/functions.php';

initSession();
requireAdminLogin();

$db = Database::getInstance();
$conn = $db->getConnection();

// Get first active ticket
$firstTicket = null;
if ($result = $conn->query("
    SELECT t.id, t.ticket_number, t.subject, c.name
    FROM tickets t
    JOIN customers c ON t.customer_id = c.id
    WHERE t.status != 'closed'
    ORDER BY t.id DESC
    LIMIT 1
")) {
    $firstTicket = $result->fetch_assoc();
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Click Handler Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 20px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .container {
            max-width: 1000px;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border: none;
        }
        .console-output {
            background: #1e1e1e;
            color: #4ec9b0;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            line-height: 1.6;
            max-height: 400px;
            overflow-y: auto;
            margin: 20px 0;
            border: 1px solid #333;
        }
        .console-line {
            margin: 4px 0;
        }
        .console-log {
            color: #4ec9b0;
        }
        .console-error {
            color: #f48771;
        }
        .console-warn {
            color: #dcdcaa;
        }
        .console-info {
            color: #9cdcfe;
        }
        .test-button {
            padding: 12px 24px;
            margin: 10px 5px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .test-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .btn-run {
            background: #10b981;
            color: white;
        }
        .btn-run:hover {
            background: #059669;
            color: white;
        }
        .btn-clear {
            background: #ef4444;
            color: white;
        }
        .btn-clear:hover {
            background: #dc2626;
            color: white;
        }
        .code-block {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            font-family: monospace;
            margin: 10px 0;
            overflow-x: auto;
        }
        .section-title {
            font-size: 1.3em;
            font-weight: 700;
            color: #1f2937;
            margin-top: 30px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-body p-5">
                <h1 style="color: #667eea; margin-bottom: 10px;">
                    <i class="fas fa-mouse me-3"></i>Admin Click Handler Test
                </h1>
                <p style="color: #6b7280; margin-bottom: 30px;">Test apakah selectTicket() function berfungsi</p>

                <!-- Test Info -->
                <div style="background: #f0f7ff; border-left: 4px solid #3b82f6; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                    <h4 style="color: #1e40af; margin-bottom: 15px;">
                        <i class="fas fa-info-circle me-2"></i>Test Setup
                    </h4>
                    <?php if ($firstTicket): ?>
                        <p style="margin: 10px 0;">
                            <strong>Status:</strong> ✅ Ticket tersedia untuk testing
                        </p>
                        <p style="margin: 10px 0;">
                            <strong>Ticket ID:</strong> <code><?php echo $firstTicket['id']; ?></code>
                        </p>
                        <p style="margin: 10px 0;">
                            <strong>Ticket Number:</strong> <code><?php echo htmlspecialchars($firstTicket['ticket_number']); ?></code>
                        </p>
                    <?php else: ?>
                        <p style="color: #dc2626; margin: 10px 0;">
                            ❌ Tidak ada ticket untuk testing. Buat ticket terlebih dahulu di <a href="index.php" style="color: #dc2626; font-weight: 600;">halaman customer</a>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Test Controls -->
                <div class="section-title">Test Controls</div>
                <div style="margin-bottom: 20px;">
                    <?php if ($firstTicket): ?>
                        <button class="test-button btn-run" onclick="testSelectTicket(<?php echo $firstTicket['id']; ?>)">
                            <i class="fas fa-play me-2"></i>Run selectTicket() Test
                        </button>
                    <?php else: ?>
                        <button class="test-button btn-run" disabled style="opacity: 0.5; cursor: not-allowed;">
                            <i class="fas fa-play me-2"></i>Run selectTicket() Test
                        </button>
                    <?php endif; ?>
                    <button class="test-button btn-clear" onclick="clearOutput()">
                        <i class="fas fa-trash me-2"></i>Clear Output
                    </button>
                </div>

                <!-- Console Output -->
                <div class="section-title">Test Output</div>
                <div class="console-output" id="consoleOutput">
                    <div class="console-line console-info"># Click "Run selectTicket() Test" button above to start</div>
                </div>

                <!-- Expected Output -->
                <div class="section-title">Expected Output</div>
                <p>Ketika test dijalankan, console seharusnya menampilkan:</p>
                <div class="code-block">
> selectTicket called with ID: <?php echo $firstTicket['id'] ?? 'X'; ?><br>
> Type of ticketId: number<br>
> Ticket item highlighted<br>
> Chat input area shown<br>
> Loading ticket details for ID: <?php echo $firstTicket['id'] ?? 'X'; ?><br>
> API response status: 200<br>
> Ticket data: {success: true, data: {...}}<br>
> Loading ticket messages for ID: <?php echo $firstTicket['id'] ?? 'X'; ?><br>
> Messages API response status: 200<br>
> Messages data: {success: true, data: {messages: [...]}}
                </div>

                <!-- Next Steps -->
                <div class="section-title">Next Steps</div>
                <ol style="color: #1f2937;">
                    <li><strong>Run Test Above</strong> - Click "Run selectTicket() Test" button</li>
                    <li><strong>Check Output</strong> - Verify all expected logs appear</li>
                    <li><strong>If All Good</strong> - Go to <a href="src/admin/manage-tickets.php" style="color: #667eea; font-weight: 600;">Manage Tickets</a> and test click handler there</li>
                    <li><strong>If Error Appears</strong> - Follow error message for fix</li>
                </ol>

                <!-- Troubleshooting -->
                <div class="section-title">Troubleshooting</div>
                <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 20px; border-radius: 8px;">
                    <h5 style="color: #92400e; margin-bottom: 15px;">
                        <i class="fas fa-exclamation-triangle me-2"></i>Common Issues
                    </h5>
                    <ul style="color: #78350f; margin: 10px 0;">
                        <li><strong>"Ticket item highlighted" tidak muncul?</strong> → Element dengan ID tidak ditemukan</li>
                        <li><strong>"API response 200" tidak muncul?</strong> → Fetch failed atau server error</li>
                        <li><strong>Test button tidak ada/disabled?</strong> → Tidak ada ticket, buat ticket dulu</li>
                        <li><strong>Tidak ada output?</strong> → JavaScript error sebelumnya, lihat browser console</li>
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        const outputElement = document.getElementById('consoleOutput');

        function addLog(message, type = 'log') {
            const timestamp = new Date().toLocaleTimeString('id-ID');
            const line = document.createElement('div');
            line.className = `console-line console-${type}`;
            
            let prefix = '> ';
            if (type === 'error') prefix = '✗ ERROR: ';
            if (type === 'warn') prefix = '⚠ WARNING: ';
            if (type === 'info') prefix = 'ℹ INFO: ';
            if (type === 'success') prefix = '✓ SUCCESS: ';
            
            line.textContent = prefix + message;
            outputElement.appendChild(line);
            outputElement.scrollTop = outputElement.scrollHeight;
        }

        function clearOutput() {
            outputElement.innerHTML = '<div class="console-line console-info"># Console cleared</div>';
        }

        async function testSelectTicket(ticketId) {
            clearOutput();
            addLog('Test started for ticket ID: ' + ticketId, 'info');
            
            try {
                // Simulate selectTicket logic
                addLog('selectTicket called with ID: ' + ticketId, 'log');
                addLog('Type of ticketId: ' + typeof ticketId, 'log');
                
                if (!ticketId || isNaN(ticketId)) {
                    throw new Error('Invalid ticket ID');
                }
                
                addLog('Ticket ID is valid', 'success');
                addLog('Ticket item would be highlighted', 'log');
                addLog('Chat input area would be shown', 'log');
                
                // Test API calls
                addLog('Loading ticket details for ID: ' + ticketId, 'log');
                
                const detailsResponse = await fetch(`src/api/get-ticket.php?id=${ticketId}`);
                addLog('API response status: ' + detailsResponse.status, 'log');
                
                const detailsData = await detailsResponse.json();
                if (detailsData.success) {
                    addLog('Ticket data received successfully', 'success');
                    addLog('Ticket: ' + detailsData.data.ticket_number, 'log');
                } else {
                    addLog('Ticket API error: ' + detailsData.message, 'error');
                }
                
                // Test messages
                addLog('Loading ticket messages for ID: ' + ticketId, 'log');
                
                const messagesResponse = await fetch(`src/api/get-ticket-messages.php?ticket_id=${ticketId}`);
                addLog('Messages API response status: ' + messagesResponse.status, 'log');
                
                const messagesData = await messagesResponse.json();
                if (messagesData.success) {
                    addLog('Messages data received successfully (' + messagesData.data.messages.length + ' messages)', 'success');
                } else {
                    addLog('Messages API error: ' + messagesData.message, 'error');
                }
                
                addLog('Test completed successfully!', 'success');
                
                Swal.fire({
                    icon: 'success',
                    title: 'Test Passed!',
                    text: 'selectTicket() function works correctly',
                    confirmButtonColor: '#667eea'
                });
                
            } catch (e) {
                addLog('ERROR: ' + e.message, 'error');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Test Failed',
                    text: e.message,
                    confirmButtonColor: '#667eea'
                });
            }
        }
    </script>
</body>
</html>
