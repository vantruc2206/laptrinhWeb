<?php
require_once 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$error = '';
$success = '';
$uploadDir = '../assets/images/articles/';

// Ensure upload directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Handle Delete
if ($action == 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT image FROM articles WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $article = $stmt->fetch();
    if ($article && $article['image'] && file_exists($uploadDir . $article['image'])) {
        unlink($uploadDir . $article['image']);
    }
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
    if ($stmt->execute([$_GET['id']])) {
        redirect('articles.php?success=deleted');
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $category_id = $_POST['category_id'];
    $summary = sanitize($_POST['summary']);
    $content = $_POST['content']; // Expect HTML from a rich text editor if implemented
    $status = $_POST['status'];
    $slug = generateSlug($title);
    $author_id = $_SESSION['user_id'];

    if (empty($title) || empty($category_id) || empty($summary) || empty($content)) {
        $error = "All fields except image are required.";
    } else {
        // Image Upload Logic
        $imageName = $_POST['current_image'] ?? null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $tmpName = $_FILES['image']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($ext, $allowed)) {
                $imageName = time() . '_' . uniqid() . '.' . $ext;
                if (!move_uploaded_file($tmpName, $uploadDir . $imageName)) {
                    $error = "Failed to upload image.";
                } else if (isset($_POST['current_image']) && file_exists($uploadDir . $_POST['current_image'])) {
                    unlink($uploadDir . $_POST['current_image']); 
                }
            } else {
                $error = "Invalid image type. Allowed: JPG, PNG, GIF, WEBP.";
            }
        }

        if (!$error) {
            if ($action == 'create') {
                $stmt = $pdo->prepare("INSERT INTO articles (category_id, author_id, title, slug, summary, content, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$category_id, $author_id, $title, $slug, $summary, $content, $imageName, $status])) {
                    redirect('articles.php?success=created');
                } else {
                    $error = "Failed to create article. Title/Slug might exist.";
                }
            } elseif ($action == 'edit' && isset($_GET['id'])) {
                $stmt = $pdo->prepare("UPDATE articles SET category_id = ?, title = ?, slug = ?, summary = ?, content = ?, image = ?, status = ? WHERE id = ?");
                if ($stmt->execute([$category_id, $title, $slug, $summary, $content, $imageName, $status, $_GET['id']])) {
                    redirect('articles.php?success=updated');
                } else {
                    $error = "Failed to update article.";
                }
            }
        }
    }
}

// Handle success messages
if (isset($_GET['success'])) {
    $msgs = ['created' => 'Tạo bài viết thành công.', 'updated' => 'Cập nhật bài viết thành công.', 'deleted' => 'Đã xóa bài viết.'];
    $success = $msgs[$_GET['success']] ?? '';
}

// Fetch categories for forms
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
?>

<div class="admin-header">
    <h1>Quản lý Bài viết</h1>
    <?php if ($action == 'list'): ?>
        <a href="?action=create" class="btn btn-primary">Viết Bài mới</a>
    <?php else: ?>
        <a href="articles.php" class="btn btn-secondary">Quay lại Danh sách</a>
    <?php endif; ?>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<?php if ($action == 'list'): ?>
    <div class="admin-card table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Hình ảnh</th>
                    <th>Tiêu đề</th>
                    <th>Danh mục</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("
                    SELECT a.id, a.title, a.image, a.status, a.created_at, c.name as category_name
                    FROM articles a 
                    LEFT JOIN categories c ON a.category_id = c.id
                    ORDER BY a.created_at DESC
                ");
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td>
                        <?php if ($row['image']): ?>
                            <img src="<?= $uploadDir . htmlspecialchars($row['image']) ?>" alt="" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                        <?php else: ?>
                            <div style="width: 60px; height: 40px; background: #E5E7EB; border-radius: 4px;"></div>
                        <?php endif; ?>
                    </td>
                    <td style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?= htmlspecialchars($row['title']) ?>
                    </td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                    <td>
                        <span class="badge <?= $row['status'] == 'published' ? 'badge-success' : 'badge-warning' ?>">
                            <?= $row['status'] == 'published' ? 'Đã xuất bản' : 'Bản nháp' ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                    <td class="action-links">
                        <a href="?action=edit&id=<?= $row['id'] ?>" class="edit">Sửa</a>
                        <a href="?action=delete&id=<?= $row['id'] ?>" class="delete" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này? Hành động này không thể hoàn tác!');">Xóa</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: 
    // Form Tạo mới hoặc Chỉnh sửa
    $article = ['title' => '', 'category_id' => '', 'summary' => '', 'content' => '', 'image' => '', 'status' => 'published'];
    if ($action == 'edit' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $article = $stmt->fetch();
        if (!$article) redirect('articles.php');
    }
?>
<!-- Nhúng TinyMCE từ CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      tinymce.init({
        selector: 'textarea[name="content"]',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        height: 600,
        images_upload_url: 'upload_handler.php',
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,
        branding: false,
        promotion: false
      });
    </script>

    <div class="admin-card" style="padding: 2rem;">
        <form method="POST" action="" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                <!-- Main Column -->
                <div>
                    <div class="form-group">
                        <label>Tiêu đề</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($article['title']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Tóm tắt</label>
                        <textarea name="summary" class="form-control" rows="3" required><?= htmlspecialchars($article['summary']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Nội dung</label>
                        <textarea name="content" class="form-control" rows="15"><?= htmlspecialchars($article['content']) ?></textarea>
                    </div>
                </div>
                
                <!-- Sidebar Column -->
                <div>
                    <div class="form-group">
                        <label>Danh mục</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Chọn danh mục...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $article['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Trạng thái</label>
                        <select name="status" class="form-control">
                            <option value="published" <?= $article['status'] == 'published' ? 'selected' : '' ?>>Đã xuất bản</option>
                            <option value="draft" <?= $article['status'] == 'draft' ? 'selected' : '' ?>>Bản nháp</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ảnh đại diện</label>
                        <?php if ($article['image']): ?>
                            <div style="margin-bottom: 10px;">
                                <img src="<?= $uploadDir . htmlspecialchars($article['image']) ?>" alt="Curent Image" style="width: 100%; height: auto; border-radius: 4px;">
                                <input type="hidden" name="current_image" value="<?= htmlspecialchars($article['image']) ?>">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem; padding: 1rem;">
                        <?= $action == 'create' ? 'Xuất bản bài viết' : 'Cập nhật bài viết' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
