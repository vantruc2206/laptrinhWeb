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

// Xử lý Sửa / Xóa bình luận
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if (isLoggedIn()) {
        $comment_id = (int)($_POST['comment_id'] ?? 0);
        $user_id = $_SESSION['user_id'];
        
        if ($_POST['action'] == 'delete_comment') {
            $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?")->execute([$comment_id, $user_id]);
            redirect("article.php?slug=" . urlencode($slug) . "&success=deleted");
        } elseif ($_POST['action'] == 'update_comment') {
            $new_content = sanitize($_POST['content'] ?? '');
            if (!empty($new_content)) {
                $pdo->prepare("UPDATE comments SET content = ? WHERE id = ? AND user_id = ?")->execute([$new_content, $comment_id, $user_id]);
                redirect("article.php?slug=" . urlencode($slug) . "&success=updated");
            }
        }
    }
}

// Xử lý thông báo
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'deleted') $commentSuccess = "Đã xóa bình luận thành công.";
    if ($_GET['success'] == 'updated') $commentSuccess = "Đã cập nhật bình luận thành công.";
}

// Xử lý gửi bình luận
$commentSuccess = "";
$commentError = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_comment'])) {
    if (!isLoggedIn()) {
        $commentError = "Bạn cần đăng nhập để bình luận.";
    } else {
        $content = sanitize($_POST['content']);
        $user_id = $_SESSION['user_id'];
        $article_id = $article['id'];

        if (empty($content)) {
            $commentError = "Nội dung bình luận không được để trống.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO comments (article_id, user_id, content) VALUES (?, ?, ?)");
            if ($stmt->execute([$article_id, $user_id, $content])) {
                $commentSuccess = "Bình luận đã được gửi!";
            } else {
                $commentError = "Có lỗi xảy ra, vui lòng thử lại.";
            }
        }
    }
}

// Lấy danh sách bình luận
$stmt = $pdo->prepare("
    SELECT c.*, u.username 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.article_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->execute([$article['id']]);
$comments = $stmt->fetchAll();

// Tạo URL tuyệt đối để redirect về đúng sau khi đăng nhập
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$currentUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

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
        </div>

        <!-- Phần bình luận -->
        <div class="comments-section">
            <h2 style="font-size: 1.5rem; margin-bottom: 2rem;">Bình luận (<?= count($comments) ?>)</h2>

            <?php if ($commentSuccess): ?>
                <div class="alert alert-success"><?= $commentSuccess ?></div>
            <?php endif; ?>
            <?php if ($commentError): ?>
                <div class="alert alert-danger"><?= $commentError ?></div>
            <?php endif; ?>

            <?php if (isLoggedIn()): ?>
                <div class="comment-form-container">
                    <form action="" method="POST" class="comment-form">
                        <h3>Để lại ý kiến của bạn</h3>
                        <textarea name="content" placeholder="Viết bình luận của bạn tại đây..." required></textarea>
                        <button type="submit" name="submit_comment" class="btn btn-primary">Gửi bình luận</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="login-to-comment">
                    <p>Bạn cần <strong><a href="auth/login.php?redirect=<?= urlencode($currentUrl) ?>" style="color: var(--primary); text-decoration: underline;">đăng nhập</a></strong> để có thể tham gia bình luận.</p>
                </div>
            <?php endif; ?>

            <div class="comments-list">
                <?php if (empty($comments)): ?>
                    <p style="text-align: center; color: var(--gray); padding: 2rem 0;">Chưa có bình luận nào. Hãy là người đầu tiên!</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item">
                            <div class="comment-avatar">
                                <?= strtoupper(substr($comment['username'], 0, 1)) ?>
                            </div>
                            <div class="comment-details">
                                <div class="comment-author"><?= htmlspecialchars($comment['username']) ?></div>
                                <div class="comment-date"><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></div>
                                <div class="comment-text" id="comment-text-<?= $comment['id'] ?>">
                                    <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                </div>
                                <?php if (isLoggedIn() && $_SESSION['user_id'] == $comment['user_id']): ?>
                                    <div style="margin-top: 10px; font-size: 0.9rem;">
                                        <button onclick="toggleEdit(<?= $comment['id'] ?>)" style="background: none; border: none; color: var(--primary); cursor: pointer; padding: 0;"><i class="fas fa-pen"></i> Sửa</button>
                                        <span style="color: #ccc; margin: 0 5px;">|</span>
                                        <form method="POST" action="" id="delete-form-<?= $comment['id'] ?>" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_comment">
                                            <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                            <button type="button" onclick="confirmDelete(<?= $comment['id'] ?>)" style="background: none; border: none; color: red; cursor: pointer; padding: 0;"><i class="fas fa-trash"></i> Xóa</button>
                                        </form>
                                    </div>
                                    <!-- Biểu mẫu sửa bình luận -->
                                    <form method="POST" action="" id="edit-form-<?= $comment['id'] ?>" style="display: none; margin-top: 10px;">
                                        <input type="hidden" name="action" value="update_comment">
                                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                        <textarea name="content" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc; margin-bottom: 8px; font-family: inherit; font-size: 0.95rem; resize: vertical;" required><?= htmlspecialchars($comment['content']) ?></textarea>
                                        <div>
                                            <button type="submit" class="btn btn-primary" style="padding: 6px 16px; font-size: 0.85rem;">Lưu thay đổi</button>
                                            <button type="button" class="btn btn-secondary" style="padding: 6px 16px; font-size: 0.85rem;" onclick="toggleEdit(<?= $comment['id'] ?>)">Hủy</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid #E5E7EB; text-align: center;">
            <a href="index.php" class="btn btn-secondary">&larr; Quay lại Tin tức</a>
        </div>
</article>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function toggleEdit(id) {
    var form = document.getElementById('edit-form-' + id);
    var text = document.getElementById('comment-text-' + id);
    if (form.style.display === 'none') {
        form.style.display = 'block';
        text.style.display = 'none';
    } else {
        form.style.display = 'none';
        text.style.display = 'block';
    }
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Bạn có chắc chắn?',
        text: "Bình luận này sẽ bị xóa vĩnh viễn và không thể khôi phục!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: '<i class="fas fa-trash"></i> Vâng, Xóa nó!',
        cancelButtonText: 'Hủy thao tác',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-4 shadow-lg'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    })
}
</script>

<?php require_once 'includes/footer.php'; ?>
