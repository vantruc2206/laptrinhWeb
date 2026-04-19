<?php
require_once 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$error = '';
$success = '';
$uploadDir = '../assets/images/articles/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Phân trang
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Xử lý xóa
if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT image FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();

    if ($article && $article['image'] && file_exists($uploadDir . $article['image'])) {
        unlink($uploadDir . $article['image']);
    }

    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
    if ($stmt->execute([$id])) {
        redirect("articles.php?success=deleted&page=$page");
    }
}

// Xử lý tạo/sửa bài viết
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title       = sanitize($_POST['title']);
    $category_id = (int)$_POST['category_id'];
    $summary     = sanitize($_POST['summary']);
    $content     = $_POST['content'];
    $status      = $_POST['status'];
    $slug        = generateSlug($title);
    $author_id   = $_SESSION['user_id'] ?? 1;

    if (empty($title) || empty($category_id) || empty($summary) || empty($content)) {
        $error = "Vui lòng điền đầy đủ các trường bắt buộc.";
    } else {
        $imageName = $_POST['current_image'] ?? null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $tmpName = $_FILES['image']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($ext, $allowed)) {
                $imageName = time() . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($tmpName, $uploadDir . $imageName);

                if (!empty($_POST['current_image']) && file_exists($uploadDir . $_POST['current_image'])) {
                    unlink($uploadDir . $_POST['current_image']);
                }
            } else {
                $error = "Chỉ chấp nhận file ảnh: JPG, PNG, GIF, WEBP.";
            }
        }

        if (!$error) {
            if ($action == 'create') {
                $stmt = $pdo->prepare("INSERT INTO articles (category_id, author_id, title, slug, summary, content, image, status, created_at) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$category_id, $author_id, $title, $slug, $summary, $content, $imageName, $status]);
                redirect('articles.php?success=created');
            } elseif ($action == 'edit' && isset($_GET['id'])) {
                $stmt = $pdo->prepare("UPDATE articles SET category_id = ?, title = ?, slug = ?, summary = ?, content = ?, image = ?, status = ? WHERE id = ?");
                $stmt->execute([$category_id, $title, $slug, $summary, $content, $imageName, $status, $_GET['id']]);
                redirect("articles.php?success=updated&page=$page");
            }
        }
    }
}

// Success message
if (isset($_GET['success'])) {
    $msgs = ['created' => 'Tạo bài viết thành công!', 'updated' => 'Cập nhật bài viết thành công!', 'deleted' => 'Đã xóa bài viết thành công!'];
    $success = $msgs[$_GET['success']] ?? '';
}

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

$totalArticles = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$totalPages = ceil($totalArticles / $limit);
?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold mb-0">
            <?= $action == 'list' ? 'Quản lý Bài viết' : ($action == 'create' ? 'Viết Bài Mới' : 'Chỉnh Sửa Bài Viết') ?>
        </h1>

        <?php if ($action == 'list'): ?>
            <a href="?action=create" class="btn btn-primary btn-lg">
                <i class="fas fa-plus"></i> Viết Bài Mới
            </a>
        <?php else: ?>
            <a href="articles.php" class="btn btn-success btn-lg">
                ← Quay lại Danh sách
            </a>
        <?php endif; ?>
    </div>

    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

    <?php if ($action == 'list'): ?>
        <!-- Danh sách -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Hình ảnh</th>
                                <th>Tiêu đề</th>
                                <th>Danh mục</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th width="160">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("
                                SELECT a.id, a.title, a.image, a.status, a.created_at, c.name as category_name
                                FROM articles a 
                                LEFT JOIN categories c ON a.category_id = c.id
                                ORDER BY a.created_at DESC 
                                LIMIT ? OFFSET ?
                            ");
                            $stmt->execute([$limit, $offset]);
                            while ($row = $stmt->fetch()):
                            ?>
                            <tr>
                                <td>
                                    <?php if ($row['image']): ?>
                                        <img src="<?= $uploadDir . htmlspecialchars($row['image']) ?>" 
                                             style="width: 65px; height: 45px; object-fit: cover; border-radius: 6px;" alt="">
                                    <?php else: ?>
                                        <div style="width: 65px; height: 45px; background:#e9ecef; border-radius: 6px;"></div>
                                    <?php endif; ?>
                                </td>
                                <td style="max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= htmlspecialchars($row['title']) ?>
                                </td>
                                <td><?= htmlspecialchars($row['category_name'] ?? 'Chưa phân loại') ?></td>
                                <td>
                                    <span class="badge <?= $row['status'] == 'published' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $row['status'] == 'published' ? 'Đã xuất bản' : 'Bản nháp' ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <a href="?action=edit&id=<?= $row['id'] ?>&page=<?= $page ?>" class="btn btn-sm btn-success me-2">Sửa</a>
                                    <a href="?action=delete&id=<?= $row['id'] ?>&page=<?= $page ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này?\nHành động này không thể hoàn tác!');">
                                        Xóa
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Phân trang -->
        <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page-1 ?>">« Trước</a></li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page+1 ?>">Sau »</a></li>
            </ul>
        </nav>
        <?php endif; ?>

    <?php else: ?>
        <!-- ================== FORM VIẾT BÀI MỚI ================== -->
        <?php
        $article = ['title'=>'', 'category_id'=>'', 'summary'=>'', 'content'=>'', 'image'=>'', 'status'=>'published'];
        if ($action == 'edit' && isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $article = $stmt->fetch() ?: $article;
        }
        ?>

        <div class="card shadow-sm">
            <div class="card-body p-5">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="mb-4">
                                <label class="form-label fw-semibold fs-5">Tiêu đề bài viết</label>
                                <input type="text" name="title" class="form-control form-control-lg" 
                                       value="<?= htmlspecialchars($article['title']) ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Tóm tắt</label>
                                <textarea name="summary" class="form-control" rows="3" required><?= htmlspecialchars($article['summary']) ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Nội dung bài viết</label>
                                <textarea name="content" class="form-control" rows="16" required><?= htmlspecialchars($article['content']) ?></textarea>
                                <small class="text-muted">Hỗ trợ HTML để định dạng nội dung.</small>
                            </div>
                        </div>

                        <!-- Sidebar thông tin -->
                        <div class="col-lg-4">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Danh mục</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Chọn danh mục...</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= $article['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Trạng thái</label>
                                <select name="status" class="form-select">
                                    <option value="published" <?= $article['status'] == 'published' ? 'selected' : '' ?>>Đã xuất bản</option>
                                    <option value="draft" <?= $article['status'] == 'draft' ? 'selected' : '' ?>>Bản nháp</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Ảnh đại diện</label>
                                <?php if (!empty($article['image'])): ?>
                                    <div class="mb-3 text-center">
                                        <img src="<?= $uploadDir . htmlspecialchars($article['image']) ?>" 
                                             class="img-fluid rounded shadow-sm" style="max-height: 220px; object-fit: cover;" alt="">
                                        <input type="hidden" name="current_image" value="<?= htmlspecialchars($article['image']) ?>">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 mt-4">
                                <?= $action == 'create' ? 'Xuất bản bài viết' : 'Cập nhật bài viết' ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>