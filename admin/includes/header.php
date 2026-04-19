<?php
require_once '../includes/functions.php';

// Ensure user is admin
if (!isAdmin()) {
    redirect('../index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng điều khiển Admin - Website Tin tức</title>
    <!-- We load base style first, then admin overrides -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h3>Quản trị Tin tức</h3>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="sidebar-nav-item">Bảng điều khiển</a>
                <a href="categories.php" class="sidebar-nav-item">Danh mục</a>
                <a href="articles.php" class="sidebar-nav-item">Bài viết</a>
                <a href="users.php" class="sidebar-nav-item">Người dùng</a>
                <a href="../index.php" class="sidebar-nav-item" target="_blank">Xem Website</a>
                <a href="../auth/logout.php" class="sidebar-nav-item text-danger">Đăng xuất</a>
            </nav>
        </aside>

        <!-- Main Content Wrapper -->
        <main class="admin-main">
            <header class="admin-header">
                <h1>Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
                <div class="admin-user">
                    <span>Trang quản trị</span>
                </div>
            </header>