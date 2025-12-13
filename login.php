<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #5568d3;
            --secondary: #764ba2;
        }

        body {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .login-header h1 {
            font-size: 2em;
            font-weight: 800;
            margin-bottom: 10px;
            margin: 0 0 10px 0;
        }

        .login-header p {
            opacity: 0.95;
            margin: 0;
            font-size: 0.95em;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            color: #1f2937;
            font-weight: 600;
            font-size: 0.95em;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.95em;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: #6b7280;
            font-size: 0.9em;
        }

        .remember-me input {
            margin-right: 8px;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
            padding: 12px 16px;
            font-size: 0.9em;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #991b1b;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #065f46;
        }

        .login-footer {
            text-align: center;
            padding: 20px 30px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 0.9em;
        }

        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: #d1d5db;
            font-size: 0.9em;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            margin: 0 10px;
            color: #9ca3af;
        }

        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1><i class="fas fa-lock me-2"></i>Admin Login</h1>
                <p>Helpdesk MTsN 11 Majalengka</p>
            </div>

            <div class="login-body">
                <?php
                // Kecil kemungkinan ada session atau error messages
                if (isset($_GET['error'])) {
                    echo '<div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>' . htmlspecialchars($_GET['error']) . '
                    </div>';
                }
                if (isset($_GET['success'])) {
                    echo '<div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>' . htmlspecialchars($_GET['success']) . '
                    </div>';
                }
                ?>

                <form id="loginForm" method="POST" action="src/api/login.php">
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-2"></i>Email Admin
                        </label>
                        <input 
                            type="email" 
                            class="form-control" 
                            id="email" 
                            name="email" 
                            placeholder="admin@example.com"
                            required
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-key me-2"></i>Password
                        </label>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            placeholder="Masukkan password"
                            required
                        >
                    </div>

                    <div class="remember-me">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            name="remember" 
                            value="1"
                        >
                        <label for="remember" style="margin: 0; cursor: pointer;">
                            Ingat saya
                        </label>
                    </div>

                    <button type="submit" class="btn-login" id="loginBtn">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>

                <div class="divider">
                    <span>atau</span>
                </div>

                <p style="text-align: center; color: #6b7280; margin: 20px 0 0 0; font-size: 0.9em;">
                    <i class="fas fa-info-circle me-1"></i>Hanya untuk admin. <br>
                    <a href="index.php" style="color: var(--primary);">Kembali ke halaman utama</a>
                </p>
            </div>

            <div class="login-footer">
                <p style="margin: 0;">
                    <i class="fas fa-shield-alt me-2"></i>Area ini terlindungi. <br>
                    Akses tidak sah akan dilog dan dilaporkan.
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('loginBtn');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<span class="loading"></span> Verifying...';
            
            const formData = new FormData(this);
            
            fetch('src/api/login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Login Berhasil!',
                        text: 'Mengarahkan ke dashboard...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                            setTimeout(() => {
                                window.location.href = 'src/admin/dashboard.php';
                            }, 1500);
                        }
                    });
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Gagal',
                        text: data.message || 'Email atau password salah. Silakan coba lagi.',
                        confirmButtonColor: '#667eea'
                    });
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                console.error('Error:', error);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan',
                    text: 'Terjadi kesalahan saat login. Silakan coba lagi.',
                    confirmButtonColor: '#667eea'
                });
            });
        });

        // Remove error messages on input
        document.getElementById('email').addEventListener('focus', function() {
            document.querySelectorAll('.alert').forEach(alert => alert.remove());
        });
    </script>
</body>
</html>
