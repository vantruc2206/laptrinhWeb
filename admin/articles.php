<?php
require_once 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$error = '';
$success = '';
$uploadDir = '../assets/images/articles/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// ── Xóa bài viết
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

// ── Xử lý form tạo / sửa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title       = sanitize($_POST['title']);
    $category_id = $_POST['category_id'];
    $summary     = sanitize($_POST['summary']);
    $content     = $_POST['content'];
    $status      = $_POST['status'];
    $slug        = generateSlug($title);
    $author_id   = $_SESSION['user_id'];

    if (empty($title) || empty($category_id) || empty($summary) || empty($content)) {
        $error = "Vui lòng điền đầy đủ các trường bắt buộc.";
    } else {
        $imageName = $_POST['current_image'] ?? null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $tmpName = $_FILES['image']['tmp_name'];
            $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed)) {
                $imageName = time() . '_' . uniqid() . '.' . $ext;
                if (!move_uploaded_file($tmpName, $uploadDir . $imageName)) {
                    $error = "Tải ảnh lên thất bại.";
                } elseif (isset($_POST['current_image']) && file_exists($uploadDir . $_POST['current_image'])) {
                    unlink($uploadDir . $_POST['current_image']);
                }
            } else {
                $error = "Định dạng ảnh không hợp lệ. Chấp nhận: JPG, PNG, GIF, WEBP.";
            }
        }

        if (!$error) {
            if ($action == 'create') {
                $stmt = $pdo->prepare("INSERT INTO articles (category_id, author_id, title, slug, summary, content, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$category_id, $author_id, $title, $slug, $summary, $content, $imageName, $status])) {
                    redirect('articles.php?success=created');
                } else {
                    $error = "Tạo bài viết thất bại. Tiêu đề có thể đã tồn tại.";
                }
            } elseif ($action == 'edit' && isset($_GET['id'])) {
                $stmt = $pdo->prepare("UPDATE articles SET category_id=?, title=?, slug=?, summary=?, content=?, image=?, status=? WHERE id=?");
                if ($stmt->execute([$category_id, $title, $slug, $summary, $content, $imageName, $status, $_GET['id']])) {
                    redirect('articles.php?success=updated');
                } else {
                    $error = "Cập nhật bài viết thất bại.";
                }
            }
        }
    }
}

// ── Thông báo thành công 
if (isset($_GET['success'])) {
    $msgs    = ['created' => 'Tạo bài viết thành công.', 'updated' => 'Cập nhật bài viết thành công.', 'deleted' => 'Đã xóa bài viết.'];
    $success = $msgs[$_GET['success']] ?? '';
}

// ── Danh mục cho dropdown 
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

// ── Search / Filter / Pagination
$limit        = 10;
$page         = isset($_GET['page'])          ? max(1, (int)$_GET['page'])  : 1;
$search       = isset($_GET['search'])        ? trim($_GET['search'])        : '';
$filterCat    = isset($_GET['category_id'])   ? (int)$_GET['category_id']   : 0;
$filterStatus = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : '';

$whereParts = [];
$params     = [];

if ($search !== '') {
    $whereParts[] = "(a.title LIKE ? OR a.summary LIKE ?)";
    $params[]     = "%$search%";
    $params[]     = "%$search%";
}
if ($filterCat > 0) {
    $whereParts[] = "a.category_id = ?";
    $params[]     = $filterCat;
}
if ($filterStatus === 'published' || $filterStatus === 'draft') {
    $whereParts[] = "a.status = ?";
    $params[]     = $filterStatus;
}

$whereSql     = count($whereParts) ? ' WHERE ' . implode(' AND ', $whereParts) : '';
$countStmt    = $pdo->prepare("SELECT COUNT(*) FROM articles a LEFT JOIN categories c ON a.category_id = c.id" . $whereSql);
$countStmt->execute($params);
$totalResults = (int)$countStmt->fetchColumn();
$totalPages   = max(1, (int)ceil($totalResults / $limit));
if ($page > $totalPages) $page = $totalPages;
$start = ($page - 1) * $limit;

function buildQuery($overrides = []) {
    $base   = [
        'search'        => $_GET['search']        ?? '',
        'category_id'   => $_GET['category_id']   ?? '',
        'status_filter' => $_GET['status_filter']  ?? '',
    ];
    $merged = array_merge($base, $overrides);
    $merged = array_filter($merged, fn($v) => $v !== '' && $v !== '0' && $v !== 0);
    return http_build_query($merged);
}
?>

