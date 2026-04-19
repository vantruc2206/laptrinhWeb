<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Fetch Categories for Navbar
$navCategories = $pdo->query("SELECT * FROM categories ORDER BY name ASC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NewsDaily - Modern News Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a href="index.php" class="navbar-brand">NewsDaily.</a>
        
        <div class="nav-links">
            <a href="index.php">Trang chủ</a>
            <?php foreach($navCategories as $cat): ?>
                <a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></a>
            <?php endforeach; ?>
            
            <?php if(isLoggedIn()): ?>
                <?php if(isAdmin()): ?>
                    <a href="admin/index.php" style="color: var(--primary);">Trang quản trị</a>
                <?php endif; ?>
                <a href="auth/logout.php" class="btn btn-secondary" style="padding: 0.25rem 0.75rem;">Đăng xuất</a>
            <?php else: ?>
                <a href="auth/login.php" class="btn btn-primary" style="padding: 0.25rem 0.75rem; color: #fff;">Đăng nhập</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container" style="min-height: calc(100vh - 200px); padding-top: 2rem; padding-bottom: 2rem;">
