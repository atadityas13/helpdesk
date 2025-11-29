<?php
/**
 * Simple Hash Generator
 * Just generate a valid hash for password123
 */

$password = 'password123';
$hash = password_hash($password, PASSWORD_BCRYPT);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Hash Generator</title>
    <style>
        body { font-family: monospace; padding: 20px; }
        .box { background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 10px 0; }
        code { background: #ddd; padding: 5px 10px; border-radius: 3px; }
        .success { color: green; font-weight: bold; }
        .sql { background: #ffe; padding: 10px; border: 1px solid #cc0; }
    </style>
</head>
<body>
    <h1>🔐 Password Hash Generator</h1>
    
    <div class="box">
        <strong>Password:</strong> password123<br>
        <strong>Generated Hash:</strong><br>
        <code><?php echo $hash; ?></code>
    </div>

    <div class="box">
        <strong>Verify:</strong><br>
        <?php 
        $verify = password_verify($password, $hash);
        echo '<span class="' . ($verify ? 'success' : 'error') . '">';
        echo $verify ? '✅ VALID' : '❌ INVALID';
        echo '</span>';
        ?>
    </div>

    <div class="sql">
        <strong>Run this SQL command to update database:</strong><br><br>
        <code>
        UPDATE admins SET password = '<?php echo $hash; ?>' WHERE username = 'admin';
        </code><br><br>
        Or in phpMyAdmin, go to admins table, find admin user, and update password field with above hash.
    </div>

    <div class="box">
        <strong>After updating, you can login with:</strong><br>
        Username: <code>admin</code><br>
        Password: <code>password123</code>
    </div>
</body>
</html>
