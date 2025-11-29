<?php
/**
 * Generate Fresh Bcrypt Hash untuk password123
 * Copy hash ini dan gunakan di phpMyAdmin
 */

$password = 'password123';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

// Verify hash works
$isValid = password_verify($password, $hash);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Generate Hash untuk phpMyAdmin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            text-align: center;
        }
        .hash-box {
            background: #2d2d2d;
            color: #00ff00;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            line-height: 1.6;
        }
        .copy-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin: 10px 0;
        }
        .copy-btn:hover {
            background: #764ba2;
        }
        .success-msg {
            display: none;
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            text-align: center;
        }
        .status {
            text-align: center;
            margin: 20px 0;
        }
        .status.valid {
            color: green;
            font-weight: bold;
        }
        .status.invalid {
            color: red;
            font-weight: bold;
        }
        .instruction {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 3px;
        }
        .sql-command {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 3px;
        }
        .sql-command code {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 3px;
            display: block;
            word-break: break-all;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Generate Hash untuk phpMyAdmin</h1>
        
        <div class="status <?php echo $isValid ? 'valid' : 'invalid'; ?>">
            Status: <?php echo $isValid ? '✅ HASH VALID' : '❌ HASH INVALID'; ?>
        </div>

        <div class="instruction">
            <strong>Password:</strong> <code>password123</code>
        </div>

        <h2>Generated Hash:</h2>
        <div class="hash-box" id="hashBox"><?php echo $hash; ?></div>
        
        <button class="copy-btn" onclick="copyHash()">📋 Copy Hash</button>
        <div class="success-msg" id="successMsg">✅ Hash copied to clipboard!</div>

        <div class="sql-command">
            <strong>SQL Command untuk phpMyAdmin:</strong>
            <code id="sqlCommand">UPDATE admins SET password = '<?php echo $hash; ?>' WHERE username = 'admin';</code>
            <button class="copy-btn" onclick="copySql()">📋 Copy SQL Command</button>
        </div>

        <div class="instruction">
            <h3>📋 Cara Menggunakan di phpMyAdmin:</h3>
            <ol>
                <li>Buka phpMyAdmin</li>
                <li>Pilih database: <strong>mtsnmaja_helpdesk</strong></li>
                <li>Klik tab <strong>SQL</strong></li>
                <li>Paste SQL Command di atas</li>
                <li>Klik tombol <strong>GO</strong></li>
                <li>Selesai! Coba login dengan username: <strong>admin</strong>, password: <strong>password123</strong></li>
            </ol>
        </div>
    </div>

    <script>
        function copyHash() {
            const hash = document.getElementById('hashBox').textContent;
            navigator.clipboard.writeText(hash).then(() => {
                document.getElementById('successMsg').style.display = 'block';
                setTimeout(() => {
                    document.getElementById('successMsg').style.display = 'none';
                }, 3000);
            });
        }

        function copySql() {
            const sql = document.getElementById('sqlCommand').textContent;
            navigator.clipboard.writeText(sql).then(() => {
                document.getElementById('successMsg').textContent = '✅ SQL Command copied!';
                document.getElementById('successMsg').style.display = 'block';
                setTimeout(() => {
                    document.getElementById('successMsg').style.display = 'none';
                }, 3000);
            });
        }
    </script>
</body>
</html>
