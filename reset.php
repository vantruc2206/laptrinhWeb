<?php
require_once __DIR__ . '/includes/config.php';

echo "<h3>Resetting Admin Password...</h3>";
try {
    $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = :hash WHERE username = 'admin'");
    $stmt->execute(['hash' => $new_hash]);
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green'>Success! The password for 'admin' has been reset to: <b>admin123</b></p>";
    } else {
        $check = $pdo->query("SELECT * FROM users WHERE username = 'admin'")->fetch();
        if ($check) {
            echo "<p style='color:green'>Success! The password was already up to date for 'admin'. Password is: <b>admin123</b></p>";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES ('admin', :hash, 'admin@example.com', 'admin')");
            $stmt->execute(['hash' => $new_hash]);
            echo "<p style='color:green'>Admin user was missing, recreated successfully! Password is: <b>admin123</b></p>";
        }
    }
    echo "<p><a href='auth/login.php'>Click here to login</a></p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>Database Error: " . $e->getMessage() . "</p>";
}
?>
