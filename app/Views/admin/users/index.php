<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="admin-header">
    <h1>Quản lý Người dùng</h1>
    <?php if ($action == 'list'): ?>
        <a href="?action=create" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm Người dùng mới</a>
    <?php else: ?>
        <a href="users.php" class="btn btn-secondary">Quay lại Danh sách</a>
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
                    <th>Tên đăng nhập</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Ngày tham gia</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $row): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <span class="badge <?= $row['role'] == 'admin' ? 'badge-success' : 'badge-warning' ?>">
                            <?= $row['role'] == 'admin' ? 'Quản trị viên' : 'Người dùng' ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                    <td class="action-links">
                        <a href="?action=edit&id=<?= $row['id'] ?>" class="btn-edit"><i class="fas fa-pen me-1"></i>Sửa</a>
                        <?php if ($row['id'] != $_SESSION['user_id']): ?>
                            <a href="?action=delete&id=<?= $row['id'] ?>" class="btn-del ms-1" data-type="người dùng"><i class="fas fa-trash me-1"></i>Xóa</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="art-pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?>"><i class="fas fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="<?= ($page == $i) ? 'active' : '' ?>" href="?page=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page+1 ?>"><i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>

<?php else: ?>
    <div class="admin-card" style="max-width: 600px; padding: 2rem;">
        <form method="POST" action="">
            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="form-group">
                <label>Địa chỉ Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="form-group">
                <label>Vai trò</label>
                <select name="role" class="form-control">
                    <option value="user" <?= ($user['role'] ?? '') == 'user' ? 'selected' : '' ?>>Người dùng</option>
                    <option value="admin" <?= ($user['role'] ?? '') == 'admin' ? 'selected' : '' ?>>Quản trị viên</option>
                </select>
            </div>
            <div class="form-group">
                <label><?= $action == 'edit' ? 'Mật khẩu mới (để trống nếu không đổi)' : 'Mật khẩu' ?></label>
                <input type="password" name="password" class="form-control" <?= $action == 'create' ? 'required' : '' ?>>
            </div>
            <button type="submit" class="btn btn-primary"><?= $action == 'create' ? 'Tạo Người dùng' : 'Cập nhật Người dùng' ?></button>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
