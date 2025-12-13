<?php
/**
 * Admin Login Page
 * Helpdesk MTsN 11 Majalengka
 */

require_once 'src/config/database.php';
require_once 'src/helpers/functions.php';
require_once 'src/middleware/session.php';
require_once 'src/middleware/auth.php';
require_once 'src/middleware/csrf.php';
require_once 'src/middleware/rate-limit.php';

// If already logged in, redirect to dashboard
if (isAdminLoggedIn()) {
    header('Location: src/admin/dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfRequest()) {
        $error = 'Permintaan tidak valid (CSRF token kadaluarsa). Silahkan coba lagi.';
    } else {
        // Check rate limit
        $clientIp = $_SERVER['REMOTE_ADDR'];
        checkRateLimit('login', $clientIp, $conn);
        
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Username dan password harus diisi';
        } else {
            if (verifyAdminPassword($conn, $username, $password)) {
                header('Location: src/admin/dashboard.php');
                exit;
            } else {
                $error = 'Username atau password salah';
            }
        }
    }
}

// Check for messages
$expired = isset($_GET['expired']);
$logged_out = isset($_GET['logged_out']);
?>
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Helpdesk MTsN 11 Majalengka</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #999;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-danger {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .alert-success {
            background: #efe;
            color: #363;
            border: 1px solid #cfc;
        }

        .alert-warning {
            background: #ffeaa7;
            color: #d97f04;
            border: 1px solid #fdcb6e;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🎓 Helpdesk</h1>
            <p>MTsN 11 Majalengka</p>
        </div>

        <?php if ($expired): ?>
            <div class="alert alert-warning">
                ⏰ Sesi Anda telah berakhir. Silahkan login kembali.
            </div>
        <?php endif; ?>

        <?php if ($logged_out): ?>
            <div class="alert alert-success">
                ✓ Anda telah berhasil logout.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                ✕ <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <?php echo getCsrfTokenField(); ?>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="login-footer">
            <p>© 2024 Helpdesk MTsN 11 Majalengka</p>
        </div>

        <div class="default-credentials">
            <strong>Default Credentials:</strong><br>
            Username: <code>admin</code><br>
            Password: <code>password123</code>
        </div>
    </div>
</body>
</html>
