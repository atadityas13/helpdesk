<?php
// Generate password hash untuk password123
$password = 'password123';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
echo $hash;
?>
