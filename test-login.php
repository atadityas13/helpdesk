<?php
/**
 * Test Login Functionality
 * Untuk test dan debug login issues
 */

require_once 'src/config/database.php';
require_once 'src/middleware/session.php';
require_once 'src/middleware/auth.php';
require_once 'src/helpers/functions.php';

// Start session
initSession();

// Test info
$testInfo = [
    'php_version' => phpversion(),
    'server_time' => date('Y-m-d H:i:s'),
    'session_id' => session_id(),
    'database_status' => 'Testing...',
    'admin_count' => 0,
    'test_login' => null
];

// Test database connection
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $testInfo['database_status'] = 'Connected ✓';
    
    // Count admins
    $result = $conn->query('SELECT COUNT(*) as count FROM admins');
    $row = $result->fetch_assoc();
    $testInfo['admin_count'] = $row['count'];
    
    // Get admin list
    $adminList = [];
    $result = $conn->query('SELECT id, username, email, is_active FROM admins');
    while ($row = $result->fetch_assoc()) {
        $adminList[] = $row;
    }
    
    // Test login dengan default credentials
    $testResult = authenticateAdmin('admin', 'admin123');
    $testInfo['test_login'] = [
        'identifier' => 'admin',
        'password' => 'admin123',
        'success' => $testResult['success'],
        'message' => $testResult['message'],
        'admin_id' => $testResult['admin_id']
    ];
    
} catch (Exception $e) {
    $testInfo['database_status'] = 'ERROR: ' . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Login - Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            padding: 40px 20px;
        }
        .container {
            max-width: 900px;
        }
        .test-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .status-ok {
            color: #10b981;
            font-weight: 600;
        }
        .status-error {
            color: #ef4444;
            font-weight: 600;
        }
        .status-info {
            color: #3b82f6;
            font-weight: 600;
        }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        table {
            margin-top: 15px;
        }
        th {
            background: #f3f4f6;
            border-bottom: 2px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="mb-4">
            <h1><i class="fas fa-flask me-2"></i>Test Login System</h1>
            <p class="text-muted">Diagnostik lengkap sistem login helpdesk</p>
        </div>

        <!-- System Info -->
        <div class="test-section">
            <h3><i class="fas fa-server me-2"></i>System Information</h3>
            <div class="row mt-3">
                <div class="col-md-6">
                    <p><strong>PHP Version:</strong> <code><?php echo $testInfo['php_version']; ?></code></p>
                    <p><strong>Server Time:</strong> <?php echo $testInfo['server_time']; ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Session ID:</strong> <code><?php echo substr(session_id(), 0, 20); ?>...</code></p>
                    <p><strong>Database Status:</strong> 
                        <span class="<?php echo strpos($testInfo['database_status'], 'Connected') !== false ? 'status-ok' : 'status-error'; ?>">
                            <?php echo $testInfo['database_status']; ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Database Check -->
        <div class="test-section">
            <h3><i class="fas fa-database me-2"></i>Database Check</h3>
            <?php if ($testInfo['admin_count'] > 0): ?>
                <p><strong>Admin Accounts Found:</strong> <span class="status-ok"><?php echo $testInfo['admin_count']; ?> ✓</span></p>
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($adminList as $admin): ?>
                        <tr>
                            <td><?php echo $admin['id']; ?></td>
                            <td><code><?php echo htmlspecialchars($admin['username']); ?></code></td>
                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                            <td>
                                <span class="<?php echo $admin['is_active'] ? 'status-ok' : 'status-error'; ?>">
                                    <?php echo $admin['is_active'] ? 'Active ✓' : 'Inactive ✗'; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><span class="status-error">❌ No admin accounts found!</span></p>
                <p class="mt-2">
                    <strong>Need to create admin account?</strong><br>
                    Run this SQL:
                    <pre><code>INSERT INTO admins (username, password, email, role, is_active)
VALUES ('admin', '$2y$10$KIX6ByqXIIeG4t8hY7r7lOMuaYD3.8BGDKZ4fY.nRJXhvzfSn6rtu', 'admin@helpdesk.local', 'admin', 1);</code></pre>
                    Password: <code>admin123</code>
                </p>
            <?php endif; ?>
        </div>

        <!-- Login Test -->
        <div class="test-section">
            <h3><i class="fas fa-sign-in-alt me-2"></i>Login Test - Default Credentials</h3>
            <?php if ($testInfo['test_login']): ?>
                <table class="table table-sm">
                    <tr>
                        <th style="width: 150px;">Identifier</th>
                        <td><code><?php echo $testInfo['test_login']['identifier']; ?></code></td>
                    </tr>
                    <tr>
                        <th>Password</th>
                        <td><code><?php echo $testInfo['test_login']['password']; ?></code></td>
                    </tr>
                    <tr>
                        <th>Result</th>
                        <td>
                            <span class="<?php echo $testInfo['test_login']['success'] ? 'status-ok' : 'status-error'; ?>">
                                <?php echo $testInfo['test_login']['success'] ? '✓ SUCCESS' : '✗ FAILED'; ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Message</th>
                        <td><?php echo $testInfo['test_login']['message']; ?></td>
                    </tr>
                    <tr>
                        <th>Admin ID</th>
                        <td><?php echo $testInfo['test_login']['admin_id'] ?? 'N/A'; ?></td>
                    </tr>
                </table>
                
                <?php if ($testInfo['test_login']['success']): ?>
                    <div class="alert alert-success mt-3">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Login function is working!</strong> You can now try logging in at <a href="login.php" class="alert-link">login.php</a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger mt-3">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Login test failed!</strong> Message: <?php echo $testInfo['test_login']['message']; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Manual Test -->
        <div class="test-section">
            <h3><i class="fas fa-keyboard me-2"></i>Manual Login Test</h3>
            <p class="mb-3">Test dengan email atau username:</p>
            <form method="POST" id="testForm">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" class="form-control mb-3" id="identifier" placeholder="Email atau Username" value="admin">
                    </div>
                    <div class="col-md-6">
                        <input type="password" class="form-control mb-3" id="password" placeholder="Password" value="admin123">
                    </div>
                </div>
                <button type="button" class="btn btn-primary" onclick="testLogin()">
                    <i class="fas fa-check me-2"></i>Test Login
                </button>
            </form>
            <div id="testResult" class="mt-3"></div>
        </div>

        <!-- Troubleshooting -->
        <div class="test-section bg-warning bg-opacity-10">
            <h3><i class="fas fa-wrench me-2"></i>Troubleshooting</h3>
            <div class="accordion" id="troubleshoot">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#trouble1">
                            <i class="fas fa-question-circle me-2"></i>Login tidak bisa, password salah?
                        </button>
                    </h2>
                    <div id="trouble1" class="accordion-collapse collapse" data-bs-parent="#troubleshoot">
                        <div class="accordion-body">
                            <p>Coba reset password admin dengan command:</p>
                            <pre><code>php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"</code></pre>
                            <p>Kemudian update database dengan hash yang dihasilkan.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#trouble2">
                            <i class="fas fa-question-circle me-2"></i>Tidak ada admin account?
                        </button>
                    </h2>
                    <div id="trouble2" class="accordion-collapse collapse" data-bs-parent="#troubleshoot">
                        <div class="accordion-body">
                            <p>Buat admin baru dengan SQL query di atas. Atau gunakan script creator admin.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#trouble3">
                            <i class="fas fa-question-circle me-2"></i>Koneksi database gagal?
                        </button>
                    </h2>
                    <div id="trouble3" class="accordion-collapse collapse" data-bs-parent="#troubleshoot">
                        <div class="accordion-body">
                            <p>Check file <code>.env</code> atau <code>src/config/.env.php</code></p>
                            <p>Verifikasi:</p>
                            <ul>
                                <li>Database host/user/password benar</li>
                                <li>Database server running</li>
                                <li>Database mtsnmaja_helpdesk sudah di-create</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Next Steps:</strong>
            <ol class="mb-0 mt-2">
                <li>Jika test login berhasil, buka <a href="login.php" class="alert-link">login.php</a></li>
                <li>Gunakan credentials yang sudah di-test di atas</li>
                <li>Jika masih gagal, periksa browser console (F12 → Console)</li>
                <li>Check network tab untuk lihat response dari server</li>
            </ol>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function testLogin() {
            const identifier = document.getElementById('identifier').value;
            const password = document.getElementById('password').value;
            const resultDiv = document.getElementById('testResult');
            
            if (!identifier || !password) {
                resultDiv.innerHTML = '<div class="alert alert-warning">Isi email/username dan password</div>';
                return;
            }
            
            resultDiv.innerHTML = '<div class="alert alert-info">Testing...</div>';
            
            // Simulate the login endpoint
            fetch('src/api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'email=' + encodeURIComponent(identifier) + '&password=' + encodeURIComponent(password)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Login Berhasil!</strong>
                            <p class="mb-0 mt-2">Admin ID: <code>${data.admin_id}</code></p>
                            <p class="mb-0">Message: ${data.message}</p>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Login Gagal!</strong>
                            <p class="mb-0 mt-2">${data.message}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Error:</strong>
                        <p class="mb-0 mt-2">${error.message}</p>
                    </div>
                `;
            });
        }
    </script>
</body>
</html>
