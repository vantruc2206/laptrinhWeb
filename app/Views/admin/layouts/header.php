<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng điều khiển Admin - Website Tin tức</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= time() ?>">
</head>
<body class="bg-light">
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
                <div style="display: flex; align-items: center; gap: 15px;">

                    <h1 style="margin: 0;">Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
                </div>
                <div class="admin-user">
                    <span>Trang quản trị</span>
                </div>
            </header>
