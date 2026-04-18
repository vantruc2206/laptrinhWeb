<?php
require_once 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$error = '';
$success = '';

// Handle Delete
if ($action == 'delete' && isset($_GET['id']) && $_GET['id'] != $_SESSION['user_id']) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$_GET['id']])) {
        redirect('users.php?success=deleted');
    }
}

// Handle Form Submission (Create/Edit)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
    $password = $_POST['password'];

    if (empty($username) || empty($email)) {
        $error = "Username and Email are required.";
    } else {
        if ($action == 'create') {
            if (empty($password)) {
                $error = "Password is required for new users.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$username, $email, $hashed_password, $role])) {
                    redirect('users.php?success=created');
                } else {
                    $error = "Failed to create user. Username or Email might exist.";
                }
            }
        } elseif ($action == 'edit' && isset($_GET['id'])) {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, password = ? WHERE id = ?");
                $params = [$username, $email, $role, $hashed_password, $_GET['id']];
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
                $params = [$username, $email, $role, $_GET['id']];
            }
            if ($stmt->execute($params)) {
                redirect('users.php?success=updated');
            } else {
                $error = "Failed to update user.";
            }
        }
    }
}

// Handle success messages
if (isset($_GET['success'])) {
    $msgs = ['created' => 'Tạo người dùng thành công.', 'updated' => 'Cập nhật người dùng thành công.', 'deleted' => 'Đã xóa người dùng.'];
    $success = $msgs[$_GET['success']] ?? '';
}
?>

<div class="admin-header">
    <h1>Quản lý Người dùng</h1>
    <?php if ($action == 'list'): ?>
        <a href="?action=create" class="btn btn-primary">Thêm Người dùng mới</a>
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
                <?php
                $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <span class="badge <?= $row['role'] == 'admin' ? 'badge-success' : 'badge-warning' ?>">
                            <?= $row['role'] == 'admin' ? 'Quản trị viên' : 'Người dùng thường' ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                    <td class="action-links">
                        <a href="?action=edit&id=<?= $row['id'] ?>" class="edit">Sửa</a>
                        <?php if ($row['id'] != $_SESSION['user_id']): ?>
                            <a href="?action=delete&id=<?= $row['id'] ?>" class="delete" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này không?');">Xóa</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: 
    // Create or Edit Form
    $user = ['username' => '', 'email' => '', 'role' => 'user'];
    if ($action == 'edit' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $user = $stmt->fetch();
        if (!$user) redirect('users.php');
    }
?>
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
                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>Người dùng thường</option>
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Quản trị viên</option>
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

<?php require_once 'includes/footer.php'; ?>
