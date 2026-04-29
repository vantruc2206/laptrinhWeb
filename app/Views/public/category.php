<?php require_once __DIR__ . '/layouts/header.php'; ?>


<div class="container" style="margin-bottom: 2rem;">
    <h1 style="font-size: 2.5rem; color: var(--primary); margin-bottom: 0.5rem;"><?= htmlspecialchars($category['name']) ?></h1>
    <?php if ($category['description']): ?>
        <p style="color: var(--text-muted); font-size: 1.125rem;"><?= nl2br(htmlspecialchars($category['description'])) ?></p>
    <?php endif; ?>
    <hr style="margin-top: 2rem; border: 0; border-top: 1px solid #E5E7EB;">
</div>

<?php if (empty($articles)): ?>
    <div class="container" style="text-align:center; padding: 2rem 0;">
        <p>Chưa có bài viết nào trong danh mục này.</p>
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

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
