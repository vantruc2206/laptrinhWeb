<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NewsDaily - Modern News Portal</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-top">
        <div class="container">
            <div class="nav-left">
                <a href="index.php" class="navbar-brand">BÁO HƠI MỚI</a>

                <div class="weather-widget">
                    <div class="location-info">
                        <span class="city">TP HCM</span>
                        <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                        <div class="location-dropdown">
                            <div class="dropdown-search">
                                <i class="fas fa-search"></i>
                                <input type="text" placeholder="Tìm địa điểm" id="city-search-input">
                            </div>
                            <div class="city-list-wrapper">
                                <div class="city-list" id="full-city-list">
                                    <div class="loading-cities" style="padding: 10px; font-size: 0.8rem; color: #94a3b8;">
                                        <i class="fas fa-spinner fa-spin"></i> Đang tải danh sách...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="weather-detail">
                        <i class="fas fa-cloud-sun"></i>
                        <span class="temp">34°</span>
                    </div>
                    <div class="current-date">
                        <?php
                        $days = ["Chủ nhật","Thứ hai","Thứ ba","Thứ tư","Thứ năm","Thứ sáu","Thứ bảy"];
                        echo $days[date('w')] . ", " . date('d/m/Y');
                        ?>
                    </div>
                </div>
            </div>

            <form action="search.php" method="GET" class="search-bar">
                <input type="text" name="search" placeholder="Tìm kiếm tin tức..." required>
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>

            <div class="nav-auth">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="admin/index.php" class="admin-link">Trang quản trị</a>
                    <?php endif; ?>
                    <a href="auth/logout.php" class="logout-link">Đăng xuất</a>
                <?php else: ?>
                    <a href="auth/login.php" class="login-link">Đăng nhập</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="navbar-bottom" id="navMenu">
        <div class="container">
            <div class="category-nav">
                <a href="index.php" class="nav-home"><i class="fas fa-home"></i></a>
                <div class="nav-cats">
                    <?php
                    use App\Models\CategoryModel;
                    $categoryModel  = new CategoryModel();
                    $allCategories  = $categoryModel->getAllCategories();
                    $limit          = 10;
                    $mainCategories = array_slice($allCategories, 0, $limit);
                    $moreCategories = array_slice($allCategories, $limit);
                    ?>
                    <?php foreach ($mainCategories as $cat): ?>
                        <a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></a>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($moreCategories)): ?>
                    <div class="dropdown">
                        <input type="checkbox" id="nav-dropdown-check" class="dropdown-check">
                        <label for="nav-dropdown-check" class="dropdown-toggle">
                            <i class="fas fa-bars"></i>
                        </label>
                        <div class="dropdown-menu">
                            <?php foreach ($moreCategories as $cat): ?>
                                <a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main class="container" style="min-height: calc(100vh - 200px); padding-top: 2rem; padding-bottom: 2rem;">
