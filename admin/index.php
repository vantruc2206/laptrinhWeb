<?php
require_once 'includes/header.php';

// Fetch statistics
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$categoryCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$articleCount = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3>Tổng số người dùng</h3>
            <div class="stat-value"><?= $userCount ?></div>
        </div>
        <div class="stat-icon">👤</div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>Danh mục</h3>
            <div class="stat-value"><?= $categoryCount ?></div>
        </div>
        <div class="stat-icon">📁</div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>Bài viết</h3>
            <div class="stat-value"><?= $articleCount ?></div>
        </div>
        <div class="stat-icon">📄</div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h2>Bài viết gần đây</h2>
        <a href="articles.php" class="btn btn-primary">Quản lý Bài viết</a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Tiêu đề</th>
                    <th>Danh mục</th>
                    <th>Tác giả</th>
                    <th>Ngày tạo</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("
                    SELECT a.title, a.created_at, a.status, c.name as category, u.username as author 
                    FROM articles a 
                    LEFT JOIN categories c ON a.category_id = c.id 
                    LEFT JOIN users u ON a.author_id = u.id 
                    ORDER BY a.created_at DESC LIMIT 5
                ");
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= htmlspecialchars($row['author']) ?></td>
                    <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                    <td>
                        <span class="badge <?= $row['status'] == 'published' ? 'badge-success' : 'badge-warning' ?>">
                            <?= $row['status'] == 'published' ? 'Đã xuất bản' : 'Bản nháp' ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>