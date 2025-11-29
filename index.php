<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Example - Helpdesk Widget Integration</title>
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
            padding: 40px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }

        header h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }

        header p {
            font-size: 18px;
            opacity: 0.9;
        }

        .content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.5s ease forwards;
            opacity: 0;
        }

        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
            from {
                opacity: 0;
                transform: translateY(20px);
            }
        }

        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }

        .card h2 {
            color: #667eea;
            margin-bottom: 16px;
            font-size: 20px;
        }

        .card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .card-icon {
            font-size: 40px;
            margin-bottom: 16px;
        }

        .feature-list {
            list-style: none;
            margin: 16px 0;
        }

        .feature-list li {
            padding: 8px 0;
            color: #666;
            padding-left: 24px;
            position: relative;
        }

        .feature-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #667eea;
            font-weight: bold;
        }

        .demo-section {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .demo-section h2 {
            color: #333;
            margin-bottom: 24px;
            font-size: 24px;
        }

        .demo-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }

        .demo-box {
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
        }

        .demo-box h3 {
            color: #333;
            margin-bottom: 16px;
        }

        .demo-box code {
            display: block;
            background: #2c3e50;
            color: #ecf0f1;
            padding: 16px;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 12px;
        }

        .demo-box small {
            color: #999;
            font-size: 12px;
        }

        .links {
            display: flex;
            gap: 16px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #f0f0ff;
        }

        .footer {
            text-align: center;
            color: white;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 14px;
        }

        @media (max-width: 768px) {
            header h1 {
                font-size: 24px;
            }

            .demo-content {
                grid-template-columns: 1fr;
            }

            .content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🎓 Helpdesk MTsN 11 Majalengka</h1>
            <p>Sistem Support dengan Floating Button Widget</p>
        </header>

        <div class="content">
            <div class="card">
                <div class="card-icon">📱</div>
                <h2>User Side</h2>
                <p>Floating button widget yang dapat diakses dari mana saja untuk membuat ticket dan chat dengan support team.</p>
                <ul class="feature-list">
                    <li>Floating button selalu tersedia</li>
                    <li>Chat interface seperti WhatsApp</li>
                    <li>Generate nomor ticket otomatis</li>
                    <li>Resume chat dengan nomor ticket</li>
                </ul>
            </div>

            <div class="card">
                <div class="card-icon">👨‍💼</div>
                <h2>Admin Side</h2>
                <p>Dashboard lengkap untuk manajemen tickets dan komunikasi dengan customers secara real-time.</p>
                <ul class="feature-list">
                    <li>Dashboard dengan statistik</li>
                    <li>Manajemen tickets</li>
                    <li>Chat dengan customer</li>
                    <li>FAQ management</li>
                </ul>
            </div>

            <div class="card">
                <div class="card-icon">⚡</div>
                <h2>Teknologi</h2>
                <p>Built dengan teknologi standar web yang reliable dan mudah di-maintain.</p>
                <ul class="feature-list">
                    <li>PHP & MySQL</li>
                    <li>HTML & CSS</li>
                    <li>Vanilla JavaScript</li>
                    <li>RESTful API</li>
                </ul>
            </div>
        </div>

        <div class="demo-section">
            <h2>📝 Quick Start</h2>
            
            <div class="demo-content">
                <div class="demo-box">
                    <h3>1️⃣ Setup Database</h3>
                    <p>Import file SQL ke MySQL Anda:</p>
                    <code>mysql -u root helpdesk_mtsn11 < database.sql</code>
                    <small>Atau manual import melalui phpMyAdmin</small>
                </div>

                <div class="demo-box">
                    <h3>2️⃣ Konfigurasi</h3>
                    <p>Update database credentials di file:</p>
                    <code>src/config/database.php</code>
                    <small>Sesuaikan DB_HOST, DB_USER, DB_PASS</small>
                </div>

                <div class="demo-box">
                    <h3>3️⃣ Login Admin</h3>
                    <p>Akses admin dashboard:</p>
                    <code>http://localhost/helpdesk/login.php</code>
                    <small>User: admin | Pass: password123</small>
                </div>

                <div class="demo-box">
                    <h3>4️⃣ Integrasi Widget</h3>
                    <p>Tambahkan ke website Anda:</p>
                    <code>&lt;script src="/.../public/js/widget.js"&gt;&lt;/script&gt;</code>
                    <small>Letakkan sebelum closing &lt;/body&gt; tag</small>
                </div>
            </div>

            <div class="links">
                <a href="login.php" class="btn btn-primary">🔐 Login Admin</a>
                <a href="README.md" class="btn btn-secondary" download>📖 Download Documentation</a>
            </div>
        </div>

        <footer class="footer">
            <p>© 2024 Helpdesk MTsN 11 Majalengka | All Rights Reserved</p>
        </footer>
    </div>

    <!-- Helpdesk Widget Integration -->
    <!-- Uncomment untuk test widget:
    <script src="public/js/widget.js"></script>
    -->
</body>
</html>
