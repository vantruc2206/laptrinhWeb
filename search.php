<?php
require_once 'includes/header.php';

$query = isset($_GET['search']) ? trim($_GET['search']) : '';

if (empty($query)) {
    echo "<div class='container' style='text-align:center; padding: 4rem 0;'><h2>Vui lòng nhập từ khóa tìm kiếm!</h2></div>";
    require_once 'includes/footer.php';
    exit;
}

// Split query into individual words for more flexible search
$words = array_filter(array_map('trim', explode(' ', $query)));
$whereParts = [];
$params = [];

foreach ($words as $i => $word) {
    $whereParts[] = "(a.title LIKE :title$i OR a.summary LIKE :summary$i OR a.content LIKE :content$i)";
    $params["title$i"]   = "%$word%";
    $params["summary$i"] = "%$word%";
    $params["content$i"] = "%$word%";
}

$whereSql = count($whereParts) > 0 ? "AND " . implode(" AND ", $whereParts) : "";

// Fetch Articles matching search query
$sql = "
    SELECT a.*, c.name as category_name, c.slug as category_slug, u.username as author_name
    FROM articles a
    LEFT JOIN categories c ON a.category_id = c.id
    LEFT JOIN users u ON a.author_id = u.id
    WHERE a.status = 'published' 
    $whereSql
    ORDER BY a.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

$uploadPath = 'assets/images/articles/';
?>

<div class="container">
    <h1 style="font-size: 2rem; margin-bottom: 2rem;">Kết quả tìm kiếm cho: "<?= htmlspecialchars($query) ?>"</h1>

    <?php if (empty($articles)): ?>
        <div style="text-align:center; padding: 4rem 0;">
            <i class="fas fa-search" style="font-size: 4rem; color: #E5E7EB; margin-bottom: 1rem;"></i>
            <h2>Không tìm thấy bài viết nào phù hợp.</h2>
            <p style="color: var(--gray);">Hãy thử tìm kiếm với từ khóa khác.</p>
        </div>
    <?php else: ?>
        <div class="articles-grid">
            <?php foreach ($articles as $art): ?>
                <article class="article-card">
                    <a href="article.php?slug=<?= htmlspecialchars($art['slug']) ?>" style="display: block;">
                        <?php if ($art['image']): ?>
                            <img src="<?= $uploadPath . htmlspecialchars($art['image']) ?>" alt="<?= htmlspecialchars($art['title']) ?>" class="article-img">
                        <?php else: ?>
                            <div class="article-img" style="background: #E5E7EB;"></div>
                        <?php endif; ?>
                    </a>
                    <div class="article-content">
                        <a href="category.php?slug=<?= htmlspecialchars($art['category_slug']) ?>" class="article-category">
                            <?= htmlspecialchars($art['category_name']) ?>
                        </a>
                        <h3 class="article-title">
                            <a href="article.php?slug=<?= htmlspecialchars($art['slug']) ?>">
                                <?= htmlspecialchars($art['title']) ?>
                            </a>
                        </h3>
                        <p class="article-summary">
                            <?= htmlspecialchars($art['summary']) ?>
                        </p>
                        <div class="article-meta">
                            <span><?= htmlspecialchars($art['author_name']) ?></span>
                            <span><?= date('d/m/Y', strtotime($art['created_at'])) ?></span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
