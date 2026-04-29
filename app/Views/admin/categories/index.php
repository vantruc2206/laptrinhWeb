<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="admin-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="fw-bold m-0">Quản lý Danh mục</h1>
    <?php if ($action == 'list'): ?>
        <a href="?action=create" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="fas fa-plus me-2"></i>Thêm Danh mục mới
        </a>
    <?php else: ?>
        <a href="categories.php" class="btn btn-light border rounded-pill px-4">
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
    <div class="search-wrapper">
        <form method="GET" class="search-bar">
            <input type="text" name="search" placeholder="Tìm danh mục..."
                   value="<?= htmlspecialchars($search ?? '') ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
            <?php if (!empty($search)): ?>
                <a href="categories.php" class="btn-clear text-muted ms-2">✕</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="art-card">
        <div class="art-table-header">
            <h5>Danh sách danh mục</h5>
            <span class="art-count-badge"><?= $totalResults ?> danh mục</span>
        </div>
        <div class="table-responsive">
            <table class="art-table">
                <thead>
                    <tr>
                        <th style="padding-left:1.25rem;">ID</th>
                        <th>Tên danh mục</th>
                        <th>Slug</th>
                        <th>Bài viết</th>
                        <th style="text-align:right; padding-right:1.25rem;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($totalResults > 0): foreach ($categories as $row): ?>
                    <tr>
                        <td style="padding-left:1.25rem;">#<?= $row['id'] ?></td>
                        <td><span class="art-title"><?= htmlspecialchars($row['name']) ?></span></td>
                        <td><span class="cat-badge"><?= htmlspecialchars($row['slug']) ?></span></td>
                        <td>
                            <span class="status-published rounded-pill"><?= $row['article_count'] ?> bài viết</span>
                        </td>
                        <td style="text-align:right; padding-right:1.25rem; white-space:nowrap;">
                            <a href="?action=edit&id=<?= $row['id'] ?>" class="btn-edit">
                                <i class="fas fa-pen me-1"></i>Sửa
                            </a>
                            <a href="?action=delete&id=<?= $row['id'] ?>" class="btn-del ms-1" data-type="danh mục">
                                <i class="fas fa-trash me-1"></i>Xóa
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="5">
                            <div class="art-empty">
                                <i class="fas fa-folder-open"></i>
                                <p>Không tìm thấy danh mục nào.</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="art-pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>"><i class="fas fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="<?= ($page == $i) ? 'active' : '' ?>" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>"><i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>

<?php else: ?>
    <div class="admin-card" style="padding:2rem;">
        <h2 class="fw-bold mb-4">
            <?= $action == 'create' ? 'Thêm Danh mục mới' : 'Cập nhật Danh mục' ?>
        </h2>
        <form method="POST" action="">
            <?php if ($action == 'edit'): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($category['id']) ?>">
            <?php endif; ?>
            <div class="form-group mb-3">
                <label class="fw-bold mb-2">Tên Danh mục</label>
                <input type="text" name="name" class="form-control"
                       placeholder="Ví dụ: Đời sống, Sức khỏe..."
                       value="<?= htmlspecialchars($category['name']) ?>" required>
            </div>
            <div class="form-group mb-4">
                <label class="fw-bold mb-2">Mô tả chi tiết</label>
                <textarea name="description" class="form-control"
                          rows="5" placeholder="Nhập mô tả ngắn gọn về danh mục này..."><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
            </div>
            <div style="margin-top:1rem;">
                <button type="submit" class="btn btn-primary px-5 py-2">
                    <?= $action == 'create' ? 'Tạo danh mục' : 'Cập nhật ngay' ?>
                </button>
                <a href="categories.php" class="btn btn-light border px-5 py-2 ms-2">Hủy bỏ</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
