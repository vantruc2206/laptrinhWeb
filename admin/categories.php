<?php
require_once 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$error = '';
$success = '';

$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$whereSql = "";
$params = [];
if ($search !== '') {
    $whereSql = " WHERE name LIKE ? OR slug LIKE ? ";
    $params = ["%$search%", "%$search%"];
}


$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM categories" . $whereSql);
$totalStmt->execute($params);
$totalResults = $totalStmt->fetchColumn();
$totalPages = ceil($totalResults / $limit);

// Handle Delete
if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $check = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE category_id = ?");
    $check->execute([$id]);
    
    if ($check->fetchColumn() > 0) {
        $error = "Không thể xóa danh mục này vì đang có bài viết thuộc danh mục!";
    } else {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        if ($stmt->execute([$id])) {
            redirect('categories.php?success=deleted');
        } else {
            $error = "Không thể xóa danh mục.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name        = sanitize($_POST['name']);
    $description = sanitize($_POST['description'] ?? '');
    $id          = $_POST['id'] ?? null;

    if (empty($name)) {
        $error = "Tên danh mục không được để trống!";
    } else {
        $slug = generateSlug($name);

        
        if ($action == 'create') {
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
            $stmt->execute([$slug]);

            if ($stmt->rowCount() > 0) {
                $baseSlug = $slug;
                $counter = 2;
                do {
                    $slug = $baseSlug . '-' . $counter++;
                    $stmt->execute([$slug]);
                } while ($stmt->rowCount() > 0);
            }

            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $slug, $description])) {
                redirect('categories.php?success=created');
            } else {
                $error = "Không thể tạo danh mục. Vui lòng thử lại.";
            }

        } elseif ($action == 'edit' && $id) {
           
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $id]);

            if ($stmt->rowCount() > 0) {
                $baseSlug = $slug;
                $counter = 2;
                do {
                    $slug = $baseSlug . '-' . $counter++;
                    $stmt->execute([$slug, $id]);
                } while ($stmt->rowCount() > 0);
            }

            $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
            if ($stmt->execute([$name, $slug, $description, $id])) {
                redirect('categories.php?success=updated');
            } else {
                $error = "Không thể cập nhật danh mục.";
            }
        }
    }
}

if (isset($_GET['success'])) {
    $msgs = ['created' => 'Tạo thành công.', 'updated' => 'Cập nhật thành công.', 'deleted' => 'Đã xóa.'];
    $success = $msgs[$_GET['success']] ?? '';
}
?>

<div class="admin-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="fw-bold m-0">Quản lý Danh mục</h1>
    <?php if ($action == 'list'): ?>
        <a href="?action=create" class="btn btn-primary rounded-pill px-4 shadow-sm animate__animated animate__fadeInRight">
            <i class="fas fa-plus me-2"></i>Thêm Danh mục mới
        </a>
    <?php else: ?>
        <a href="categories.php" class="btn btn-light border rounded-pill px-4 animate__animated animate__fadeInLeft">
            <i class="fas fa-arrow-left me-2"></i>Quay lại Danh sách
        </a>
    <?php endif; ?>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger border-0 shadow-sm rounded-4 animate__animated animate__shakeX"><?= $error ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-4 animate__animated animate__bounceIn"><?= $success ?></div>
<?php endif; ?>

<?php if ($action == 'list'): ?>
    <div class="search-wrapper animate__animated animate__fadeInUp">
    <form method="GET" class="search-bar">
        <input type="text" name="search"
            placeholder="Tìm danh mục..."
            value="<?= htmlspecialchars($search ?? '') ?>">

        <button type="submit">
            <i class="fas fa-search"></i>
        </button>

        <?php if(!empty($search)): ?>
            <a href="categories.php" class="btn-clear text-muted ms-2">✕</a>
        <?php endif; ?>
    </form>
</div>

    <div class="admin-card-modern animate__animated animate__fadeInUp">

    <!-- HEADER TABLE -->
    <div class="table-header">
        <h5 class="fw-bold mb-0">Danh sách danh mục</h5>
        <span class="badge bg-light text-dark rounded-pill px-3"><?= $totalResults ?> danh mục</span>
    </div>

    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Tên danh mục</th>
                    <th>Slug</th>
                    <th class="text-center">Bài viết</th>
                    <th class="text-end pe-4">Thao tác</th>
                </tr>
            </thead>
