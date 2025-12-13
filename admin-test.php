<?php
/**
 * Admin Login Test
 * Untuk test login dan akses admin
 */

require_once 'src/config/.env.php';
require_once 'src/config/database.php';
require_once 'src/middleware/session.php';
require_once 'src/helpers/functions.php';

// Initialize session
initSession();

// Check if logged in
$isLoggedIn = isAdminLoggedIn();
$adminUsername = getAdminUsername();
$adminId = getAdminId();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Admin Access</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 600px;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .status-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            margin: 10px 0;
        }
        .status-ok {
            background: #d4edda;
            color: #155724;
        }
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Admin Access Test</h1>
        <hr>
        
        <h3>Session Status</h3>
        <?php if ($isLoggedIn): ?>
            <div class="status-badge status-ok">✅ LOGGED IN</div>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($adminUsername); ?></p>
            <p><strong>Admin ID:</strong> <?php echo $adminId; ?></p>
        <?php else: ?>
            <div class="status-badge status-error">❌ NOT LOGGED IN</div>
            <p>Anda harus login terlebih dahulu</p>
            <a href="login.php" class="btn btn-primary mt-3">Go to Login</a>
        <?php endif; ?>
        
        <h3 class="mt-5">Database Connection</h3>
        <?php
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            // Test query
            $result = $db->query("SELECT COUNT(*) as ticket_count FROM tickets");
            if ($result) {
                $row = $result->fetch_assoc();
                echo '<div class="status-badge status-ok">✅ DATABASE OK</div>';
                echo '<p>Total Tickets: ' . $row['ticket_count'] . '</p>';
            } else {
                echo '<div class="status-badge status-error">❌ QUERY ERROR</div>';
            }
        } catch (Exception $e) {
            echo '<div class="status-badge status-error">❌ CONNECTION ERROR</div>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
        
        <h3 class="mt-5">API Endpoints</h3>
        <div id="apiTests"></div>
        
        <div class="mt-5">
            <?php if ($isLoggedIn): ?>
                <a href="src/admin/manage-tickets.php" class="btn btn-success btn-lg">
                    Go to Manage Tickets →
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Test API endpoints
        async function testAPIs() {
            const container = document.getElementById('apiTests');
            const apis = [
                'src/api/get-ticket.php?id=1',
                'src/api/get-ticket-messages.php?ticket_id=1',
                'src/api/get-customer-messages.php?ticket_id=1'
            ];
            
            for (const api of apis) {
                try {
                    const response = await fetch(api);
                    const status = response.status;
                    const isOK = status === 200 || status === 400;
                    const badge = isOK ? 'status-ok' : 'status-error';
                    const icon = isOK ? '✅' : '❌';
                    
                    const div = document.createElement('div');
                    div.className = `status-badge ${badge}`;
                    div.textContent = `${icon} ${api} (${status})`;
                    container.appendChild(div);
                } catch (e) {
                    const div = document.createElement('div');
                    div.className = 'status-badge status-error';
                    div.textContent = `❌ ${api} - ${e.message}`;
                    container.appendChild(div);
                }
            }
        }
        
        testAPIs();
    </script>
</body>
</html>