<!-- Tiêu đề trang -->
<div class="admin-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="fw-bold m-0">Quản lý Bài viết</h1>
    <?php if ($action == 'list'): ?>
        <a href="?action=create" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="fas fa-plus me-2"></i>Viết Bài mới
        </a>
    <?php else: ?>
        <a href="articles.php" class="btn btn-light border rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Quay lại Danh sách
        </a>
    <?php endif; ?>
</div>

<!-- Thông báo -->
<?php if ($error):   ?><div class="alert alert-danger  border-0 shadow-sm rounded-4 animate__animated animate__shakeX"><?= $error   ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success border-0 shadow-sm rounded-4 animate__animated animate__bounceIn"><?= $success ?></div><?php endif; ?>

<?php if ($action == 'list'): ?>

<!-- ── Search bar (giống categories) -->
<div class="search-wrapper">
    <form method="GET" action="articles.php" class="search-bar">
        <input type="hidden" name="action" value="list">
        <?php if ($filterCat > 0): ?><input type="hidden" name="category_id" value="<?= $filterCat ?>"><?php endif; ?>
        <?php if ($filterStatus !== ''): ?><input type="hidden" name="status_filter" value="<?= htmlspecialchars($filterStatus) ?>"><?php endif; ?>
        
        <input type="text"
               name="search"
               placeholder="Tìm bài viết theo tiêu đề, tóm tắt..."
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
        <?php if ($search !== ''): ?>
            <a href="articles.php?<?= buildQuery(['search' => '']) ?>" class="btn-clear">✕</a>
        <?php endif; ?>
    </form>
</div>

<!-- ── Filter row (danh mục + trạng thái) -->
<form method="GET" action="articles.php">
    <input type="hidden" name="action" value="list">
    <?php if ($search !== ''): ?><input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>"><?php endif; ?>

    <div class="filter-row">

        <!-- Lọc danh mục -->
        <select name="category_id" onchange="this.form.submit()">
            <option value=""> Tất cả danh mục</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $filterCat == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Lọc trạng thái -->
        <select name="status_filter" onchange="this.form.submit()">
            <option value=""> Tất cả trạng thái</option>
            <option value="published" <?= $filterStatus === 'published' ? 'selected' : '' ?>> Đã xuất bản</option>
            <option value="draft"     <?= $filterStatus === 'draft'     ? 'selected' : '' ?>> Bản nháp</option>
        </select>



        <!-- Xóa bộ lọc -->
        <?php if ($filterCat > 0 || $filterStatus !== '' || $search !== ''): ?>
            <a href="articles.php" class="btn-reset">✕ Xóa lọc</a>
        <?php endif; ?>

        <!-- Đếm kết quả -->
        <span class="result-info">
            Tìm thấy <strong><?= number_format($totalResults) ?></strong> bài viết
        </span>
    </div>
</form>

<!-- Table card -->
<div class="art-card">

    <div class="art-table-header">
        <h5>Danh sách bài viết</h5>
        <span class="art-count-badge"><?= number_format($totalResults) ?> bài viết</span>
    </div>

    <div class="table-responsive">
        <table class="art-table">
            <thead>
                <tr>
                    <th style="padding-left:1.25rem; width:74px;">Ảnh</th>
                    <th>Tiêu đề</th>
                    <th>Danh mục</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th style="text-align:right; padding-right:1.25rem;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sql = "
                SELECT a.id, a.title, a.image, a.status, a.created_at, c.name AS category_name
                FROM articles a
                LEFT JOIN categories c ON a.category_id = c.id
                {$whereSql}
                ORDER BY a.created_at DESC
                LIMIT {$start}, {$limit}
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            if (count($rows) === 0): ?>
                <tr>
                    <td colspan="6">
                        <div class="art-empty">
                            <i class="fas fa-newspaper"></i>
                            <p>Không tìm thấy bài viết nào.</p>
                        </div>
                    </td>
                </tr>
            <?php else: foreach ($rows as $row): ?>
                <tr>
                    <!-- Ảnh thumbnail -->
                    <td style="padding-left:1.25rem;">
                        <?php if ($row['image']): ?>
                            <img class="art-thumb"
                                 src="<?= $uploadDir . htmlspecialchars($row['image']) ?>"
                                 alt="">
                        <?php else: ?>
                            <div class="art-thumb-empty">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                    </td>

                    <!-- Tiêu đề -->
                    <td>
                        <span class="art-title" title="<?= htmlspecialchars($row['title']) ?>">
                            <?= htmlspecialchars($row['title']) ?>
                        </span>
                    </td>

                    <!-- Danh mục -->
                    <td>
                        <span class="cat-badge">
                            <?= htmlspecialchars($row['category_name'] ?? '—') ?>
                        </span>
                    </td>

                    <!-- Trạng thái -->
                    <td>
                        <?php if ($row['status'] === 'published'): ?>
                            <span class="status-published">Đã xuất bản</span>
                        <?php else: ?>
                            <span class="status-draft">Bản nháp</span>
                        <?php endif; ?>
                    </td>

                    <!-- Ngày tạo -->
                    <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>

                    <!-- Thao tác -->
                    <td style="text-align:right; padding-right:1.25rem; white-space:nowrap;">
                        <a href="?action=edit&id=<?= $row['id'] ?>" class="btn-edit">
                            <i class="fas fa-pen me-1"></i>Sửa
                        </a>
                        <a href="?action=delete&id=<?= $row['id'] ?>&<?= buildQuery() ?>"
                           class="btn-del ms-1" data-type="bài viết">
                            <i class="fas fa-trash me-1"></i>Xóa
                        </a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Phân trang -->