<tbody>
    <?php
    $sql = "SELECT c.*, 
                   (SELECT COUNT(*) FROM articles WHERE category_id = c.id) as article_count 
            FROM categories c 
            $whereSql 
            ORDER BY c.id DESC 
            LIMIT $start, $limit";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($totalResults > 0):
        while ($row = $stmt->fetch()):
    ?>
    <tr>
        <td class="ps-4 text-muted fw-medium">#<?= $row['id'] ?></td>
        
        <td class="fw-bold text-dark"><?= htmlspecialchars($row['name']) ?></td>
        
        <td>
            <span class="slug-badge"><?= htmlspecialchars($row['slug']) ?></span>
        </td>
        
       
        <td class="text-center">
            <span class="badge bg-soft-success text-success px-3 py-2 rounded-pill" style="background: var(--success-soft);">
                <?= $row['article_count'] ?> bài viết
            </span>
        </td>
        
       
        <td class="text-end pe-4">
            <a href="?action=edit&id=<?= $row['id'] ?>" 
               class="btn btn-sm btn-outline-primary border-0 me-2 px-3 fw-bold">
                Sửa
            </a>
            
            <a href="?action=delete&id=<?= $row['id'] ?>" 
               class="btn btn-sm btn-danger rounded-3 px-3 shadow-sm"
               onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này không?');">
                Xóa
            </a>
        </td>
    </tr>
    <?php endwhile; else: ?>
    <tr>
        <td colspan="5" class="text-center py-5 text-muted">Không có danh mục nào.</td>
    </tr>
    <?php endif; ?>
</tbody>

        </table>
    </div>

</div>
   

    <?php if ($totalPages > 1): ?>
    <div class="d-flex justify-content-center mt-4 mb-5">
        <nav class="pagination-container">
            <?php if ($page > 1): ?>
                <a class="page-square" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <a class="page-square <?= ($page == $i) ? 'active' : '' ?>" 
                   href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a class="page-square" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </nav>
    </div>
    <?php endif; ?>

<?php else: ?>
    <?php 
        $category = ['name' => '', 'description' => ''];
        if ($action == 'edit' && isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $category = $stmt->fetch();
            if (!$category) redirect('categories.php');
        }
    ?>
    <div class="admin-card shadow-sm mx-auto border-0 rounded-4 bg-white animate__animated animate__fadeIn" style="max-width: 700px; padding: 3rem; margin-top: 2rem;">
        <h2 class="fw-bold mb-4 text-center text-dark">
            <?= $action == 'create' ? '✨ Thêm Danh mục mới' : '📝 Cập nhật Danh mục' ?>
        </h2>
        
        <form method="POST" action="">
            <div class="form-group mb-4">
                <label class="fw-bold mb-2 text-muted small text-uppercase">Tên Danh mục</label>
                <input type="text" name="name" class="form-control rounded-pill py-2 px-4 border-light-subtle bg-light" 
                       placeholder="Ví dụ: Đời sống, Sức khỏe..."
                       value="<?= htmlspecialchars($category['name']) ?>" required>
            </div>
            
            <div class="form-group mb-4">
                <label class="fw-bold mb-2 text-muted small text-uppercase">Mô tả chi tiết</label>
                <textarea name="description" class="form-control rounded-4 px-4 py-3 border-light-subtle bg-light" 
                          rows="5" placeholder="Nhập mô tả ngắn gọn về danh mục này..."><?= htmlspecialchars($category['description']) ?></textarea>
            </div>
            
            <div class="d-flex gap-3 mt-5 justify-content-center">
                <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold shadow-sm py-2">
                    <?= $action == 'create' ? 'Tạo danh mục' : 'Cập nhật ngay' ?>
                </button>
                <a href="categories.php" class="btn btn-light border px-5 rounded-pill text-muted py-2">Hủy bỏ</a>
            </div>
        </form>
    </div>
<?php endif; ?>
