<?php require_once __DIR__ . '/layouts/header.php'; ?>

<?php if (empty($articles)): ?>
    <div style='text-align:center; padding: 4rem 0;'><h2>Không có bài viết nào mới. Vui lòng quay lại sau!</h2></div>
<?php else: ?>

    <!-- Featured Article Hero -->
    <div class="article-card" style="display: flex; flex-direction: row; margin-bottom: 3rem; background: var(--dark); color: var(--white);">
        <div style="flex: 1; max-width: 50%;">
            <?php if ($featured['image']): ?>
                <img src="<?= $uploadPath . htmlspecialchars($featured['image']) ?>" alt="<?= htmlspecialchars($featured['title']) ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-lg) 0 0 var(--radius-lg);">
            <?php else: ?>
                <div style="width: 100%; height: 100%; background: #374151; border-radius: var(--radius-lg) 0 0 var(--radius-lg); min-height: 350px;"></div>
            <?php endif; ?>
        </div>
        <div style="flex: 1; padding: 3rem; display: flex; flex-direction: column; justify-content: center;">
            <span class="article-category" style="color: var(--secondary);"><?= htmlspecialchars($featured['category_name']) ?></span>
            <h1 style="color: var(--white); font-size: 2.25rem; margin-bottom: 1rem;">
                <a href="article.php?slug=<?= htmlspecialchars($featured['slug']) ?>" style="color: var(--white); text-decoration: none;">
                    <?= htmlspecialchars($featured['title']) ?>
                </a>
            </h1>
            <p style="color: #9CA3AF; margin-bottom: 2rem; font-size: 1.125rem;"><?= htmlspecialchars($featured['summary']) ?></p>
            <div class="article-meta" style="border-top: none; color: #6B7280;">
                <span>Bởi <?= htmlspecialchars($featured['author_name']) ?></span>
                <span><?= date('d/m/Y', strtotime($featured['created_at'])) ?> &middot; <?= $featured['views'] ?> lượt xem</span>
            </div>
        </div>
    </div>

    <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center;">
        Tin tức mới nhất
        <span style="height: 2px; flex-grow: 1; background: #E5E7EB; margin-left: 1rem;"></span>
    </h2>

    <!-- Regular Articles Grid -->
    <div class="articles-grid">
        <?php foreach ($regularArticles as $art): ?>
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

<?php require_once __DIR__ . '/layouts/footer.php'; ?>

