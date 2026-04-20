<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NewsDaily - Modern News Portal</title>
    <link rel="stylesheet" href="assets/css/style.css?v=1.2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a href="index.php" class="navbar-brand">Báo hơi mới</a>
        
        <div class="nav-links">
            <a href="index.php">Trang chủ</a>
            <?php 
            // Fetch all categories
            $allCategories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
            $limit = 5;
            $mainCategories = array_slice($allCategories, 0, $limit);
            $moreCategories = array_slice($allCategories, $limit);
            ?>

            <?php foreach($mainCategories as $cat): ?>
                <a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></a>
            <?php endforeach; ?>

            <?php if(!empty($moreCategories)): ?>
                <div class="dropdown">
                    <input type="checkbox" id="nav-dropdown-check" class="dropdown-check">
                    <label for="nav-dropdown-check" class="dropdown-toggle">
                        Xem thêm <i class="fas fa-chevron-down" style="font-size: 0.8rem; margin-left: 4px;"></i>
                    </label>
                    <div class="dropdown-menu">
                        <?php foreach($moreCategories as $cat): ?>
                            <a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if(isLoggedIn()): ?>
                <?php if(isAdmin()): ?>
                    <a href="admin/index.php" style="color: var(--primary);">Trang quản trị</a>
                <?php else: ?>
                    <a href="auth/logout.php" class="btn btn-secondary" style="padding: 0.25rem 0.75rem;">Đăng xuất</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="auth/login.php" class="btn btn-primary" style="padding: 0.25rem 0.75rem; color: #fff;">Đăng nhập</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container" style="min-height: calc(100vh - 200px); padding-top: 2rem; padding-bottom: 2rem;">
