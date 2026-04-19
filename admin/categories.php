<?php
require_once 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$error = '';
$success = '';

// Handle Delete
if ($action == 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$_GET['id']])) {
        redirect('categories.php?success=deleted');
    }
}

// Handle Form Submission (Create/Edit)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $slug = generateSlug($name);

    if (empty($name)) {
        $error = "Category name is required.";
    } else {
        if ($action == 'create') {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $slug, $description])) {
                redirect('categories.php?success=created');
            } else {
                $error = "Failed to create category. Name might already exist.";
            }
        } elseif ($action == 'edit' && isset($_GET['id'])) {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
            if ($stmt->execute([$name, $slug, $description, $_GET['id']])) {
                redirect('categories.php?success=updated');
            } else {
                $error = "Failed to update category.";
            }
        }
    }
}

// Handle success messages from redirect
if (isset($_GET['success'])) {
    $msgs = ['created' => 'Tạo danh mục thành công.', 'updated' => 'Cập nhật danh mục thành công.', 'deleted' => 'Đã xóa danh mục.'];
    $success = $msgs[$_GET['success']] ?? '';
}
?>

<div class="admin-header">
    <h1>Quản lý Danh mục</h1>
    <?php if ($action == 'list'): ?>
        <a href="?action=create" class="btn btn-primary">Thêm Danh mục mới</a>
    <?php else: ?>
        <a href="categories.php" class="btn btn-secondary">Quay lại Danh sách</a>
    <?php endif; ?>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<?php if ($action == 'list'): ?>
    <div class="admin-card table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Đường dẫn</th>
                    <th>Số bài viết</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("
                    SELECT c.*, (SELECT COUNT(*) FROM articles WHERE category_id = c.id) as count 
                    FROM categories c ORDER BY c.name ASC
                ");
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['slug']) ?></td>
                    <td><span class="badge badge-success"><?= $row['count'] ?></span></td>
                    <td class="action-links">
                        <a href="?action=edit&id=<?= $row['id'] ?>" class="edit">Sửa</a>
                        <a href="?action=delete&id=<?= $row['id'] ?>" class="delete" onclick="return confirm('Xác nhận xóa danh mục này? LƯU Ý: Toàn bộ bài viết thuộc danh mục này cũng sẽ bị xóa vĩnh viễn!');">Xóa</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: 
    // Create or Edit Form
    $category = ['name' => '', 'description' => ''];
    if ($action == 'edit' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $category = $stmt->fetch();
        if (!$category) redirect('categories.php');
    }
?>
    <div class="admin-card" style="max-width: 600px; padding: 2rem;">
        <form method="POST" action="">
            <div class="form-group">
                <label>Tên Danh mục</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Mô tả</label>
                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($category['description']) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?= $action == 'create' ? 'Tạo Danh mục' : 'Cập nhật Danh mục' ?></button>
        </form>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
