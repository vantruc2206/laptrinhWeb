<?php
require_once __DIR__ . '/includes/config.php';
$new_hash = password_hash('admin123', PASSWORD_DEFAULT);
$pdo->exec("UPDATE users SET password = '$new_hash' WHERE username = 'admin'");
echo "Password updated successfully.";
