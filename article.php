<?php
require_once 'includes/header.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    redirect('index.php');
}

$stmt = $pdo->prepare("
    SELECT a.*, c.name as category_name, c.slug as category_slug, u.username as author_name
    FROM articles a
    LEFT JOIN categories c ON a.category_id = c.id
    LEFT JOIN users u ON a.author_id = u.id
    WHERE a.slug = ? AND a.status = 'published'
");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    echo "<div class='container' style='text-align:center; padding: 4rem 0;'><h2>Bài viết không tồn tại hoặc đã bị ẩn.</h2></div>";
    require_once 'includes/footer.php';
    exit;
}

$pdo->prepare("UPDATE articles SET views = views + 1 WHERE id = ?")->execute([$article['id']]);

$uploadPath = 'assets/images/articles/';
?>

<article class="container">
    <div class="article-header">
        <a href="category.php?slug=<?= htmlspecialchars($article['category_slug']) ?>" class="article-category" style="display: inline-block; margin-bottom: 1rem;">
            <?= htmlspecialchars($article['category_name']) ?>
        </a>
        <h1><?= htmlspecialchars($article['title']) ?></h1>
        <div style="color: var(--text-muted); font-size: 1rem; margin-top: 1rem; display: flex; justify-content: center; gap: 1rem;">
            <span>Bởi <strong><?= htmlspecialchars($article['author_name']) ?></strong></span>
            <span>&bull;</span>
            <span><?= date('d/m/Y', strtotime($article['created_at'])) ?></span>
            <span>&bull;</span>
            <span><?= $article['views'] + 1 ?> Lượt xem</span>
        </div>
    </div>

    <?php if ($article['image']): ?>
        <img src="<?= $uploadPath . htmlspecialchars($article['image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="article-hero-img">
    <?php endif; ?>

    <div class="article-body">
       
        <?= $article['content'] ?>
        
        <div style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid #E5E7EB; text-align: center;">
            <a href="index.php" class="btn btn-secondary">&larr; Quay lại Tin tức</a>
        </div>
    </div>
</article>

<?php require_once 'includes/footer.php'; ?>
