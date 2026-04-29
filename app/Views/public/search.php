<?php require_once __DIR__ . '/layouts/header.php'; ?>


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
                        <p class="article-summary"><?= htmlspecialchars($art['summary']) ?></p>
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

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
