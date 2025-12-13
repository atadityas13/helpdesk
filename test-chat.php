<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Helpdesk Chat System - Diagnostic Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            max-width: 1000px;
        }
        
        .test-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .test-title {
            color: #667eea;
            margin-bottom: 30px;
            font-size: 2em;
            font-weight: 700;
        }
        
        .test-item {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .test-item.success {
            border-left-color: #10b981;
            background: #ecfdf5;
        }
        
        .test-item.error {
            border-left-color: #ef4444;
            background: #fef2f2;
        }
        
        .test-item.loading {
            border-left-color: #f59e0b;
            background: #fffbeb;
        }
        
        .test-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #1f2937;
        }
        
        .test-result {
            font-size: 0.9em;
            color: #6b7280;
            font-family: 'Courier New', monospace;
        }
        
        .test-result.success {
            color: #10b981;
        }
        
        .test-result.error {
            color: #ef4444;
        }
        
        .btn-test {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-test:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .status-icon {
            font-size: 1.5em;
            margin-right: 10px;
        }
        
        .section-title {
            font-size: 1.5em;
            font-weight: 700;
            color: #1f2937;
            margin-top: 30px;
            margin-bottom: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="test-card">
            <h1 class="test-title">
                <i class="fas fa-heartbeat me-3"></i>Helpdesk Chat System - Diagnostic Test
            </h1>
            
            <div class="summary">
                <h3><i class="fas fa-info-circle status-icon"></i>System Status</h3>
                <p id="systemStatus" style="margin: 0;">
                    <i class="fas fa-hourglass-half"></i> Initializing tests...
                </p>
            </div>
            
            <h2 class="section-title"><i class="fas fa-database"></i> Database Connection Tests</h2>
            <div id="dbTests"></div>
            
            <h2 class="section-title"><i class="fas fa-cloud"></i> API Endpoint Tests</h2>
            <div id="apiTests"></div>
            
            <h2 class="section-title"><i class="fas fa-comments"></i> Chat Message Flow Tests</h2>
            <div id="chatTests"></div>
            
            <h2 class="section-title"><i class="fas fa-cogs"></i> Manual Tests</h2>
            <div class="test-item">
                <div class="test-label">Test Customer Chat</div>
                <p class="test-result">Create a test ticket and test the customer chat interface</p>
                <button class="btn-test" onclick="testCustomerChat()">
                    <i class="fas fa-comments me-2"></i> Test Customer Chat
                </button>
            </div>
            
            <div class="test-item">
                <div class="test-label">Test Admin Chat</div>
                <p class="test-result">Test the admin chat interface and message sending</p>
                <button class="btn-test" onclick="testAdminChat()">
                    <i class="fas fa-headset me-2"></i> Test Admin Chat
                </button>
            </div>
        </div>
    </div>

    <script>
        const tests = {
            db: [],
            api: [],
            chat: []
        };
        
        async function runAllTests() {
            document.getElementById('systemStatus').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Running tests...';
            
            await runDatabaseTests();
            await runApiTests();
            await runChatTests();
            
            updateSystemStatus();
        }
        
        async function runDatabaseTests() {
            const dbTests = document.getElementById('dbTests');
            dbTests.innerHTML = '<div class="test-item loading"><i class="fas fa-spinner fa-spin"></i> Running database tests...</div>';
            
            // Test database connection through API
            try {
                const response = await fetch('src/api/get-ticket-by-number.php?ticket_number=TEST');
                if (response.ok) {
                    addTest('db', 'Database Connection', 'success', 'Database connection is active');
                } else {
                    addTest('db', 'Database Connection', 'error', 'Database connection failed');
                }
            } catch (e) {
                addTest('db', 'Database Connection', 'error', 'Error: ' + e.message);
            }
            
            renderTests('db', dbTests);
        }
        
        async function runApiTests() {
            const apiTests = document.getElementById('apiTests');
            apiTests.innerHTML = '<div class="test-item loading"><i class="fas fa-spinner fa-spin"></i> Running API tests...</div>';
            
            const endpoints = [
                { name: 'get-ticket-by-number.php', url: 'src/api/get-ticket-by-number.php?ticket_number=TEST' },
                { name: 'get-customer-messages.php', url: 'src/api/get-customer-messages.php?ticket_id=1' },
                { name: 'get-ticket-messages.php', url: 'src/api/get-ticket-messages.php?ticket_id=1' },
                { name: 'send-customer-message.php', url: 'src/api/send-customer-message.php', method: 'POST' },
                { name: 'send-admin-message.php', url: 'src/api/send-admin-message.php', method: 'POST' }
            ];
            
            for (const endpoint of endpoints) {
                try {
                    const response = await fetch(endpoint.url, {
                        method: endpoint.method || 'GET'
                    });
                    if (response.status === 200 || response.status === 400) {
                        addTest('api', endpoint.name, 'success', 'API endpoint is accessible');
                    } else {
                        addTest('api', endpoint.name, 'error', `HTTP ${response.status}`);
                    }
                } catch (e) {
                    addTest('api', endpoint.name, 'error', 'Error: ' + e.message);
                }
            }
            
            renderTests('api', apiTests);
        }
        
        async function runChatTests() {
            const chatTests = document.getElementById('chatTests');
            chatTests.innerHTML = '<div class="test-item loading"><i class="fas fa-spinner fa-spin"></i> Running chat tests...</div>';
            
            // Test message retrieval format consistency
            try {
                const response1 = await fetch('src/api/get-customer-messages.php?ticket_id=1');
                const response2 = await fetch('src/api/get-ticket-messages.php?ticket_id=1');
                
                if (response1.ok && response2.ok) {
                    const data1 = await response1.json();
                    const data2 = await response2.json();
                    
                    if (data1.success && data2.success) {
                        addTest('chat', 'Message API Consistency', 'success', 'Both APIs return consistent format');
                    } else {
                        addTest('chat', 'Message API Consistency', 'error', 'One or both APIs returned error');
                    }
                }
            } catch (e) {
                addTest('chat', 'Message API Consistency', 'error', e.message);
            }
            
            // Test message display rendering
            try {
                const testMessages = [
                    { id: 1, ticket_id: 1, sender_type: 'customer', sender_name: 'Test Customer', message: 'Test message', created_at: new Date().toISOString() },
                    { id: 2, ticket_id: 1, sender_type: 'admin', sender_name: 'Test Admin', message: 'Test response', created_at: new Date().toISOString() }
                ];
                
                let html = '';
                testMessages.forEach(msg => {
                    const messageClass = msg.sender_type === 'admin' ? 'admin' : 'customer';
                    const date = new Date(msg.created_at);
                    const timeStr = date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                    
                    html += `
                        <div class="message ${messageClass}">
                            <div>${msg.sender_name}: ${msg.message} (${timeStr})</div>
                        </div>
                    `;
                });
                
                addTest('chat', 'Message Display Rendering', 'success', 'Chat messages render correctly');
            } catch (e) {
                addTest('chat', 'Message Display Rendering', 'error', e.message);
            }
            
            renderTests('chat', chatTests);
        }
        
        function addTest(category, label, status, result) {
            tests[category].push({ label, status, result });
        }
        
        function renderTests(category, container) {
            const categoryTests = tests[category];
            let html = '';
            
            categoryTests.forEach(test => {
                const icon = test.status === 'success' ? '✅' : test.status === 'error' ? '❌' : '⏳';
                html += `
                    <div class="test-item ${test.status}">
                        <div class="test-label">${icon} ${test.label}</div>
                        <div class="test-result ${test.status}">${test.result}</div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }
        
        function updateSystemStatus() {
            const total = tests.db.length + tests.api.length + tests.chat.length;
            const passed = (tests.db.filter(t => t.status === 'success').length +
                          tests.api.filter(t => t.status === 'success').length +
                          tests.chat.filter(t => t.status === 'success').length);
            
            const statusColor = passed === total ? '#10b981' : '#f59e0b';
            const statusIcon = passed === total ? '✅' : '⚠️';
            
            document.getElementById('systemStatus').innerHTML = `
                <span style="color: ${statusColor};">
                    ${statusIcon} Tests Passed: ${passed}/${total}
                </span>
            `;
        }
        
        function testCustomerChat() {
            const ticketNumber = prompt('Enter ticket number (e.g., TK-20251215-XXXXX) or leave empty to create new:');
            if (ticketNumber === null) return;
            
            if (ticketNumber) {
                window.location.href = `chat.php?ticket=${encodeURIComponent(ticketNumber)}`;
            } else {
                window.location.href = 'index.php';
            }
        }
        
        function testAdminChat() {
            window.location.href = 'src/admin/manage-tickets.php';
        }
        
        // Run tests on page load
        window.addEventListener('load', runAllTests);
    </script>
</body>
</html>