<?php if ($totalPages > 1): ?>
<nav class="art-pagination">
    <?php if ($page > 1): ?>
        <a href="?<?= buildQuery(['page' => $page - 1]) ?>">
            <i class="fas fa-chevron-left"></i>
        </a>
    <?php endif; ?>

    <?php
    $range   = 2;
    $prevDot = false;
    for ($i = 1; $i <= $totalPages; $i++):
        if ($i === 1 || $i === $totalPages || abs($i - $page) <= $range) {
            $prevDot = false; ?>
            <a href="?<?= buildQuery(['page' => $i]) ?>"
               class="<?= $page === $i ? 'active' : '' ?>"><?= $i ?></a>
        <?php } elseif (!$prevDot) {
            $prevDot = true;
            echo '<span>…</span>';
        }
    endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?<?= buildQuery(['page' => $page + 1]) ?>">
            <i class="fas fa-chevron-right"></i>
        </a>
    <?php endif; ?>
</nav>
<?php endif; ?>

<?php else:
// Form tạo mới / chỉnh sửa
$article = ['title' => '', 'category_id' => '', 'summary' => '', 'content' => '', 'image' => '', 'status' => 'published'];
if ($action == 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $article = $stmt->fetch();
    if (!$article) redirect('articles.php');
}
?>

<!-- Trình soạn thảo TinyMCE -->
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

<div class="admin-card" style="padding:2rem;">
    <form method="POST" action="" enctype="multipart/form-data">
        <div style="display:grid; grid-template-columns:2fr 1fr; gap:2rem;">

            <!-- Cột chính -->
            <div>
                <div class="form-group">
                    <label>Tiêu đề</label>
                    <input type="text" name="title" class="form-control"
                           value="<?= htmlspecialchars($article['title']) ?>" required>
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

            <!-- Thanh bên -->
            <div>
                <div class="form-group mb-3">
                    <label class="fw-bold mb-2">Danh mục</label>
                    <select name="category_id" class="form-select rounded-pill">
                        <option value="">Chọn danh mục...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                    <?= $article['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="status" class="form-control">
                        <option value="published" <?= $article['status'] == 'published' ? 'selected' : '' ?>>Đã xuất bản</option>
                        <option value="draft"     <?= $article['status'] == 'draft'     ? 'selected' : '' ?>>Bản nháp</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ảnh đại diện</label>
                    <?php if ($article['image']): ?>
                        <div style="margin-bottom:10px;">
                            <img src="<?= $uploadDir . htmlspecialchars($article['image']) ?>"
                                 alt="Ảnh hiện tại"
                                 style="width:100%;height:auto;border-radius:8px;">
                            <input type="hidden" name="current_image"
                                   value="<?= htmlspecialchars($article['image']) ?>">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control"
                           accept="image/jpeg,image/png,image/gif,image/webp">
                </div>

                <button type="submit" class="btn btn-primary btn-block"
                        style="margin-top:1rem; padding:1rem; width:100%;">
                    <?= $action == 'create' ? 'Xuất bản bài viết' : 'Cập nhật bài viết' ?>
                </button>
            </div>

        </div>
    </form>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>